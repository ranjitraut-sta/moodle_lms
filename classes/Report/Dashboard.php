<?php

namespace theme_mytheme\Report;

defined('MOODLE_INTERNAL') || die();

class Dashboard
{
    protected $user;

    public function __construct($user = null)
    {
        global $USER;
        $this->user = $user ?? $USER;
    }

    /**
     * MAIN DASHBOARD DATA
     */
    public function getData(): array
    {
        return [
            // Stats
            'total_courses' => $this->getTotalCoursesCount(),
            'total_users' => $this->getTotalUsersCount(),
            'total_enrollments' => $this->getTotalEnrollmentsCount(),
            'dropout_rate' => $this->getDropoutRate(),

            // Charts
            'active_users_chart' => $this->getActiveUsersChart(30),
            'enrollment_chart' => $this->getEnrollmentChart(5),

            // Tables
            'top_3_enrolled' => $this->getTopEnrolledCourses(3),
            'most_completed' => $this->getMostCompletedCourse(),

            // User info
            'user_fullname' => fullname($this->user),
            'user_firstname' => $this->user->firstname,
            'user_profile_pix' => $this->getUserPicture(),
            'active_users_today' => $this->getTodayActiveUsersCount(),
        ];
    }

    /**
     * USER PICTURE
     */
    protected function getUserPicture()
    {
        global $OUTPUT;
        return $OUTPUT->user_picture($this->user, [
            'size' => 100,
            'link' => false
        ]);
    }

    /**
     * TOTAL COURSES
     */
    protected function getTotalCoursesCount(): int
    {
        global $DB;

        return (int) $DB->count_records_sql("
            SELECT COUNT(1)
            FROM {course}
            WHERE id != 1 AND visible = 1
        ");
    }

    /**
     * TOTAL USERS
     */
    protected function getTotalUsersCount(): int
    {
        global $DB;

        return (int) $DB->count_records_sql("
            SELECT COUNT(1)
            FROM {user}
            WHERE deleted = 0 AND suspended = 0
        ");
    }

    /**
     * TOTAL ENROLLMENTS
     */
    protected function getTotalEnrollmentsCount(): int
    {
        global $DB;

        return (int) $DB->count_records('user_enrolments');
    }

    /**
     * TOP ENROLLED COURSES
     */
    protected function getTopEnrolledCourses(int $limit = 3): array
    {
        global $DB;

        $sql = "
            SELECT 
                c.id,
                c.fullname,
                COUNT(ue.id) AS enrollments
            FROM {course} c
            JOIN {enrol} e ON e.courseid = c.id
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            WHERE c.id != 1
            GROUP BY c.id, c.fullname
            ORDER BY enrollments DESC
        ";

        return array_values($DB->get_records_sql($sql, null, 0, $limit));
    }

    /**
     * MOST COMPLETED COURSE
     */
    protected function getMostCompletedCourse(): ?\stdClass
    {
        global $DB;

        $sql = "
            SELECT 
                c.id,
                c.fullname,
                COUNT(cc.id) AS completions
            FROM {course} c
            JOIN {course_completions} cc ON cc.course = c.id
            WHERE cc.timecompleted > 0
            GROUP BY c.id, c.fullname
            ORDER BY completions DESC
        ";

        $records = $DB->get_records_sql($sql, null, 0, 1);

        return !empty($records) ? reset($records) : null;
    }

    /**
     * ACTIVE USERS (LAST N DAYS) - OPTIMIZED (NO LOOP QUERIES)
     */
    protected function getActiveUsersChart(int $days = 30): array
    {
        global $DB;

        $startTime = strtotime("-$days days");

        $sql = "
            SELECT 
                DATE(FROM_UNIXTIME(timecreated)) AS day,
                COUNT(DISTINCT userid) AS total
            FROM {logstore_standard_log}
            WHERE timecreated >= :starttime
            GROUP BY DATE(FROM_UNIXTIME(timecreated))
            ORDER BY day ASC
        ";

        $records = $DB->get_records_sql($sql, [
            'starttime' => $startTime
        ]);

        $labels = [];
        $data = [];

        foreach ($records as $r) {
            $labels[] = $r->day;
            $data[] = (int) $r->total;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * DROP OUT RATE
     */
    protected function getDropoutRate(): float
    {
        global $DB;

        $totalEnroll = $this->getTotalEnrollmentsCount();

        if ($totalEnroll <= 0) {
            return 0;
        }

        $completed = (int) $DB->count_records_sql("
            SELECT COUNT(1)
            FROM {course_completions}
            WHERE timecompleted > 0
        ");

        $dropout = max($totalEnroll - $completed, 0);

        return round(($dropout / $totalEnroll) * 100, 2);
    }

    /**
     * ENROLLMENT DISTRIBUTION CHART
     */
    protected function getEnrollmentChart(int $limit = 5): array
    {
        $courses = $this->getTopEnrolledCourses($limit);

        $labels = [];
        $data = [];

        foreach ($courses as $c) {
            $labels[] = $c->fullname;
            $data[] = (int) $c->enrollments;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    protected function getTodayActiveUsersCount(): int
    {
        global $DB;

        $start = strtotime('today midnight');
        $end = time();

        return (int) $DB->count_records_sql("
        SELECT COUNT(DISTINCT userid)
        FROM {logstore_standard_log}
        WHERE action = 'loggedin'
        AND timecreated BETWEEN ? AND ?
    ", [$start, $end]);
    }
}