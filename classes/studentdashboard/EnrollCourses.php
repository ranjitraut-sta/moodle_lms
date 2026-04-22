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

        $now = time();

        $sql = "SELECT c.id, c.fullname, c.shortname, c.summary,
                       ue.timestart AS enrolled_on,
                       cc.timecompleted AS completed_on
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                LEFT JOIN {course_completions} cc 
                    ON cc.course = c.id AND cc.userid = ue.userid
                WHERE ue.userid = ? AND c.id != 1 AND c.visible = 1
                    AND ue.status = 0
                    AND (ue.timestart = 0 OR ue.timestart <= ?)
                    AND (ue.timeend = 0 OR ue.timeend > ?)
                ORDER BY ue.timestart DESC";

        $records = $DB->get_records_sql($sql, [$this->user->id, $now, $now]);

        foreach ($records as &$course) {

            // =========================
            // STATUS
            // =========================
            $course->status = !empty($course->completed_on) ? 'completed' : 'running';

            // =========================
            // FIRST LESSON (safe modinfo)
            // =========================
            $modinfo = get_fast_modinfo($course->id, $this->user->id);

            $firstcmid = null;

            foreach ($modinfo->cms as $cm) {
                if ($cm->uservisible) {
                    $firstcmid = $cm->id;
                    break;
                }
            }

            // =========================
            // COURSE LINK (CUSTOM LESSON FLOW)
            // =========================
            $course->course_link = $firstcmid
                ? (new \moodle_url('/theme/mytheme/pages/lesson.php', [
                    'id' => $course->id,
                    'cmid' => $firstcmid
                ]))->out(false)
                : (new \moodle_url('/theme/mytheme/pages/course.php', [
                    'id' => $course->id
                ]))->out(false);

            // =========================
            // COURSE IMAGE (MOODLE SAFE WAY)
            // =========================
            $courseobj = new \core_course_list_element($course);

            $imageurl = '';

            $context = \context_course::instance($course->id);
            \context_helper::preload_from_record((object)[
                'id' => $context->id,
                'contextlevel' => CONTEXT_COURSE,
                'instanceid' => $course->id
            ]);


            foreach ($courseobj->get_course_overviewfiles() as $file) {
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

            // =========================
            // FALLBACK IMAGE
            // =========================
            $course->image = $imageurl ?: ($CFG->wwwroot . '/theme/image.php?theme=boost&component=core&image=f2');
        }

        return array_values($records);
    }
}
