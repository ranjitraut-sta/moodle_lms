<?php

namespace theme_mytheme\StudentDashboard;

defined('MOODLE_INTERNAL') || die();

class OurCourses
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
            'enrolled_courses' => $this->getAllCourses(),
            'user_fullname' => fullname($this->user),
            'user_firstname' => $this->user->firstname,
            'user_profile_pix' => $this->get_user_picture(),
                        'logo' => $this->get_logo_url(),
        ];
    }

    protected function get_user_picture()
    {
        global $OUTPUT;
        return $OUTPUT->user_picture($this->user, array('size' => 100, 'link' => false));
    }

    public function getAllCourses(): array
    {
        global $DB, $CFG;

        $now = time();

        // १. GROUP BY c.id थपिएको छ ताकि एउटा कोर्स एक पटक मात्र आओस्
        // २. MAX() वा MIN() प्रयोग गरेर इनरोलमेन्ट डेटा लिइएको छ
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

        $records = $DB->get_records_sql($sql, [$this->user->id, $this->user->id]);

        foreach ($records as &$course) {
            // बाँकी लजिक उस्तै रहन्छ...
            $course->enrolled = !empty($course->is_enrolled);
            $course->status = !empty($course->completed_on) ? 'completed' : ($course->enrolled ? 'running' : 'not_enrolled');

            $modinfo = get_fast_modinfo($course->id, $this->user->id);
            $firstcmid = null;
            foreach ($modinfo->cms as $cm) {
                if ($cm->uservisible) {
                    $firstcmid = $cm->id;
                    break;
                }
            }

            if ($course->enrolled) {
                $course->course_link = $firstcmid
                    ? (new \moodle_url('/theme/mytheme/pages/lesson.php', ['id' => $course->id, 'cmid' => $firstcmid]))->out(false)
                    : (new \moodle_url('/theme/mytheme/pages/course.php', ['id' => $course->id]))->out(false);
            } else {
                $course->course_link = (new \moodle_url('/theme/mytheme/pages/course.php', ['id' => $course->id]))->out(false);
            }

            $courseobj = new \core_course_list_element($course);
            $imageurl = '';
            foreach ($courseobj->get_course_overviewfiles() as $file) {
                if ($file->is_valid_image()) {
                    $imageurl = \moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        null,
                        $file->get_filepath(),
                        $file->get_filename()
                    )->out(false);
                    break;
                }
            }
            $course->image = $imageurl ?: ($CFG->wwwroot . '/theme/image.php?theme=boost&component=core&image=f2');
        }

        return array_values($records);
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
