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

            // Get real-time dynamic sequence check count from Moodle Engine
            $postdata[$prefix . ':sequencecheck'] = $qa->get_sequence_check_count();


            // -----------------------------------------------------------------
// FIXED & TESTED: DRAG AND DROP INTO TEXT (DDWTOS) MAPPING
// -----------------------------------------------------------------
            if ($qtype === 'ddwtos' && !empty($ans['drops'])) {

                // Log the incoming drop payload for tracing
                error_log("DDWTOS Slot " . $slot . " Payload: " . json_encode($ans['drops']));

                // Get the initialization step (Step 0 holds the structural choice orders)
                $step0 = $qa->get_step(0);

                foreach ($ans['drops'] as $blankno => $dragno) {
                    $blankno = (int) $blankno;
                    $dragno = (int) $dragno; // This is the 'no' property from get_ddwtos_data()

                    // Find which group this specific drop zone (place) belongs to
                    // Moodle 4.4 places array is 1-indexed matching the target layout placeholders
                    $group = null;
                    if (isset($question->places[$blankno])) {
                        $group = $question->places[$blankno];
                    } else if (isset($question->places[$blankno - 1])) {
                        $group = $question->places[$blankno - 1];
                    }

                    if ($group === null) {
                        error_log("DDWTOS Error: Place/Blank {$blankno} not found in Moodle question object.");
                        continue;
                    }

                    // Fetch the randomized choice order string for this specific group (e.g., "3,1,4,2")
                    $choiceorder_str = $step0->get_qt_var('_choiceorder' . $group);

                    if ($choiceorder_str) {
                        $choiceorder = explode(',', $choiceorder_str);

                        // Search where our original item counter position falls within the shuffled block
                        $index = array_search((string) $dragno, $choiceorder);

                        if ($index !== false) {
                            // Moodle expects a 1-based index pointing to the layout choice position
                            $choicekey = $index + 1;

                            // Bind response value to the standard parameter format
                            $postdata[$prefix . 'p' . $blankno] = $choicekey;

                            // Fallback mechanism if your template context handles 0-indexed values
                            if (!isset($question->places[$blankno]) && isset($question->places[$blankno - 1])) {
                                $postdata[$prefix . 'p' . ($blankno - 1)] = $choicekey;
                            }
                        } else {
                            error_log("DDWTOS Error: Drag item '{$dragno}' not found inside choiceorder array.");
                        }
                    }
                }
                error_log("Forged Post Data for DDWTOS Slot {$slot}: " . json_encode($postdata));
            }

            // -----------------------------------------------------------------
            // MCQ MULTIPLE CHOICE (CHECKBOX MATRIX) MAPPING
            // -----------------------------------------------------------------
            elseif ($qtype === 'multichoice_multi' && !empty($ans['selections'])) {
                $order = $question->get_order($qa);

                foreach ($ans['selections'] as $answerid => $isChecked) {
                    $aid = (int) $answerid;
                    $akey = array_search($aid, $order);
                    if ($akey !== false) {
                        $postdata[$prefix . 'choice' . $akey] = (int) $isChecked;
                    }
                }
            }

            // -----------------------------------------------------------------
            // MCQ SINGLE CHOICE (RADIO BUTTONS) MAPPING
            // -----------------------------------------------------------------
            elseif ($qtype === 'multichoice_single' && !empty($ans['answerid'])) {
                $order = $question->get_order($qa);
                $aid = (int) $ans['answerid'];
                $akey = array_search($aid, $order);
                if ($akey !== false) {
                    $postdata[$prefix . 'answer'] = (string) $akey;
                }
            }

            // -----------------------------------------------------------------
            // TRUE / FALSE MAPPING
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

        // Bind forged data map globally to PHP superglobal $_POST
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