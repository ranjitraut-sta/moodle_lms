<?php

namespace theme_mytheme\Report;

defined('MOODLE_INTERNAL') || die();

class Dashboard
{
    /**
     * सम्पूर्ण ड्यासबोर्ड (System-wide) डेटा तयार गर्ने मुख्य फङ्सन
     */
    protected $user;

    public function __construct($user = null)
    {
        global $USER;
        $this->user = $user ?? $USER;
    }
    public function getData(): array
    {
        return [
            // Stats Cards
            'total_courses' => $this->getTotalCoursesCount(),
            'total_users' => $this->getTotalUsersCount(),
            'total_enrollments' => $this->getTotalEnrollmentsCount(),
            'top_3_enrolled' => $this->getTopEnrolledCourses(3),
            'most_completed' => $this->getMostCompletedCourse(),

            // Charts Data
            'active_users_chart' => $this->getActiveUsersData(30), // Last 30 days
            'enrollment_chart' => $this->getCourseEnrollmentChart(5),
            'dropout_rate' => $this->getDropoutRate(),
            'user_fullname' => fullname($this->user),
            'user_firstname' => $this->user->firstname,
            'user_profile_pix' => $this->get_user_picture(),
        ];
    }

    protected function get_user_picture()
    {
        global $OUTPUT;
        return $OUTPUT->user_picture($this->user, array('size' => 100, 'link' => false));
    }

    /**
     * १. कुल कोर्सहरूको संख्या
     */
    protected function getTotalCoursesCount(): int
    {
        global $DB;
        return (int) $DB->count_records('course', ['visible' => 1]) - 1; // Frontpage हटाएर
    }

    /**
     * २. कुल युजरहरूको संख्या
     */
    protected function getTotalUsersCount(): int
    {
        global $DB;
        return (int) $DB->count_records('user', ['deleted' => 0, 'suspended' => 0]);
    }

    /**
     * ३. कुल इन्रोलमेन्ट संख्या
     */
    protected function getTotalEnrollmentsCount(): int
    {
        global $DB;
        return (int) $DB->count_records('user_enrolments');
    }

    /**
     * ४. धेरै इन्रोल भएका टप ३ कोर्सहरू
     */
    protected function getTopEnrolledCourses($limit = 3): array
    {
        global $DB;
        $sql = "SELECT c.id, c.fullname, COUNT(ue.id) as enrollments
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                WHERE c.id != 1
                GROUP BY c.id, c.fullname
                ORDER BY enrollments DESC";
        return array_values($DB->get_records_sql($sql, null, 0, $limit));
    }

    /**
     * ५. सबैभन्दा धेरै कप्लिट गरिएको कोर्स
     */
    protected function getMostCompletedCourse(): ?\stdClass
    {
        global $DB;
        $sql = "SELECT c.id, c.fullname, COUNT(cc.id) as completions
                FROM {course} c
                JOIN {course_completions} cc ON cc.course = c.id
                WHERE cc.timecompleted > 0
                GROUP BY c.id, c.fullname
                ORDER BY completions DESC";
        $records = $DB->get_records_sql($sql, null, 0, 1);
        return !empty($records) ? reset($records) : null;
    }

    /**
     * ६. एक्टिभ युजर चार्ट (Last 7/30 Days)
     */
    protected function getActiveUsersData($days = 30): array
    {
        global $DB;
        $labels = [];
        $data = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $start = strtotime("$date 00:00:00");
            $end = strtotime("$date 23:59:59");

            $count = $DB->count_records_select(
                'user',
                "lastaccess BETWEEN ? AND ?",
                [$start, $end]
            );

            $labels[] = date('M d', $start);
            $data[] = $count;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * ७. ड्रपआउट रेट (Enroll vs Not Completed %)
     * Formula: ((Enrollments - Completions) / Enrollments) * 100
     */
    protected function getDropoutRate(): float
    {
        $totalEnroll = $this->getTotalEnrollmentsCount();
        if ($totalEnroll == 0)
            return 0;

        global $DB;
        $totalComplete = $DB->count_records_select('course_completions', "timecompleted > 0");

        $dropoutCount = max($totalEnroll - $totalComplete, 0);
        $rate = ($dropoutCount / $totalEnroll) * 100;

        return round($rate, 2);
    }

    /**
     * ८. कोर्स अनुसार इन्रोलमेन्ट चार्ट
     */
    protected function getCourseEnrollmentChart($limit = 5): array
    {
        $courses = $this->getTopEnrolledCourses($limit);
        $labels = [];
        $data = [];

        foreach ($courses as $c) {
            $labels[] = $c->fullname;
            $data[] = (int) $c->enrollments;
        }

        return ['labels' => $labels, 'data' => $data];
    }
}