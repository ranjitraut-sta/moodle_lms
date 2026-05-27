<?php
require_once('../../../config.php');
require_once($CFG->libdir . '/completionlib.php');

$courseid = required_param('id', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$state = required_param('state', PARAM_INT);

require_login($courseid);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);

$completion = new completion_info($course);
if ($cm->completion == COMPLETION_TRACKING_MANUAL) {
    // Use Moodle's completion API instead of direct DB manipulation
    $completion_state = $state ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    $completion->update_state($cm, $completion_state, $USER->id);
}

redirect(new moodle_url('/theme/mytheme/pages/lesson.php', ['id' => $courseid, 'cmid' => $cmid]));
