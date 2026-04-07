<?php
// pages/dashboard.php - Moodle dashboard page
require_once('../../../config.php');
require_login();

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/theme/mytheme/pages/enrollcourses.php');
$PAGE->set_pagelayout('dashboard'); 
$PAGE->set_title('Dashboard');
$PAGE->set_heading(fullname($USER));

// Load CSS
$PAGE->requires->css('/theme/mytheme/styles/user-dash.css');
$PAGE->requires->js(new moodle_url('/theme/mytheme/amd/src/user-dash.js'));

// Dynamic data (mock/hardcoded now; replace with real Moodle queries later)
// ४. डेटा तयार गर्ने (Namespace प्रयोग गरेर कल गर्ने)
$dashboard_preparer = new \theme_mytheme\StudentDashboard\EnrollCourses($USER);
$data = $dashboard_preparer->getData();

// echo "<pre>";
// var_dump($data);
// echo "</pre>";

// die("DEBUG STOP");
// Load dashboard renderer
echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $PAGE->title; ?></title>
    <?php echo $OUTPUT->standard_head_html(); ?>
</head>
<body <?php echo $OUTPUT->body_attributes(); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<?php
echo $OUTPUT->render_from_template('theme_mytheme/dashboard/main/header', $data);
echo $OUTPUT->render_from_template('theme_mytheme/dashboard/main/sidebar', $data);

// Render dashboard using mustache templates
echo $OUTPUT->render_from_template('theme_mytheme/dashboard/pages/enrollcourses', $data);

echo $OUTPUT->render_from_template('theme_mytheme/dashboard/main/footer', $data);

echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>