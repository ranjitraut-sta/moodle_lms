<?php

namespace theme_mytheme\Report;

defined('MOODLE_INTERNAL') || die();

use theme_config; // 🔥 थिम कन्फिग क्लास लोड गरेको

class Report
{
    protected $user;

    public function __construct($user = null)
    {
        global $USER;
        $this->user = $user ?? $USER;
    }

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

        $reports = [];
        $has_filter = ($selected_course !== 'all' || $selected_status !== 'all');

        if ($has_filter) {
            $reports = $this->getDetailedReport($selected_course, $selected_status);
        }
        $has_results = !empty($reports);
        $show_dashboard = $has_filter; // only show dashboard when filter applied

        $stats = $this->calculateSummaryStats($reports);

        $status_options = [
            (object) ['value' => 'all', 'label' => 'All Status', 'selected' => $selected_status === 'all'],
            (object) ['value' => 'completed', 'label' => 'Completed Only', 'selected' => $selected_status === 'completed'],
            (object) ['value' => 'running', 'label' => 'In Progress Only', 'selected' => $selected_status === 'running'],
            (object) ['value' => 'dropout', 'label' => 'Dropped Out Only', 'selected' => $selected_status === 'dropout'],
        ];

        return [
            'course_options' => $course_options,
            'reports' => $reports,
            'stats' => $stats,
            'chart_data_json' => json_encode($stats['chart']),
            'current_url' => $PAGE->url->out(false),
            'has_filter' => $has_filter,
            'user_fullname' => fullname($this->user),
            'user_firstname' => $this->user->firstname,
            'user_profile_pix' => $this->get_user_picture(),
            'status_options' => $status_options,
            'show_dashboard' => $has_filter,
            'has_results' => $has_results,
            'logo' => $this->get_logo_url(),
        ];
    }

    protected function get_user_picture()
    {
        global $OUTPUT;
        return $OUTPUT->user_picture($this->user, array('size' => 100, 'link' => false));
    }

    private function calculateSummaryStats(array $reports): array
    {
        $total = count($reports);
        $completed = 0;
        $running = 0;
        $dropout = 0; // 🔥 Drop out काउन्टर थपियो
        $dropout_rate = 0;

        foreach ($reports as $r) {
            if ($r['status'] === 'Completed') {
                $completed++;
            } else if ($r['status'] === 'Drop Out') {
                $dropout++;
            } else {
                $running++;
            }
        }

        if (($total) > 0) {
            $dropout_rate = round(($dropout / $total) * 100, 2);
        }

        return [
            'total' => $total,
            'completed' => $completed,
            'running' => $running,
            'dropout' => $dropout,
            'dropout_rate' => $dropout_rate,
            'chart' => [
                // 🔥 चार्टमा 'Drop Out' पनि थपियो
                'labels' => ['Completed', 'In Progress', 'Drop Out'],
                'values' => [$completed, $running, $dropout]
            ]
        ];
    }

    public function getCourseListForDropdown(): array
    {
        global $DB;

        $sql = "SELECT id, fullname 
            FROM {course} 
            WHERE id != 1 AND visible = 1 
            ORDER BY fullname ASC";

        $records = $DB->get_records_sql($sql);
        return array_values($records);
    }

    public function getDetailedReport($courseid, $status): array
    {
        global $DB;

        // 🔥 १. थिम कन्फिगरेसनबाट दिन (Days) तान्ने
        $themeconfig = theme_config::load('mytheme');
        $valid_days = !empty($themeconfig->courseCompleteInBetween) ? (int) $themeconfig->courseCompleteInBetween : 1;
        $seconds_allowed = $valid_days * DAYSECS; // दिनलाई seconds मा बदलियो (१ दिन = ८६४०० सेकेन्ड)
        $now = time();

        $params = [];
        $where = "c.id != 1";

        if ($courseid !== 'all') {
            $where .= " AND c.id = :courseid";
            $params['courseid'] = $courseid;
        }

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

        $records = $DB->get_records_sql($sql, $params);

        $data = [];
        foreach ($records as $r) {

            // 🔥 २. STATUS LOGIC (कन्फिगरेसन दिनको आधारमा)
            $is_completed = !empty($r->timecompleted);
            $enrolled_time = (int) $r->enrolled_date;
            $expiry_time = $enrolled_time + $seconds_allowed;

            if ($is_completed) {
                $current_status = 'completed';
                $status_label = 'Completed';
                $status_class = 'success';
            } else if ($now > $expiry_time) {
                // यदि कोर्स सकिएको छैन र हालको समय (now) तोकिएको समय भन्दा नाघिसक्यो भने
                $current_status = 'dropout';
                $status_label = 'Drop Out';
                $status_class = 'danger'; // रातो रङको Badge को लागि
            } else {
                // यदि समय बाँकी नै छ र कोर्स चलिरहेको छ भने
                $current_status = 'running';
                $status_label = 'In Progress';
                $status_class = 'warning'; // पहेलो/सुन्तला रङको Badge को लागि
            }

            // PHP side फिल्टर चेक (dropdown मा 'status' फिल्टर गर्दा काम गर्छ)
            if ($status !== 'all' && $current_status !== $status) {
                continue;
            }

            $data[] = [
                'user' => $r->firstname . ' ' . $r->lastname,
                'course' => $r->coursename,
                'date' => userdate($r->enrolled_date, '%Y-%m-%d'),
                'completed_date' => $is_completed ? userdate($r->timecompleted, '%Y-%m-%d') : '-',
                'status' => $status_label,
                'status_class' => $status_class
            ];
        }
        return $data;
    }

    
      protected function get_logo_url()
    {
        global $PAGE, $OUTPUT;

        // 1. Moodle ko theme object bata direct image ko url line (Yesle settings ko 'logo' file check garchha)
        $logourl = $PAGE->theme->setting_file_url('logo', 'logo');

        // 2. Yadi logo upload bhako chha bhane link return garchha
        if ($logourl) {
            return $logourl;
        }

        // 3. Yadi setting ma logo chaina bhane, fallback ko rupma theme ko pix folder bata default image dina sakincha (Optional)
        // return $OUTPUT->image_url('default_logo', 'theme_mytheme'); 

        return false;
    }
}