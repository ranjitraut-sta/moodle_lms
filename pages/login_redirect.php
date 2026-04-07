<?php
require_once('../../../config.php');

require_login();

global $USER;

// Check if user is logged in
if (isloggedin() && !isguestuser()) {

    $courses = enrol_get_users_courses($USER->id);

    if (!empty($courses)) {
        // Student / normal user dashboard
redirect(new moodle_url('/theme/mytheme/layout/dashboard.php'));
    } else {
        // No course enrolment (admin / new user / empty user)
        redirect(new moodle_url('/my/'));
    }

} else {
    redirect(new moodle_url('/login/index.php'));
}