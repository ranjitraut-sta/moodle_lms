<?php

namespace theme_mytheme\StudentDashboard;

defined('MOODLE_INTERNAL') || die();

class EnrollCourse
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
            'enrolled_courses_count' => $this->getEnrolledCoursesCount(),
            'completed_courses_count' => $this->getCompletedCoursesCount(),
            'total_certificates' => $this->getTotalCertificates(),
            'running_courses' => $this->getRunningCourses(),
            'enrolled_courses' => $this->getEnrolledCourses(),
        ];
    }

    /**
     * TOTAL ENROLLED COURSES COUNT
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
     * COMPLETED COURSES COUNT
     */
    protected function getCompletedCoursesCount(): int
    {
        global $DB;

        $sql = "SELECT COUNT(DISTINCT course)
                FROM {course_completions}
                WHERE userid = ?
                AND timecompleted IS NOT NULL";

        return (int) $DB->count_records_sql($sql, [$this->user->id]);
    }

    /**
     * TOTAL CERTIFICATES (Custom Certificate plugin)
     */
    protected function getTotalCertificates(): int
    {
        global $DB;

        // safety check
        if (!$DB->get_manager()->table_exists('customcert_issues')) {
            return 0;
        }

        return (int) $DB->count_records('customcert_issues', [
            'userid' => $this->user->id
        ]);
    }

    /**
     * RUNNING COURSES (not completed yet)
     */
    protected function getRunningCourses(): array
    {
        global $DB;

        $sql = "SELECT c.id, c.fullname, c.shortname
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                LEFT JOIN {course_completions} cc 
                       ON cc.course = c.id 
                      AND cc.userid = ue.userid
                WHERE ue.userid = ?
                AND c.visible = 1
                AND cc.timecompleted IS NULL
                ORDER BY ue.timestart DESC";

        return $DB->get_records_sql($sql, [$this->user->id]);
    }

    /**
     * FULL ENROLLED COURSE LIST (DETAILED)
     */
    public function getEnrolledCourses(): array
    {
        global $DB;

        $sql = "SELECT 
                    c.id,
                    c.fullname,
                    c.shortname,
                    c.summary,
                    c.startdate,
                    c.enddate,
                    c.visible,
                    ue.timestart AS enrolled_on,
                    ue.timeend AS enrol_end,
                    cc.timecompleted AS completed_on
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                LEFT JOIN {course_completions} cc 
                       ON cc.course = c.id 
                      AND cc.userid = ue.userid
                WHERE ue.userid = ?
                AND c.id != 1
                AND c.visible = 1
                ORDER BY ue.timestart DESC";

        $records = $DB->get_records_sql($sql, [$this->user->id]);

        // add status field (moodle-style enrichment)
        foreach ($records as &$course) {
            $course->status = !empty($course->completed_on)
                ? 'completed'
                : 'running';
        }

        return array_values($records);
    }
}