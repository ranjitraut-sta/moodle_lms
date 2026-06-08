<?php

namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;
use mod_quiz\quiz_settings;
use mod_quiz\structure;

class Quiz implements LessonModuleInterface
{

    protected $cmid;
    protected $quiz;
    protected $userid;

    public function __construct($cmid)
    {
        global $USER;
        $this->cmid = $cmid;
        $this->userid = $USER->id;
    }

    public function getData(): array
    {
        global $DB;

        // १. बेसिक डेटा सेटअप
        $cm = get_coursemodule_from_id('quiz', $this->cmid, 0, false, MUST_EXIST);
        $this->quiz = $DB->get_record('quiz', ['id' => $cm->instance], '*', MUST_EXIST);

        // २. क्विज स्ट्रक्चर र प्रश्नहरू तान्ने
        $questions = $this->get_quiz_questions();

        // ३. एटेम्प्टहरू (Attempts) को जानकारी
        $inprogress = $this->get_attempt_by_state('inprogress');
        $finished = $this->get_attempt_by_state('finished');
        $all_attempts_data = $this->get_all_attempts_history();

        return [
            'isquiz' => true,
            'quizname' => format_string($this->quiz->name),
            'quizid' => $this->quiz->id,
            'cmid' => $this->cmid,
            'questions' => $questions,
            'hasresult' => !empty($finished),
            'attemptresult' => $finished ? $this->format_attempt_result($finished) : null,
            'inprogress' => !empty($inprogress),
            'inprogressinfo' => $inprogress ? $this->format_inprogress_info($inprogress) : null,
            'attemptid' => $inprogress ? $inprogress->id : 0,
            'highestgrade' => number_format($all_attempts_data['highest'], 2),
            'maxgrade' => number_format((float) $this->quiz->sumgrades, 2),
            'hasattempts' => !empty($all_attempts_data['history']),
            'attempts' => $all_attempts_data['history'],
            'ajaxurl' => (new \moodle_url('/theme/mytheme/quiz_ajax.php'))->out(false),
            'sesskey' => sesskey(),
        ];
    }

    /**
     * प्रश्नहरू र तिनका विकल्पहरू तयार पार्ने (Random Question र s6 Error फिक्स)
     */
    private function get_quiz_questions(): array
    {
        global $DB, $USER;

        try {
            $quizobj = quiz_settings::create_for_cmid($this->cmid, $USER->id);
        } catch (\Exception $e) {
            $quizobj = quiz_settings::create($this->quiz->id, $USER->id);
        }

        $structure = structure::create_for_quiz($quizobj);
        $quizdata = [];

        // युजरको इन-प्रोग्रेस वा हालै सकिएको एटेम्प्ट हेर्ने
        $attempt = $DB->get_record('quiz_attempts', [
            'quiz' => $this->quiz->id,
            'userid' => $USER->id,
            'state' => 'inprogress'
        ], '*', IGNORE_MULTIPLE);

        if (!$attempt) {
            $attempt = $DB->get_record_sql(
                'SELECT * FROM {quiz_attempts} WHERE quiz = ? AND userid = ? AND state = ? ORDER BY attempt DESC LIMIT 1',
                [$this->quiz->id, $USER->id, 'finished']
            );
        }

        // स्थिति १: यदि युजरको एटेम्प्ट (Attempt) छ भने र्‍यान्डम प्रश्नहरू स्वतः वास्तविक प्रश्नमा परिणत हुन्छन्
        if ($attempt) {
            $attemptobj = \mod_quiz\quiz_attempt::create($attempt->id);
            $slots = $attemptobj->get_slots();

            foreach ($slots as $slotno) {
                $clean_slotno = (int) ltrim((string) $slotno, 's');

                try {
                    $qa = $attemptobj->get_question_attempt($clean_slotno);
                    $q = $qa->get_question(false);
                } catch (\Exception $e) {
                    continue;
                }

                $qtype = is_object($q->qtype) ? $q->qtype->name() : (string) $q->qtype;

                $maxmark = 1.0;
                if (method_exists($attemptobj->get_quiz(), 'get_slot_max_mark')) {
                    $maxmark = (float) $attemptobj->get_quiz()->get_slot_max_mark($clean_slotno);
                } else {
                    $slot_obj = $structure->get_slot_by_number($clean_slotno);
                    if ($slot_obj) {
                        $maxmark = (float) $slot_obj->maxmark;
                    }
                }

                $item = [
                    'id' => (int) $q->id,
                    'slotid' => $clean_slotno,
                    'slot' => $clean_slotno,
                    'type' => $qtype,
                    'mark' => $maxmark,
                ];

                if (in_array($qtype, ['multichoice', 'truefalse'])) {
                    $item = array_merge($item, $this->get_choice_data($q, $qtype));
                } elseif ($qtype === 'ddwtos') {
                    $item = array_merge($item, $this->get_ddwtos_data($q));
                } else {
                    $item['text'] = format_text($q->questiontext, FORMAT_HTML);
                }

                $quizdata[] = $item;
            }
        } else {
            // स्थिति २: एटेम्प्ट नभएको बेला पुरानै स्ट्याटिक लोजिक चल्ने
            foreach ($structure->get_slots() as $slot) {

                // फिक्स: यदि questionid खाली छ वा अंक होइन (जस्तै 's6') भने यसलाई सुरक्षित रूपमा स्किप गर्ने
                if (empty($slot->questionid) || !is_numeric($slot->questionid)) {
                    continue;
                }

                try {
                    $q = $DB->get_record('question', ['id' => $slot->questionid], '*', MUST_EXIST);
                } catch (\Exception $e) {
                    continue; // यदि कुनै कारणले डेटाबेसमा भेटिएन भने क्र्यास हुन नदिने
                }

                $qtype = is_object($q->qtype) ? $q->qtype->name() : (string) $q->qtype;

                $item = [
                    'id' => (int) $q->id,
                    'slotid' => $slot->id,
                    'slot' => $slot->slot,
                    'type' => $qtype,
                    'mark' => (float) $slot->maxmark,
                ];

                if (in_array($qtype, ['multichoice', 'truefalse'])) {
                    $item = array_merge($item, $this->get_choice_data($q, $qtype));
                } elseif ($qtype === 'ddwtos') {
                    $item = array_merge($item, $this->get_ddwtos_data($q));
                } else {
                    $item['text'] = format_text($q->questiontext, FORMAT_HTML);
                }

                $quizdata[] = $item;
            }
        }

        return $quizdata;
    }

