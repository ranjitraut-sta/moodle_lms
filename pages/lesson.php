<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/theme/mytheme/lib.php');
require_once($CFG->libdir . '/completionlib.php');

// Required parameters
$courseid = required_param('id', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);

// User login check
require_login($courseid);



// Fetch course and module
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
$context = context_module::instance($cmid);

// Redirect Admins/Teachers to standard Moodle activity view
if (has_capability('moodle/course:manageactivities', $context)) {
    redirect(new moodle_url('/mod/' . $cm->modname . '/view.php', ['id' => $cm->id]));
}

// Server-side lock check — block direct URL access to locked lessons
$modinfo = get_fast_modinfo($course);
$allcms = [];
foreach ($modinfo->get_section_info_all() as $section) {
    if (!$section->uservisible)
        continue;
    if (!empty($modinfo->sections[$section->section])) {
        foreach ($modinfo->sections[$section->section] as $modnumber) {
            $mod = $modinfo->cms[$modnumber];
            if ($mod->uservisible)
                $allcms[] = $mod;
        }
    }
}
$currentindex = array_search($cmid, array_column($allcms, 'id'));
if ($currentindex > 0) {
    $prevmod = $allcms[$currentindex - 1];
    if ($prevmod->completion != COMPLETION_TRACKING_NONE) {
        $prevdone = theme_mytheme_check_module_completed($prevmod, $USER->id, $course);
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

// Module Delay Lock Check
if (file_exists($CFG->dirroot . '/local/moduledelay/lib.php')) {
    require_once($CFG->dirroot . '/local/moduledelay/lib.php');
    if (function_exists('local_moduledelay_can_access')) {
        $delaycheck = local_moduledelay_can_access($USER->id, $courseid, $cmid);
        if (!$delaycheck['allowed']) {
            $minutes = floor($delaycheck['remaining'] / 60);
            $seconds = $delaycheck['remaining'] % 60;
            $timeText = $minutes > 0 ? $minutes . ' min ' . $seconds . ' sec' : $seconds . ' sec';

            // Redirect back to the previous module if we have it, else course page
            $redirecturl = (isset($prevmod) && $prevmod) ?
                new moodle_url('/theme/mytheme/pages/lesson.php', ['id' => $courseid, 'cmid' => $prevmod->id]) :
                new moodle_url('/theme/mytheme/pages/course.php', ['id' => $courseid]);

            redirect(
                $redirecturl,
                'This lesson is locked. Please wait ' . $timeText . ' before accessing.',
                null,
                \core\output\notification::NOTIFY_ERROR
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
    }
}

// Trigger course_module_viewed event so standard logs and plugins like local_moduledelay can track it
try {
    $eventclass = '\\mod_' . $cm->modname . '\\event\\course_module_viewed';
    if (class_exists($eventclass)) {
        $event = $eventclass::create([
            'objectid' => $cm->instance,
            'context' => $context,
            'courseid' => $courseid
        ]);
        $event->trigger();
    }
} catch (\Exception $e) {
    // Ignore if event class doesn't exist or fails to trigger
}

// Template context
$templatecontext = array_merge(
    theme_mytheme_get_lesson_context($cmid),
    theme_mytheme_get_base_context()
);

// CSS/JS includes
$bootstrapcss = (new moodle_url('/theme/mytheme/styles/bootstrap.min.css'))->out(false);
$biconscss = (new moodle_url('/theme/mytheme/styles/bootstrap-icons.min.css'))->out(false);
$allcss = (new moodle_url('/theme/mytheme/styles/all.min.css'))->out(false);
$coursecss = (new moodle_url('/theme/mytheme/styles/course.css'))->out(false);

$jquery = (new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js'))->out(false);
$coursejs = (new moodle_url('/theme/mytheme/amd/src/course.js'))->out(false);
$quizjs = (new moodle_url('/theme/mytheme/amd/src/quiz.js'))->out(false);
$folderJs = (new moodle_url('/theme/mytheme/amd/src/folder.js'))->out(false);
$forumJs = (new moodle_url('/theme/mytheme/amd/src/forum.js'))->out(false);
$youtubeJs = (new moodle_url('/theme/mytheme/amd/src/youtube.js'))->out(false);
$scromJs = (new moodle_url('/theme/mytheme/amd/src/scrom.js'))->out(false);
$PAGE->requires->js(new moodle_url('/theme/mytheme/amd/src/forum.js'));
$PAGE->requires->js(new moodle_url('/theme/mytheme/amd/src/youtube.js'));
$PAGE->requires->js(new moodle_url('/theme/mytheme/amd/src/scrom.js'));

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

    // Render the activity header for this lesson page (guard in case renderer doesn't provide it)
    if (method_exists($OUTPUT, 'activity_header')) {
        echo $OUTPUT->activity_header();
    }

    // Render the lesson template
    echo $OUTPUT->render_from_template('theme_mytheme/lesson_detail', $templatecontext);
    ?>

    <script src="<?php echo $jquery; ?>"></script>
    <script src="<?php echo $coursejs; ?>"></script>
    <script src="<?php echo $quizjs; ?>"></script>
    <script src="<?php echo $folderJs; ?>"></script>
    <script src="<?php echo $forumJs; ?>"></script>
    <script src="<?php echo $youtubeJs; ?>"></script>
    <script src="<?php echo $scromJs; ?>"></script>

    <?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>

</html>