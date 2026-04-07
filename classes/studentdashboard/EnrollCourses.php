<?php

namespace theme_mytheme\StudentDashboard;

defined('MOODLE_INTERNAL') || die();

class EnrollCourses
{
    protected $user;

    public function __construct($user = null)
    {
        global $USER;
        $this->user = $user ?? $USER;
    }

    public function getData(): array
    {
        return [
            'enrolled_courses' => $this->getEnrolledCourses(),
        ];
    }

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




}