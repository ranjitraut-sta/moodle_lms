<?php

namespace theme_mytheme\StudentDashboard;

defined('MOODLE_INTERNAL') || die();

class DashboardDataPrepare
{
    protected $user;

    public function __construct($user = null)
    {
        global $USER;
        $this->user = $user ?? $USER;
    }

    /**
     * सम्पूर्ण ड्यासबोर्ड डेटा तयार गर्ने मुख्य फङ्सन
     */
    public function getData(): array
    {
        return [
            'user_fullname' => fullname($this->user),
            'user_firstname' => $this->user->firstname,
            'user_profile_pix' => $this->get_user_picture(),
            'enrolled_courses_count' => $this->getEnrolledCoursesCount(),
            'completed_courses_count' => $this->getCompletedCoursesCount(),
            'total_certificates' => $this->getTotalCertificates(),
            'pending_assignments' => $this->getPendingAssignmentsCount(),
            'running_courses' => $this->getRunningCourses(),
            'enrolled_courses' => $this->getEnrolledCourses(),
            'recent_courses' => $this->getRecentCourses(),
            'course_progress' => $this->getCourseProgressChart(),
            'assignment' => $this->getAssignmentChart(),
            'activity' => $this->getWeeklyActivity(),
        ];
    }

    /**
     * १. इन्रोल भएका कोर्सहरूको संख्या
     */
    protected function getEnrolledCoursesCount(): int
    {
        global $DB;
        $sql = "SELECT COUNT(ue.id)
                FROM {user_enrolments} ue
                JOIN {enrol} e ON e.id = ue.enrolid
                WHERE ue.userid = ?";
        return (int) $DB->count_records_sql($sql, [$this->user->id]);
    }

    /**
     * २. पूर्ण भएका कोर्सहरूको संख्या
     */
    protected function getCompletedCoursesCount(): int
    {
        global $DB;
        $sql = "SELECT COUNT(DISTINCT course)
                FROM {course_completions}
                WHERE userid = ? AND timecompleted > 0";
        return (int) $DB->count_records_sql($sql, [$this->user->id]);
    }

    /**
     * ३. सर्टिफिकेटको संख्या (Custom Certificate Plugin)
     */
    protected function getTotalCertificates(): int
    {
        global $DB;
        if (!$DB->get_manager()->table_exists('customcert_issues')) {
            return 0;
        }
        return (int) $DB->count_records('customcert_issues', ['userid' => $this->user->id]);
    }

    /**
     * ४. हाल चलिरहेका (Running) कोर्सहरू
     */
    protected function getRunningCourses(): array
    {
        global $DB;
        $sql = "SELECT c.id, c.fullname, c.shortname
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                LEFT JOIN {course_completions} cc ON cc.course = c.id AND cc.userid = ue.userid
                WHERE ue.userid = ? AND c.visible = 1 AND cc.timecompleted IS NULL AND c.id != 1
                ORDER BY ue.timestart DESC";
        return array_values($DB->get_records_sql($sql, [$this->user->id]));
    }

    /**
     * ५. बुझाउन बाँकी असाइनमेन्टहरू
     */
    protected function getPendingAssignmentsCount(): int
    {
        global $DB;
        $sql = "SELECT COUNT(a.id)
                FROM {assign} a
                JOIN {course} c ON a.course = c.id
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                WHERE ue.userid = ? AND a.duedate > ? 
                AND NOT EXISTS (SELECT s.id FROM {assign_submission} s WHERE s.assignment = a.id AND s.userid = ? AND s.status = 'submitted')";
        return (int) $DB->count_records_sql($sql, [$this->user->id, time(), $this->user->id]);
    }

    /**
     * ६. विस्तृत इन्रोलमेन्ट सूची (Detailed Course List)
     */
    public function getEnrolledCourses(): array
    {
        global $DB;
        $sql = "SELECT c.id, c.fullname, c.shortname, c.summary, ue.timestart AS enrolled_on, cc.timecompleted AS completed_on
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                LEFT JOIN {course_completions} cc ON cc.course = c.id AND cc.userid = ue.userid
                WHERE ue.userid = ? AND c.id != 1 AND c.visible = 1
                ORDER BY ue.timestart DESC";

        $records = $DB->get_records_sql($sql, [$this->user->id]);

        foreach ($records as &$course) {
            $course->status = !empty($course->completed_on) ? 'completed' : 'running';
            $course->course_link = (new \moodle_url('/course/view.php', ['id' => $course->id]))->out(false);
        }
        return array_values($records);
    }

    /**
     * ७. भर्खरै एक्सेस गरिएका कोर्सहरू (Recent 3)
     */
    protected function getRecentCourses(): array
    {
        global $DB;
        $sql = "SELECT c.id, c.fullname, c.shortname
                FROM {user_lastaccess} ul
                JOIN {course} c ON c.id = ul.courseid
                WHERE ul.userid = ? AND c.id != 1
                ORDER BY ul.timeaccess DESC";
        $records = $DB->get_records_sql($sql, [$this->user->id], 0, 3);

        foreach ($records as &$course) {
            $course->course_link = (new \moodle_url('/course/view.php', ['id' => $course->id]))->out(false);
        }
        return array_values($records);
    }

    /**
     * ८. युजरको प्रोफाइल फोटो
     */
    protected function get_user_picture()
    {
        global $OUTPUT;
        return $OUTPUT->user_picture($this->user, array('size' => 100, 'link' => false));
    }

    // 1. Course Progress (Pie)
    protected function getCourseProgressChart(): array
    {
        $completed = $this->getCompletedCoursesCount();
        $total = $this->getEnrolledCoursesCount();
        $running = max($total - $completed, 0);

        return [
            'labels' => ['Completed', 'Running'],
            'data' => [$completed, $running],
        ];
    }

    // 2. Assignment Chart (Bar)
    protected function getAssignmentChart(): array
    {
        global $DB;

        $submitted = $DB->count_records('assign_submission', [
            'userid' => $this->user->id,
            'status' => 'submitted'
        ]);

        $pending = $this->getPendingAssignmentsCount();

        return [
            'labels' => ['Submitted', 'Pending'],
            'data' => [$submitted, $pending],
        ];
    }

    // 3. Weekly Activity (Line)
    protected function getWeeklyActivity(): array
    {
        global $DB;

        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $start = strtotime("-$i days midnight");
            $end = strtotime("-$i days 23:59:59");

            $count = $DB->count_records_select(
                'logstore_standard_log',
                "userid = ? AND timecreated BETWEEN ? AND ?",
                [$this->user->id, $start, $end]
            );

            $labels[] = date('D', $start);
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}