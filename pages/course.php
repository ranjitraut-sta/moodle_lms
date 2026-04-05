<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/theme/mytheme/lib.php');
require_once($CFG->dirroot . '/theme/mytheme/locallib.php');

$courseid = required_param('id', PARAM_INT);
$justenrolled = optional_param('enrolled', 0, PARAM_INT);

// Login required only if enrolled, otherwise show course detail
require_login(null, false);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

// Redirect to first lesson after enroll
if ($justenrolled && is_enrolled($context, $USER)) {
    $modinfo = get_fast_modinfo($course);
    foreach ($modinfo->get_section_info_all() as $section) {
        if (!$section->uservisible) continue;
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $cmid) {
                $cm = $modinfo->cms[$cmid];
                if ($cm->uservisible) {
                    redirect(new moodle_url('/theme/mytheme/pages/lesson.php', ['id' => $courseid, 'cmid' => $cm->id]));
                }
            }
        }
    }
}

$PAGE->set_url('/theme/mytheme/pages/course.php', ['id' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('base');
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->fullname);

$templatecontext = array_merge(
    theme_mytheme_get_course_context($courseid),
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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body <?php echo $OUTPUT->body_attributes(); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<?php
echo $OUTPUT->render_from_template('theme_mytheme/header', $templatecontext);
echo $OUTPUT->render_from_template('theme_mytheme/course_detail', $templatecontext);
echo $OUTPUT->render_from_template('theme_mytheme/footer', $templatecontext);
?>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>