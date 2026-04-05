<?php
require_once('../../../config.php');

$courseid = required_param('id', PARAM_INT);

require_login();
require_sesskey();

$course  = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

$PAGE->set_context($context);
$PAGE->set_url('/theme/mytheme/pages/enroll.php', ['id' => $courseid]);

if (!is_enrolled($context, $USER)) {
    $enrolinstance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'self', 'status' => 0]);
    if ($enrolinstance) {
        $roleid = $enrolinstance->roleid ?: 5;
        $now    = time();
        // Insert user_enrolments directly — no email hook triggered
        $ue = new stdClass();
        $ue->status       = 0;
        $ue->enrolid      = $enrolinstance->id;
        $ue->userid       = $USER->id;
        $ue->timestart    = $now;
        $ue->timeend      = 0;
        $ue->modifierid   = $USER->id;
        $ue->timecreated  = $now;
        $ue->timemodified = $now;
        $DB->insert_record('user_enrolments', $ue);
        // Assign role
        role_assign($roleid, $USER->id, $context->id);
    }
}

// Redirect to first lesson
$modinfo = get_fast_modinfo($course);
foreach ($modinfo->get_section_info_all() as $section) {
    if (!$section->uservisible) continue;
    if (!empty($modinfo->sections[$section->section])) {
        foreach ($modinfo->sections[$section->section] as $cmid) {
            $cm = $modinfo->cms[$cmid];
            if ($cm->uservisible) {
                redirect(new moodle_url('/theme/mytheme/pages/lesson.php', ['id' => $courseid, 'cmid' => $cm->id]));
            }
        }
    }
}

redirect(new moodle_url('/theme/mytheme/pages/course.php', ['id' => $courseid]));
