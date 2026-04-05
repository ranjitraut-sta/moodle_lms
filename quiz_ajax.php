<?php
// theme/mytheme/quiz_ajax.php

define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

require_login();
require_sesskey();

$action   = required_param('action', PARAM_ALPHA);
$cmid     = required_param('cmid', PARAM_INT);
$quizid   = required_param('quizid', PARAM_INT);

$cm   = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
$quiz = $DB->get_record('quiz', ['id' => $quizid], '*', MUST_EXIST);

$PAGE->set_url('/theme/mytheme/quiz_ajax.php', ['action' => $action, 'quizid' => $quizid, 'cmid' => $cmid]);
$PAGE->set_context(context_module::instance($cm->id));
$PAGE->set_pagelayout('standard');

$quizobj = \mod_quiz\quiz_settings::create_for_cmid($cmid, $USER->id);
$context = $quizobj->get_context();
if (!$context) {
    throw new moodle_exception('invalidcontext', 'moodle');
}

header('Content-Type: application/json');

// ── START attempt ────────────────────────────────────────────────────────────
if ($action === 'start') {
    $existing = $DB->get_record_sql(
        "SELECT * FROM {quiz_attempts}
          WHERE quiz = :quizid AND userid = :userid AND state = 'inprogress'
       ORDER BY attempt DESC",
        ['quizid' => $quiz->id, 'userid' => $USER->id],
        IGNORE_MULTIPLE
    );

    if ($existing) {
        echo json_encode(['success' => true, 'attemptid' => $existing->id]);
        exit;
    }

    $attemptnumber = $DB->count_records('quiz_attempts', [
        'quiz'   => $quiz->id,
        'userid' => $USER->id,
    ]) + 1;

    $attempt = quiz_create_attempt($quizobj, $attemptnumber, false, time(), false, $USER->id);

    $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $context);
    $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour ?? 'deferredfeedback');

    $attempt = quiz_start_new_attempt($quizobj, $quba, $attempt, $attemptnumber, time());
    $attempt = quiz_attempt_save_started($quizobj, $quba, $attempt);

    if (empty($attempt) || empty($attempt->id)) {
        echo json_encode(['success' => false, 'error' => 'Could not create quiz attempt.']);
        exit;
    }

    echo json_encode(['success' => true, 'attemptid' => $attempt->id]);
    exit;
}

// ── SUBMIT attempt ───────────────────────────────────────────────────────────
if ($action === 'submit') {
    $attemptid = required_param('attemptid', PARAM_INT);

    $rawanswers = optional_param('answers', '', PARAM_RAW);
    if ($rawanswers !== '') {
        $answers = json_decode($rawanswers, true) ?? [];
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        $answers = $input['answers'] ?? [];
    }

    $attemptobj = \mod_quiz\quiz_attempt::create($attemptid);
    $attemptrec = $DB->get_record('quiz_attempts', ['id' => $attemptid], '*', MUST_EXIST);

    // Get question usage
    $quba = question_engine::load_questions_usage_by_activity($attemptrec->uniqueid);

    // Process each answer - Build proper post data
    $postdata = ['attempt' => $attemptid, 'sesskey' => sesskey(), 'finishattempt' => 1];
    
    // Group answers by slot for multi-select MCQ
    $slotAnswers = [];
    foreach ($answers as $ans) {
        $slot = (int)$ans['slot'];
        if (!isset($slotAnswers[$slot])) {
            $slotAnswers[$slot] = [];
        }
        $slotAnswers[$slot][] = $ans;
    }

    // Process each slot
    foreach ($slotAnswers as $slot => $slotAns) {
        $prefix = 'q' . $attemptrec->uniqueid . ':' . $slot . '_';
        
        // Get the first answer to determine type
        $firstAns = $slotAns[0];
        $answerid  = $firstAns['answerid']  ?? null;
        $textans   = $firstAns['textans']   ?? null;
        $ddwtosno  = $firstAns['ddwtosno']  ?? null;

        // DDWTOS type: p1, p2, p3... format
        if ($ddwtosno !== null) {
            foreach ($slotAns as $ans) {
                $postdata[$prefix . 'p' . $ans['ddwtosno']] = $ans['textans'] ?? '';
            }
            $postdata[$prefix . '-submit'] = '1';
            $postdata[$prefix . ':sequencecheck'] = '1';
        }
        // MCQ/TrueFalse with answer ID
        elseif ($answerid !== null) {
            // Check if multiple answers (multi-select MCQ)
            if (count($slotAns) > 1) {
                // Multi-select: array of answer IDs
                $answerIds = [];
                foreach ($slotAns as $ans) {
                    $answerIds[] = $ans['answerid'];
                }
                $postdata[$prefix . 'answer'] = $answerIds;
            } else {
                // Single select: just the answer ID
                $postdata[$prefix . 'answer'] = $answerid;
            }
            
            // Add required fields
            $postdata[$prefix . '-submit'] = '1';
            $postdata[$prefix . ':sequencecheck'] = '1';
        }
        // Short answer text
        elseif ($textans !== null) {
            $postdata[$prefix . 'answer'] = $textans;
            $postdata[$prefix . '-submit'] = '1';
            $postdata[$prefix . ':sequencecheck'] = '1';
        }
    }

    // Debug logging
    error_log('Quiz Submit Data: ' . print_r($postdata, true));

    try {
        // Process the submission
        $attemptobj->process_submitted_actions(time(), false, $postdata);
        
        // Finish the attempt properly
        $attemptobj->process_finish(time(), false);
        
        // Get updated attempt record
        $updated  = $DB->get_record('quiz_attempts', ['id' => $attemptid], '*', MUST_EXIST);
        $maxgrade = round((float)$quiz->sumgrades, 2);
        $score    = round((float)$updated->sumgrades, 2);
        $pct      = $maxgrade > 0 ? round(($score / $maxgrade) * 100, 1) : 0;
        $passed   = $quiz->gradepass > 0 && $score >= (float)$quiz->gradepass;

        // Get quiz grade (out of 10 or whatever the grade is set to)
        $quizgrade = $quiz->grade > 0 ? round(($score / $maxgrade) * $quiz->grade, 2) : 0;

        echo json_encode([
            'success'    => true,
            'score'      => $score,
            'maxgrade'   => $maxgrade,
            'percentage' => $pct,
            'quizgrade'  => $quizgrade,
            'quizgrademax' => (float)$quiz->grade,
            'passed'     => $passed,
            'reviewurl'  => (new \moodle_url('/mod/quiz/review.php', ['attempt' => $attemptid]))->out(false),
        ]);
    } catch (Exception $e) {
        error_log('Quiz submission error: ' . $e->getMessage());
        error_log('Stack trace: ' . $e->getTraceAsString());
        echo json_encode([
            'success' => false,
            'error'   => 'Submission failed: ' . $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);