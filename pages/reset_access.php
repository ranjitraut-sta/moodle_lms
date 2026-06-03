<?php

require_once('../../../config.php');

require_login();

global $DB, $USER;

/**
 * ==================================================
 * 1. PARAMS & COURSE VALIDATION
 * ==================================================
 */
$courseid = required_param('courseid', PARAM_INT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

/**
 * ==================================================
 * 2. GET USER ENROLMENT
 * ==================================================
 */
$enrol = $DB->get_record_sql("
    SELECT ue.*
    FROM {user_enrolments} ue
    JOIN {enrol} e ON e.id = ue.enrolid
    WHERE ue.userid = :userid
      AND e.courseid = :courseid
    ORDER BY ue.timestart DESC
", [
    'userid' => $USER->id,
    'courseid' => $courseid
]);

if (!$enrol) {
    redirect(
        new moodle_url('/theme/mytheme/pages/course.php', ['id' => $courseid]),
        'You are not enrolled in this course',
        2
    );
}

/**
 * ==================================================
 * 3. CREATE NEW ATTEMPT
 * ==================================================
 */
$attempt = new stdClass();
$attempt->userid = $USER->id;
$attempt->courseid = $courseid;
$attempt->attempt_no = get_next_attempt($USER->id, $courseid);
$attempt->timestart = time();
$attempt->timeend = null;
$attempt->status = 'active';

$DB->insert_record('course_attempts', $attempt);

/**
 * ==================================================
 * 4. RESET PROGRESS DATA
 * ==================================================
 */

// course completion reset
$DB->delete_records('course_completions', [
    'userid' => $USER->id,
    'course' => $courseid
]);

// grade reset (optional)
$DB->delete_records('grade_grades', [
    'userid' => $USER->id
]);

/**
 * ==================================================
 * 5. REFRESH ENROLMENT (IMPORTANT FOR EXPIRY)
 * ==================================================
 */
$now = time();

$DB->set_field('user_enrolments', 'timestart', $now, [
    'id' => $enrol->id
]);

$DB->set_field('user_enrolments', 'timemodified', $now, [
    'id' => $enrol->id
]);

/**
 * ==================================================
 * 6. GET FIRST COURSE MODULE (cmid)
 * ==================================================
 */
$firstcm = $DB->get_record_sql("
    SELECT cm.id
    FROM {course_modules} cm
    JOIN {modules} m ON m.id = cm.module
    WHERE cm.course = :courseid
    ORDER BY cm.id ASC
    LIMIT 1
", [
    'courseid' => $courseid
]);

if (!$firstcm) {
    redirect(
        new moodle_url('/theme/mytheme/pages/course.php', ['id' => $courseid]),
        'No lessons found in this course',
        2
    );
}

/**
 * ==================================================
 * 7. REDIRECT TO FIRST LESSON
 * ==================================================
 */
redirect(
    new moodle_url('/theme/mytheme/pages/lesson.php', [
        'id' => $courseid,
        'cmid' => $firstcm->id
    ]),
    'Course restarted successfully!',
    1
);

/**
 * ==================================================
 * 8. GET NEXT ATTEMPT NUMBER
 * ==================================================
 */
function get_next_attempt($userid, $courseid)
{
    global $DB;

    $last = $DB->get_field_sql("
        SELECT MAX(attempt_no)
        FROM {course_attempts}
        WHERE userid = ? AND courseid = ?
    ", [$userid, $courseid]);

    return $last ? ($last + 1) : 1;
}