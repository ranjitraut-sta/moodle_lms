<?php
// pages/certificates.php - Certificates page
require_once('../../../config.php');
require_login();

$PAGE->set_context(context_system::instance());

$PAGE->set_url('/theme/mytheme/pages/enrollcourses.php');
$PAGE->set_pagelayout('dashboard');
$PAGE->set_title('Enroll Courses');
$PAGE->set_heading('Enroll Courses');

$cert_data = [
    'certificates' => [], // Add certificates logic
];

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
    // १. पहिले सर्टिफिकेटको मुख्य सामग्री रेन्डर गर्ने
    $cert_data['body_content'] = $OUTPUT->render_from_template('theme_mytheme/dashboard/pages/enrollcourses', $cert_data);

    // २. अब एउटै लेआउट टेम्प्लेट कल गर्ने जसले सबै कुरा मिलाउँछ
    echo $OUTPUT->render_from_template('theme_mytheme/dashboard_layout', $cert_data);

    echo $OUTPUT->standard_end_of_body_html(); ?>
</body>

</html>