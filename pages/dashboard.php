<?php
// pages/dashboard.php - Moodle dashboard page
require_once('../../../config.php');
require_login();

$PAGE->set_context(context_system::instance());

$PAGE->set_url('/theme/mytheme/pages/dashboard.php');
$PAGE->set_pagelayout('dashboard');
$PAGE->set_title('Dashboard');
$PAGE->set_heading(fullname($USER));

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
    $data['body_content'] = $OUTPUT->render_from_template('theme_mytheme/dashboard/pages/dashboard', $data);
    echo $OUTPUT->render_from_template('theme_mytheme/dashboard_layout', $data);

    echo $OUTPUT->standard_end_of_body_html(); ?>
</body>

</html>