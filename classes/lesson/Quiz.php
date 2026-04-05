<?php
namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;
use mod_quiz\quiz_settings;
use mod_quiz\structure;

class Quiz implements LessonModuleInterface {

    protected $cmid;
    protected $quiz;
    protected $userid;

    public function __construct($cmid) {
        global $USER;
        $this->cmid = $cmid;
        $this->userid = $USER->id;
    }

    public function getData(): array {
        global $DB;

        // १. बेसिक डेटा सेटअप
        $cm = get_coursemodule_from_id('quiz', $this->cmid, 0, false, MUST_EXIST);
        $this->quiz = $DB->get_record('quiz', ['id' => $cm->instance], '*', MUST_EXIST);

        // २. क्विज स्ट्रक्चर र प्रश्नहरू तान्ने
        $questions = $this->get_quiz_questions();

        // ३. एटेम्प्टहरू (Attempts) को जानकारी
        $inprogress = $this->get_attempt_by_state('inprogress');
        $finished   = $this->get_attempt_by_state('finished');
        $all_attempts_data = $this->get_all_attempts_history();

        return [
            'isquiz'         => true,
            'quizname'       => format_string($this->quiz->name),
            'quizid'         => $this->quiz->id,
            'cmid'           => $this->cmid,
            'questions'      => $questions,
            'hasresult'      => !empty($finished),
            'attemptresult'  => $finished ? $this->format_attempt_result($finished) : null,
            'inprogress'     => !empty($inprogress),
            'inprogressinfo' => $inprogress ? $this->format_inprogress_info($inprogress) : null,
            'attemptid'      => $inprogress ? $inprogress->id : 0,
            'highestgrade'   => number_format($all_attempts_data['highest'], 2),
            'maxgrade'       => number_format((float)$this->quiz->sumgrades, 2),
            'hasattempts'    => !empty($all_attempts_data['history']),
            'attempts'       => $all_attempts_data['history'],
            'ajaxurl'        => (new \moodle_url('/theme/mytheme/quiz_ajax.php'))->out(false),
            'sesskey'        => sesskey(),
        ];
    }

    /**
     * प्रश्नहरू र तिनका विकल्पहरू तयार पार्ने
     */
    private function get_quiz_questions(): array {
        global $DB, $USER;
        
        try {
            $quizobj = quiz_settings::create_for_cmid($this->cmid, $USER->id);
        } catch (\Exception $e) {
            $quizobj = quiz_settings::create($this->quiz->id, $USER->id);
        }

        $structure = structure::create_for_quiz($quizobj);
        $quizdata = [];

        foreach ($structure->get_slots() as $slot) {
            if (empty($slot->questionid)) continue;

            $q = $DB->get_record('question', ['id' => $slot->questionid], '*', MUST_EXIST);
            $item = [
                'id'     => $q->id,
                'slotid' => $slot->id,
                'slot'   => $slot->slot,
                'type'   => $q->qtype,
                'mark'   => (float)$slot->maxmark,
            ];

            // प्रकार अनुसार डेटा थप्ने
            if (in_array($q->qtype, ['multichoice', 'truefalse'])) {
                $item = array_merge($item, $this->get_choice_data($q));
            } elseif ($q->qtype === 'ddwtos') {
                $item = array_merge($item, $this->get_ddwtos_data($q));
            } else {
                $item['text'] = format_text($q->questiontext, FORMAT_HTML);
            }

            $quizdata[] = $item;
        }
        return $quizdata;
    }

    /**
     * MCQ र True/False को लागि विकल्पहरू
     */
    private function get_choice_data($q): array {
        global $DB;
        $answers = [];
        $rows = $DB->get_records('question_answers', ['question' => $q->id], 'id ASC', 'id, answer');
        
        foreach ($rows as $row) {
            $answers[] = [
                'answerid'   => $row->id,
                'answertext' => format_text($row->answer, FORMAT_HTML),
            ];
        }

        $mcqsingle = true;
        if ($q->qtype === 'multichoice') {
            $table = $DB->get_manager()->table_exists('question_multichoice') ? 'question_multichoice' : 'qtype_multichoice_options';
            $field = ($table === 'question_multichoice') ? 'question' : 'questionid';
            $mcq = $DB->get_record($table, [$field => $q->id], 'single', IGNORE_MISSING);
            $mcqsingle = $mcq ? (bool)$mcq->single : true;
        }

        return [
            'text'        => format_text($q->questiontext, FORMAT_HTML),
            'ismcq'       => $q->qtype === 'multichoice',
            'ismcqsingle' => $mcqsingle,
            'istruefalse' => $q->qtype === 'truefalse',
            'answers'     => $answers
        ];
    }

