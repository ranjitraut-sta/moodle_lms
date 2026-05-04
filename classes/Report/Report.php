<?php

namespace theme_mytheme\Report;

defined('MOODLE_INTERNAL') || die();

class Report
{
public function getData(): array
{
    global $PAGE;

    $selected_course = optional_param('courseid', 'all', PARAM_RAW);
    $selected_status = optional_param('status', 'all', PARAM_ALPHANUM);

    $course_options = $this->getCourseListForDropdown();

    foreach ($course_options as $opt) {
        if ($opt->id == $selected_course) {
            $opt->selected = true;
        }
    }

    // ⚠️ DEFAULT: empty reports
    $reports = [];

    // ✅ ONLY FETCH WHEN FILTER APPLIED
    $has_filter = ($selected_course !== 'all' || $selected_status !== 'all');

    if ($has_filter) {
        $reports = $this->getDetailedReport($selected_course, $selected_status);
    }

    $stats = $this->calculateSummaryStats($reports);

    return [
        'course_options' => $course_options,

        // 🔥 default empty, search pachi matra data
        'reports' => $reports,

        'stats' => $stats,
        'chart_data_json' => json_encode($stats['chart']),
        'current_url' => $PAGE->url->out(false),

        // optional flag for UI
        'has_filter' => $has_filter
    ];
}

private function calculateSummaryStats(array $reports): array
{
    $total = count($reports);
    $completed = 0;
    $running = 0;

    foreach ($reports as $r) {
        if ($r['status'] === 'Completed') {
            $completed++;
        } else {
            $running++;
        }
    }

    return [
        'total' => $total,
        'completed' => $completed,
        'running' => $running,
        'chart' => [
            'labels' => ['Completed', 'In Progress'],
            'values' => [$completed, $running]
        ]
    ];
}

    public function getCourseListForDropdown(): array
    {
        global $DB;

        // Dropdown ko lagi khali id ra fullname matra select garne
        $sql = "SELECT id, fullname 
            FROM {course} 
            WHERE id != 1 AND visible = 1 
            ORDER BY fullname ASC";

        $records = $DB->get_records_sql($sql);

        // Array format ma convert garne (Mustache ma loop chalauna sajilo huncha)
        return array_values($records);
    }

public function getDetailedReport($courseid, $status): array
{
    global $DB;

    $params = [];
    $where = "c.id != 1";

    if ($courseid !== 'all') {
        $where .= " AND c.id = :courseid";
        $params['courseid'] = $courseid;
    }

    // Fix: MD5 use garera userid ra courseid bata unique string banaune jasle error hataunchha
    $sql = "SELECT 
                CONCAT(u.id, '_', c.id) as uniqueid, 
                u.id as userid, u.firstname, u.lastname, 
                c.fullname as coursename,
                cc.timecompleted,
                ue.timestart as enrolled_date
            FROM {user} u
            JOIN {user_enrolments} ue ON ue.userid = u.id
            JOIN {enrol} e ON e.id = ue.enrolid
            JOIN {course} c ON c.id = e.courseid
            LEFT JOIN {course_completions} cc ON cc.course = c.id AND cc.userid = u.id
            WHERE $where
            ORDER BY enrolled_date DESC";

    // get_records_sql le pahilo column (uniqueid) lai key mandinchha
    $records = $DB->get_records_sql($sql, $params);

    $data = [];
    foreach ($records as $r) {
        // Status logic based on filter if needed
        $current_status = $r->timecompleted ? 'completed' : 'running';
        
        // PHP side status filter (yadi SQL ma garne vaye ali complex hunchha, so hami yehi garchhau)
        if ($status !== 'all' && $current_status !== $status) {
            continue;
        }

        $data[] = [
            'user' => $r->firstname . ' ' . $r->lastname,
            'course' => $r->coursename,
            'date' => userdate($r->enrolled_date, '%Y-%m-%d'),
            'status' => $r->timecompleted ? 'Completed' : 'In Progress',
            'status_class' => $r->timecompleted ? 'success' : 'warning'
        ];
    }
    return $data;
}

}