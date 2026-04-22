<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/theme/mytheme/lib.php');

$categoryid = optional_param('category', 0, PARAM_INT);

$PAGE->set_url('/theme/mytheme/pages/courselist.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('base');
$PAGE->set_title(get_string('courses'));
$PAGE->set_heading(get_string('courses'));

$templatecontext = array_merge(
    theme_mytheme_get_course_list_context($categoryid),
    theme_mytheme_get_base_context()
);

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>

<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo new moodle_url('/theme/mytheme/styles/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo new moodle_url('/theme/mytheme/styles/bootstrap-icons.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo new moodle_url('/theme/mytheme/styles/all.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo new moodle_url('/theme/mytheme/styles/main.css'); ?>">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">

        <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>
    <?php echo $OUTPUT->standard_top_of_body_html(); ?>

    <?php
    echo $OUTPUT->render_from_template('theme_mytheme/header', $templatecontext);
    echo $OUTPUT->render_from_template('theme_mytheme/course_list', $templatecontext);
    echo $OUTPUT->render_from_template('theme_mytheme/footer', $templatecontext);
    ?>

    <?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>

</html>