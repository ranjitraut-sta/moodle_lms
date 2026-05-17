<?php
// theme/mytheme/quiz_ajax.php

define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

require_login();
require_sesskey();

$action = required_param('action', PARAM_ALPHA);
$cmid = required_param('cmid', PARAM_INT);
$quizid = required_param('quizid', PARAM_INT);

$cm = get_coursemodule_from_id('quiz', $cmid, 0, false, MUST_EXIST);
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
        'quiz' => $quiz->id,
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

    $input = json_decode(file_get_contents('php://input'), true);
    $answers = $input['answers'] ?? [];

    $attemptobj = \mod_quiz\quiz_attempt::create($attemptid);
    $attemptrec = $DB->get_record('quiz_attempts', ['id' => $attemptid], '*', MUST_EXIST);

    $postdata = [
        'attempt' => $attemptid,
        'sesskey' => sesskey(),
    ];

    $slotAnswers = [];

    foreach ($answers as $ans) {
        $slot = (int)$ans['slot'];
        $slotAnswers[$slot][] = $ans;
    }

    foreach ($slotAnswers as $slot => $slotAns) {

        $prefix = 'q' . $attemptrec->uniqueid . ':' . $slot . '_';

        $first = $slotAns[0] ?? [];

        $answerid = $first['answerid'] ?? null;
        $textans = $first['textans'] ?? null;
        $ddwtosno = $first['ddwtosno'] ?? null;

        // DDWTOS
        if ($ddwtosno !== null) {
            foreach ($slotAns as $ans) {
                $postdata[$prefix . 'sub' . (int)$ans['ddwtosno']] = (int)$ans['textans'];
            }
            $postdata[$prefix . ':sequencecheck'] = 1;
        }

        // MCQ / TF
        elseif ($answerid !== null) {

            if (count($slotAns) > 1) {
                $multi = [];
                foreach ($slotAns as $ans) {
                    $aid = (int)$ans['answerid'];
                    $multi[$aid] = $aid;
                }
                $postdata[$prefix . 'answer'] = $multi;
            } else {
                $postdata[$prefix . 'answer'] = (int)$answerid;
            }

            $postdata[$prefix . ':sequencecheck'] = 1;
        }

        // Text
        elseif ($textans !== null) {
            $postdata[$prefix . 'answer'] = $textans;
            $postdata[$prefix . ':sequencecheck'] = 1;
        }
    }

    try {

        $attemptobj->process_submitted_actions(time(), true, $postdata);

        // FIX HERE 👇 (Moodle 4.4)
        $attemptobj->process_finish(time());

        $updated = $DB->get_record('quiz_attempts', ['id' => $attemptid], '*', MUST_EXIST);

        $maxgrade = (float)$quiz->sumgrades;
        $score = (float)$updated->sumgrades;

        echo json_encode([
            'success' => true,
            'score' => $score,
            'maxgrade' => $maxgrade,
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }

    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);