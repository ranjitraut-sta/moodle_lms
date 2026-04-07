<?php
// pages/enrollcourse.php - Course enrollment page
require_once('../../../config.php');
require_login();

$PAGE->set_url('/theme/mytheme/pages/enrollcourse.php');
$PAGE->set_pagelayout('standard'); 
$PAGE->set_title('Enroll Courses');
$PAGE->set_heading('Enroll Courses');

$PAGE->requires->css('/theme/mytheme/styles/course.css');

$enroll_data = [
    'courses' => [], // Add course list logic here
    'categories' => [],
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('theme_mytheme/dashboard/pages/enrollcourses', $enroll_data);
echo $OUTPUT->footer();
?>