    /**
     * MCQ र True/False को लागि विकल्पहरू
     */
    private function get_choice_data($q, $qtype = null): array
    {
        global $DB;
        $answers = [];
        $rows = $DB->get_records('question_answers', ['question' => $q->id], 'id ASC', 'id, answer');

        foreach ($rows as $row) {
            $answers[] = [
                'answerid' => $row->id,
                'answertext' => format_text($row->answer, FORMAT_HTML),
            ];
        }

        if ($qtype === null) {
            $qtype = is_object($q->qtype) ? $q->qtype->name() : (string) $q->qtype;
        }

        $mcqsingle = true;
        if ($qtype === 'multichoice') {
            $table = $DB->get_manager()->table_exists('question_multichoice') ? 'question_multichoice' : 'qtype_multichoice_options';
            $field = ($table === 'question_multichoice') ? 'question' : 'questionid';
            $mcq = $DB->get_record($table, [$field => $q->id], 'single', IGNORE_MISSING);
            $mcqsingle = $mcq ? (bool) $mcq->single : true;
        }

        return [
            'text' => format_text($q->questiontext, FORMAT_HTML),
            'ismcq' => $qtype === 'multichoice',
            'ismcqsingle' => $mcqsingle,
            'istruefalse' => $qtype === 'truefalse',
            'answers' => $answers
        ];
    }

    /**
     * Drag & Drop into Text को डेटा
     */
    private function get_ddwtos_data($q): array
    {
        global $DB;
        $options = [];
        $items = [];
        $text = $q->questiontext;

        $answers = $DB->get_records('question_answers', ['question' => $q->id], 'id ASC');

        $no = 1;
        foreach ($answers as $ans) {
            $draggroup = 1;
            if (!empty($ans->feedback)) {
                $feedback = @unserialize($ans->feedback);
                if ($feedback && isset($feedback->draggroup)) {
                    $draggroup = $feedback->draggroup;
                }
            }
            $options[] = [
                'no' => $no++,
                'groupno' => $draggroup,
                'text' => strip_tags($ans->answer),
            ];
        }

        preg_match_all('/\[\[(\d+)\]\]/', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $blankNo = $match[1];
            $items[] = ['no' => $blankNo, 'text' => 'Blank ' . $blankNo];
        }

        return [
            'text' => format_text($text, FORMAT_HTML),
            'isddwtos' => true,
            'ddwtositems' => $items,
            'ddwtosoptions' => $options
        ];
    }

    /**
     * एटेम्प्ट हिस्ट्री र सबैभन्दा उच्च अंक निकाल्ने
     */
    private function get_all_attempts_history(): array
    {
        global $DB;
        $last = $DB->get_record_sql(
            'SELECT * FROM {quiz_attempts} WHERE quiz = ? AND userid = ? AND state = ? ORDER BY attempt DESC LIMIT 1',
            [$this->quiz->id, $this->userid, 'finished']
        );

        $history = [];
        $highest = 0.0;
        if ($last) {
            $grade = (float) $last->sumgrades;
            $highest = $grade;
            $history[] = [
                'attemptnumber' => $last->attempt,
                'status' => ucfirst($last->state),
                'marks' => number_format($grade, 2),
                'maxmarks' => number_format((float) $this->quiz->sumgrades, 2),
                'grade' => ($this->quiz->sumgrades > 0) ? number_format(($grade / $this->quiz->sumgrades) * 100, 2) : 0,
                'reviewurl' => (new \moodle_url('/mod/quiz/review.php', ['attempt' => $last->id]))->out(false),
            ];
        }
        return ['history' => $history, 'highest' => $highest];
    }

    private function get_attempt_by_state($state)
    {
        global $DB;
        return $DB->get_record(
            'quiz_attempts',
            ['quiz' => $this->quiz->id, 'userid' => $this->userid, 'state' => $state],
            '*',
            IGNORE_MULTIPLE
        );
    }

    private function format_attempt_result($finished): array
    {
        global $CFG;
        require_once($CFG->libdir . '/gradelib.php');

        $sumgrades = round((float) $finished->sumgrades, 2);
        $maxgrade = round((float) $this->quiz->sumgrades, 2);

        $grade_item = \grade_item::fetch([
            'itemtype' => 'mod',
            'itemmodule' => 'quiz',
            'iteminstance' => $this->quiz->id
        ]);
        $gradepass = $grade_item ? (float) $grade_item->gradepass : 0;

        $passed = $gradepass > 0 && $sumgrades >= $gradepass;

        return [
            'attemptnumber' => $finished->attempt,
            'sumgrades' => $sumgrades,
            'maxgrade' => $maxgrade,
            'percentage' => $maxgrade > 0 ? round(($sumgrades / $maxgrade) * 100, 1) : 0,
            'passed' => $passed,
            'failed' => !$passed,
        ];
    }

    private function format_inprogress_info($inprogress): array
    {
        return [
            'id' => $inprogress->id,
            'attemptnumber' => $inprogress->attempt,
            'state' => $inprogress->state,
        ];
    }
}