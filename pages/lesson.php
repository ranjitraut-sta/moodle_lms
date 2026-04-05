<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/theme/mytheme/lib.php');
require_once($CFG->libdir . '/completionlib.php');

// Required parameters
$courseid = required_param('id', PARAM_INT);
$cmid     = required_param('cmid', PARAM_INT);

// User login check
require_login($courseid);

// Fetch course and module
$course  = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$cm      = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
$context = context_module::instance($cmid);

// Redirect Admins/Teachers to standard Moodle activity view
if (has_capability('moodle/course:manageactivities', $context)) {
    redirect(new moodle_url('/mod/' . $cm->modname . '/view.php', ['id' => $cm->id]));
}

// Server-side lock check — block direct URL access to locked lessons
$modinfo  = get_fast_modinfo($course);
$allcms   = [];
foreach ($modinfo->get_section_info_all() as $section) {
    if (!$section->uservisible) continue;
    if (!empty($modinfo->sections[$section->section])) {
        foreach ($modinfo->sections[$section->section] as $modnumber) {
            $mod = $modinfo->cms[$modnumber];
            if ($mod->uservisible) $allcms[] = $mod;
        }
    }
}
$currentindex = array_search($cmid, array_column($allcms, 'id'));
if ($currentindex > 0) {
    $prevmod = $allcms[$currentindex - 1];
    if ($prevmod->completion != COMPLETION_TRACKING_NONE) {
        $prevdone = $DB->record_exists('course_modules_completion', [
            'coursemoduleid' => $prevmod->id,
            'userid'         => $USER->id,
            'completionstate'=> COMPLETION_COMPLETE,
        ]);
        if (!$prevdone) {
            redirect(
                new moodle_url('/theme/mytheme/pages/lesson.php', ['id' => $courseid, 'cmid' => $prevmod->id]),
                'Complete the previous lesson first.',
                null,
                \core\output\notification::NOTIFY_WARNING
            );
        }
    }
}

// Page setup
$PAGE->set_url('/theme/mytheme/pages/lesson.php', ['id' => $courseid, 'cmid' => $cmid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard'); // instead of 'embedded'
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->fullname);

// Mark activity completion (viewed)
$completion = new completion_info($course);
if ($completion->is_enabled($cm)) {
    if ($cm->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $completion->set_module_viewed($cm);
    } elseif ($cm->completion == COMPLETION_TRACKING_MANUAL) {
        $completion->update_state($cm, COMPLETION_COMPLETE);
    }
}

// Template context
$templatecontext = array_merge(
    theme_mytheme_get_lesson_context($cmid),
    theme_mytheme_get_base_context()
);

// CSS/JS includes
$bootstrapcss  = (new moodle_url('/theme/mytheme/styles/bootstrap.min.css'))->out(false);
$biconscss     = (new moodle_url('/theme/mytheme/styles/bootstrap-icons.min.css'))->out(false);
$allcss        = (new moodle_url('/theme/mytheme/styles/all.min.css'))->out(false);
$coursecss     = (new moodle_url('/theme/mytheme/styles/course.css'))->out(false);
$coursejs      = (new moodle_url('/theme/mytheme/amd/src/course.js'))->out(false);
$jquery        = (new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js'))->out(false);
$quizjs        = (new moodle_url('/theme/mytheme/amd/src/quiz.js'))->out(false);

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>">
    <link rel="stylesheet" href="<?php echo $bootstrapcss; ?>">
    <link rel="stylesheet" href="<?php echo $biconscss; ?>">
    <link rel="stylesheet" href="<?php echo $allcss; ?>">
    <link rel="stylesheet" href="<?php echo $coursecss; ?>">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.7.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" 
        integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" 
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<?php 
// Render the lesson template
echo $OUTPUT->render_from_template('theme_mytheme/lesson_detail', $templatecontext); 
?>

<script src="<?php echo $coursejs; ?>"></script>
<script src="<?php echo $jquery; ?>"></script>
<script src="<?php echo $quizjs; ?>"></script>







</body>
</html>