    /**
     * Drag & Drop into Text को डेटा
     */
    private function get_ddwtos_data($q): array {
        global $DB;
        $options = [];
        $items = [];
        $text = $q->questiontext;

        $choices = $DB->get_records('qtype_ddwtos_options', ['questionid' => $q->id], 'groupno ASC, no ASC');
        foreach ($choices as $choice) {
            $options[] = [
                'no' => $choice->no,
                'groupno' => $choice->groupno,
                'text' => strip_tags($choice->dragtext),
            ];
        }

        preg_match_all('/\[\[(\d+)\]\]/', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $blankNo = $match[1];
            $dropzone = '<span class="amd-ddwtos-inline-drop" data-no="'.$blankNo.'"><input type="text" readonly class="form-control d-inline-block" style="width:120px; border:2px dashed #0d6efd;"></span>';
            $text = str_replace($match[0], $dropzone, $text);
            $items[] = ['no' => $blankNo, 'text' => 'Blank ' . $blankNo];
        }

        return [
            'text'          => format_text($text, FORMAT_HTML),
            'isddwtos'      => true,
            'ddwtositems'   => $items,
            'ddwtosoptions' => $options
        ];
    }

    /**
     * एटेम्प्ट हिस्ट्री र सबैभन्दा उच्च अंक निकाल्ने
     */
    private function get_all_attempts_history(): array {
        global $DB;
        $all = $DB->get_records('quiz_attempts', ['quiz' => $this->quiz->id, 'userid' => $this->userid], 'attempt DESC');
        
        $history = [];
        $highest = 0.0;
        foreach ($all as $a) {
            $grade = (float)$a->sumgrades;
            $highest = max($highest, $grade);
            $history[] = [
                'attemptnumber' => $a->attempt,
                'status'        => ucfirst($a->state),
                'marks'         => number_format($grade, 2),
                'grade'         => ($this->quiz->sumgrades > 0) ? number_format(($grade / $this->quiz->sumgrades) * 100, 2) : 0,
                'reviewurl'     => (new \moodle_url('/mod/quiz/review.php', ['attempt' => $a->id]))->out(false),
            ];
        }
        return ['history' => $history, 'highest' => $highest];
    }

    private function get_attempt_by_state($state) {
        global $DB;
        return $DB->get_record('quiz_attempts', 
            ['quiz' => $this->quiz->id, 'userid' => $this->userid, 'state' => $state], 
            '*', IGNORE_MULTIPLE);
    }

    private function format_attempt_result($finished): array {
        $sumgrades  = round((float)$finished->sumgrades, 2);
        $maxgrade   = round((float)$this->quiz->sumgrades, 2);
        // gradepass नहुन सक्छ, त्यसैले 0 डिफल्ट राखिएको छ
        $gradepass  = isset($this->quiz->gradepass) ? (float)$this->quiz->gradepass : 0;
        $passed     = $gradepass > 0 && $sumgrades >= $gradepass;

        return [
            'attemptnumber' => $finished->attempt,
            'sumgrades'     => $sumgrades,
            'maxgrade'      => $maxgrade,
            'percentage'    => $maxgrade > 0 ? round(($sumgrades / $maxgrade) * 100, 1) : 0,
            'passed'        => $passed,
            'failed'        => !$passed,
        ];
    }

    private function format_inprogress_info($inprogress): array {
        return [
            'id'            => $inprogress->id,
            'attemptnumber' => $inprogress->attempt,
            'state'         => $inprogress->state,
        ];
    }
}