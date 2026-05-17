<?php
// theme/mytheme/quiz_ajax.php

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

// User Authentication and Security Checks
require_login();
require_sesskey();

$action = required_param('action', PARAM_ALPHA);
$cmid = required_param('cmid', PARAM_INT);
$quizid = required_param('quizid', PARAM_INT);

// Load essential records
$cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
$quiz = $DB->get_record('quiz', ['id' => $quizid], '*', MUST_EXIST);

// Set up Moodle Page context
$PAGE->set_url('/theme/mytheme/quiz_ajax.php', [
    'action' => $action,
    'quizid' => $quizid,
    'cmid' => $cmid
]);
$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('standard');

$quizobj = \mod_quiz\quiz_settings::create_for_cmid($cmid, $USER->id);
$context = $quizobj->get_context();

if (!$context) {
    throw new moodle_exception('invalidcontext', 'moodle');
}

// Set JSON Header
header('Content-Type: application/json');

// =============================================================================
// ACTION: START QUIZ
// =============================================================================
if ($action === 'start') {
    $existing = $DB->get_record_sql(
        "SELECT *
           FROM {quiz_attempts}
          WHERE quiz = :quizid
            AND userid = :userid
            AND state = 'inprogress'
       ORDER BY attempt DESC",
        [
            'quizid' => $quiz->id,
            'userid' => $USER->id
        ],
        IGNORE_MULTIPLE
    );

    // If there is already an active attempt, return it
    if ($existing) {
        echo json_encode([
            'success' => true,
            'attemptid' => $existing->id
        ]);
        exit;
    }

    $attemptnumber = $DB->count_records('quiz_attempts', [
        'quiz' => $quiz->id,
        'userid' => $USER->id
    ]) + 1;

    $attempt = quiz_create_attempt(
        $quizobj,
        $attemptnumber,
        false,
        time(),
        false,
        $USER->id
    );

    $quba = question_engine::make_questions_usage_by_activity(
        'mod_quiz',
        $context
    );

    $quba->set_preferred_behaviour(
        $quizobj->get_quiz()->preferredbehaviour ?? 'deferredfeedback'
    );

    $attempt = quiz_start_new_attempt(
        $quizobj,
        $quba,
        $attempt,
        $attemptnumber,
        time()
    );

    $attempt = quiz_attempt_save_started(
        $quizobj,
        $quba,
        $attempt
    );

    if (empty($attempt) || empty($attempt->id)) {
        echo json_encode([
            'success' => false,
            'error' => 'Could not create quiz attempt.'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'attemptid' => $attempt->id
    ]);
    exit;
}

// =============================================================================
// ACTION: SUBMIT QUIZ
// =============================================================================
if ($action === 'submit') {
    try {
        $attemptid = required_param('attemptid', PARAM_INT);
        $answersjson = optional_param('answers', '[]', PARAM_RAW);
        $answers = json_decode($answersjson, true) ?? [];

        $attemptobj = \mod_quiz\quiz_attempt::create($attemptid);
        $attemptrec = $attemptobj->get_attempt();

        // Base array to forge $_POST data for Moodle Question Engine
        $postdata = [
            'attempt' => $attemptid,
            'sesskey' => sesskey(),
        ];

        $slots_processed = [];

        // Loop through each structured question answer group sent from Frontend JS
        foreach ($answers as $ans) {
            $slot = (int) ($ans['slot'] ?? 0);
            if (!$slot) {
                continue;
            }

            $slots_processed[] = $slot;
            $prefix = 'q' . $attemptrec->uniqueid . ':' . $slot . '_';

            // Get live Moodle internal structures for this question slot
            $qa = $attemptobj->get_question_attempt($slot);
            $question = $qa->get_question(false);
            $qtype = $ans['qtype'] ?? '';

            // CRITICAL FIX: Get real-time dynamic sequence check count from Moodle Engine
            $postdata[$prefix . ':sequencecheck'] = $qa->get_sequence_check_count();

            // -----------------------------------------------------------------
            // 1. DRAG AND DROP INTO TEXT (DDWTOS) MAPPING
            // -----------------------------------------------------------------
            if ($qtype === 'ddwtos' && !empty($ans['drops'])) {
                foreach ($ans['drops'] as $blankno => $dragno) {
                    $blankno = (int) $blankno;
                    $dragno = (int) $dragno;

                    if (!isset($question->places[$blankno])) {
                        continue;
                    }

                    $group = $question->places[$blankno];
                    $step = $qa->get_step(0);
                    $choiceorder_str = $step->get_qt_var('_choiceorder' . $group);

                    if ($choiceorder_str) {
                        $choiceorder = explode(',', $choiceorder_str);
                        // Find the index of the dragged item in randomized choiceorder
                        $index = array_search((string) $dragno, $choiceorder);
                        if ($index !== false) {
                            // Moodle demands 1-based index key for the slot form parameters
                            $choicekey = $index + 1;
                            $postdata[$prefix . 'p' . $blankno] = $choicekey;
                        }
                    }
                }
            }

            // -----------------------------------------------------------------
            // 2. MCQ MULTIPLE CHOICE (CHECKBOX MATRIX) MAPPING
            // -----------------------------------------------------------------
            elseif ($qtype === 'multichoice_multi' && !empty($ans['selections'])) {
                $order = $question->get_order($qa);

                foreach ($ans['selections'] as $answerid => $isChecked) {
                    $aid = (int) $answerid;
                    $akey = array_search($aid, $order);
                    if ($akey !== false) {
                        // Matrix requires choice index bound to 1 (checked) or 0 (unchecked)
                        $postdata[$prefix . 'choice' . $akey] = (int) $isChecked;
                    }
                }
            }

            // -----------------------------------------------------------------
            // 3. MCQ SINGLE CHOICE (RADIO BUTTONS) MAPPING
            // -----------------------------------------------------------------
            elseif ($qtype === 'multichoice_single' && !empty($ans['answerid'])) {
                $order = $question->get_order($qa);
                $aid = (int) $ans['answerid'];
                $akey = array_search($aid, $order);
                if ($akey !== false) {
                    // Single format requires key order as string index
                    $postdata[$prefix . 'answer'] = (string) $akey;
                }
            }

            // -----------------------------------------------------------------
            // 4. TRUE / FALSE MAPPING
            // -----------------------------------------------------------------
            elseif ($qtype === 'truefalse' && !empty($ans['answerid'])) {
                $aid = (int) $ans['answerid'];
                if ($aid === (int) $question->trueanswerid) {
                    $postdata[$prefix . 'answer'] = 1;
                } elseif ($aid === (int) $question->falseanswerid) {
                    $postdata[$prefix . 'answer'] = 0;
                }
            }
        }

        // Inform Moodle which slots are being managed in this thread context
        if (!empty($slots_processed)) {
            $postdata['slots'] = implode(',', array_unique($slots_processed));
        }

        // CRITICAL FOR MOODLE: Bind forged data map globally to PHP superglobal $_POST
        $_POST = $postdata;

        // Process states inside Moodle Engine
        $attemptobj->process_submitted_actions(time(), true, null);

        // Finish the attempt cleanly and calculate final scoring matrices
        $attemptobj->process_finish(time(), false);

        // Fetch freshly calculated status directly from Database
        $updatedAttempt = $DB->get_record('quiz_attempts', ['id' => $attemptid], '*', MUST_EXIST);

        echo json_encode([
            'success' => true,
            'score' => (float) $updatedAttempt->sumgrades,
            'maxgrade' => (float) $quiz->sumgrades,
        ]);

    } catch (Throwable $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage() . " on Line: " . $e->getLine()
        ]);
    }
    exit;
}

// Fallback error
echo json_encode(['success' => false, 'error' => 'Unknown action request context']);
exit;