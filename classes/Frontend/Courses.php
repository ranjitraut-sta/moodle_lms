<?php

namespace theme_mytheme\Frontend;

defined('MOODLE_INTERNAL') || die();

class Courses
{
    public function __construct()
    {
    }

    public function getData(): array
    {
        return [
            'courses' => $this->getAllCourses(),
        ];
    }

    public function getAllCourses(): array
    {
        global $DB, $CFG;

        $now = time();

        $sql = "SELECT c.id, c.fullname, c.shortname, c.summary,
                       MAX(ue.id) AS is_enrolled,
                       MAX(ue.timestart) AS enrolled_on,
                       MAX(cc.timecompleted) AS completed_on
                FROM {course} c
                LEFT JOIN {enrol} e ON e.courseid = c.id
                LEFT JOIN {user_enrolments} ue ON ue.enrolid = e.id AND ue.userid = ?
                LEFT JOIN {course_completions} cc 
                    ON cc.course = c.id AND cc.userid = ?
                WHERE c.id != 1 AND c.visible = 1
                GROUP BY c.id, c.fullname, c.shortname, c.summary
                ORDER BY c.fullname ASC";

        // Pass 0 as the 'limitfrom' and 3 as the 'limitnum' parameters
        $records = $DB->get_records_sql($sql, [$this->user->id, $this->user->id], 0, 3);

        foreach ($records as &$course) {
            // ... (Your existing logic for image and link processing remains the same)
            $course->enrolled = !empty($course->is_enrolled);
            // ...
        }

        return array_values($records);
    }
}
