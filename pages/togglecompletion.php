<?php
require_once('../../../config.php');
require_once($CFG->libdir . '/completionlib.php');

$courseid = required_param('id', PARAM_INT);
$cmid     = required_param('cmid', PARAM_INT);
$state    = required_param('state', PARAM_INT);

require_login($courseid);

$course  = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$cm      = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);

$completion = new completion_info($course);
if ($cm->completion == COMPLETION_TRACKING_MANUAL) {
    $existing = $DB->get_record('course_modules_completion', [
        'coursemoduleid' => $cmid,
        'userid'         => $USER->id,
    ]);
    if ($existing) {
        $existing->completionstate = $state ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        $existing->timemodified    = time();
        $DB->update_record('course_modules_completion', $existing);
    } else {
        $DB->insert_record('course_modules_completion', [
            'coursemoduleid' => $cmid,
            'userid'         => $USER->id,
            'completionstate'=> $state ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE,
            'viewed'         => 1,
            'overrideby'     => null,
            'timemodified'   => time(),
        ]);
    }
}

redirect(new moodle_url('/theme/mytheme/pages/lesson.php', ['id' => $courseid, 'cmid' => $cmid]));
