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
    global $DB, $CFG;

    $sql = "SELECT c.id, c.fullname, c.shortname, c.summary,
                   ue.timestart AS enrolled_on,
                   cc.timecompleted AS completed_on
            FROM {course} c
            JOIN {enrol} e ON e.courseid = c.id
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            LEFT JOIN {course_completions} cc 
                ON cc.course = c.id AND cc.userid = ue.userid
            WHERE ue.userid = ? AND c.id != 1 AND c.visible = 1
            ORDER BY ue.timestart DESC";

    $records = $DB->get_records_sql($sql, [$this->user->id]);

    foreach ($records as &$course) {

        // status
        $course->status = !empty($course->completed_on) ? 'completed' : 'running';

        // course link
        $course->course_link = (new \moodle_url('/course/view.php', [
            'id' => $course->id
        ]))->out(false);

        // =========================
        // COURSE IMAGE (thumbnail)
        // =========================
        $context = \context_course::instance($course->id);

        $fs = get_file_storage();
        $files = $fs->get_area_files(
            $context->id,
            'course',
            'overviewfiles',
            0,
            'filename',
            false
        );

        $imageurl = null;

        foreach ($files as $file) {
            if ($file->is_valid_image()) {
                $imageurl = \moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
                )->out(false);
                break;
            }
        }

        // fallback image
        $course->image = $imageurl ?: ($CFG->wwwroot . '/theme/image.php?theme=boost&component=core&image=f2');
    }

    return array_values($records);
}




}