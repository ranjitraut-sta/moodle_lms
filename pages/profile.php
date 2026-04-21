<?php
// pages/dashboard.php - Moodle dashboard page
require_once('../../../config.php');
require_login();
$PAGE->set_context(context_system::instance());

$PAGE->set_url('/theme/mytheme/layout/profile.php');
$PAGE->set_pagelayout('dashboard');
$PAGE->set_title('Profile');
$PAGE->set_heading(fullname($USER));


// Load CSS
$PAGE->requires->css('/theme/mytheme/styles/user-dash.css');
$PAGE->requires->js('/theme/mytheme/amd/src/user-dash.js', array('type' => 'on-demand'));

// Dynamic data (mock/hardcoded now; replace with real Moodle queries later)
$profile = new \theme_mytheme\StudentDashboard\ProfileData();
$data = $profile->getData();

// echo '<pre>';
// print_r($data);
// echo '</pre>';
// exit;


// Moodle ko default header/navbar bypass garna manual HTML suru gareko
echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS User Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php echo $OUTPUT->standard_head_html(); ?>
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>
    <?php echo $OUTPUT->standard_top_of_body_html(); ?>
    <?php
    $data['body_content'] = $OUTPUT->render_from_template('theme_mytheme/dashboard/pages/profile', $data);
    echo $OUTPUT->render_from_template('theme_mytheme/dashboard_layout', $data);

    echo $OUTPUT->standard_end_of_body_html(); ?>
</body>

</html>