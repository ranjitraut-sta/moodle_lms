<?php
require_once('../../../config.php');
require_once($CFG->libdir . '/completionlib.php');

$courseid = required_param('id', PARAM_INT);
require_login($courseid);

$course  = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

$PAGE->set_url('/theme/mytheme/pages/complete.php', ['id' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('coursecompleted', 'completion'));

$completion = new completion_info($course);
$certurl = $lessonurl = null;

if ($completion->is_enabled()) {
    // Mark course complete
    $ccompletion = new completion_completion(['userid' => $USER->id, 'course' => $courseid]);
    $CFG->noemailever = true;
    $ccompletion->mark_complete();
    $CFG->noemailever = false;

    $modinfo = get_fast_modinfo($course);

    // Auto issue first visible certificate
    foreach ($modinfo->get_instances_of('customcert') as $cm) {
        if ($cm->uservisible) {
            $issue = $DB->get_record('customcert_issues', [
                'userid' => $USER->id,
                'customcertid' => $cm->instance
            ]);
            if (!$issue) {
                $issueid = $DB->insert_record('customcert_issues', [
                    'code' => 'auto',
                    'userid' => $USER->id,
                    'customcertid' => $cm->instance,
                    'timecreated' => time()
                ]);
            } else {
                $issueid = $issue->id;
            }

            $certurl = (new moodle_url('/mod/customcert/view.php', [
                'id' => $cm->id,
                'downloadown' => 1,
                'issueid' => $issueid
            ]))->out(false);

            break; // only first visible certificate
        }
    }

    // Find last accessed lesson/module
    $lastlesson = null;
    $allcms = $modinfo->get_cms(); // returns array
    foreach ($allcms as $cm) {
        if ($cm->uservisible && $cm->modname !== 'customcert') {
            $completiondata = $completion->get_data($cm, true, $USER->id);
            if (!empty($completiondata->timemodified)) {
                $lastlesson = $cm; // last accessed module
            }
        }
    }

    if ($lastlesson) {
        // Point to custom lesson.php page
        $lessonurl = new moodle_url('/theme/mytheme/pages/lesson.php', [
            'id' => $courseid,
            'cmid' => $lastlesson->id
        ]);
        $lessonurl = $lessonurl->out(false);
    }
}

$bootstrapcss = (new moodle_url('/theme/mytheme/styles/bootstrap.min.css'))->out(false);
$allcss       = (new moodle_url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css'))->out(false);
$coursecss    = (new moodle_url('/theme/mytheme/styles/course.css'))->out(false);

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Completed</title>
    <link rel="stylesheet" href="<?php echo $bootstrapcss; ?>">
    <link rel="stylesheet" href="<?php echo $allcss; ?>">
    <link rel="stylesheet" href="<?php echo $coursecss; ?>">
</head>
<body style="background:#f8f9fa;">

<div class="d-flex flex-column align-items-center justify-content-center" style="min-height:100vh; text-align:center; padding:2rem;">

    <div class="mb-4">
        <i class="fa-solid fa-circle-check" style="font-size:5rem; color:#28a745;"></i>
    </div>

    <h1 class="fw-bold mb-2" style="color:#1a1a2e;">Congratulations!</h1>
    <p class="text-muted mb-1" style="font-size:1.1rem;">You have successfully completed</p>
    <h3 class="fw-bold mb-4" style="color:var(--amd-secondary);"><?php echo format_string($course->fullname); ?></h3>

    <div class="d-flex flex-wrap gap-3 justify-content-center mt-2">
        <?php if ($certurl): ?>
        <a href="<?php echo $certurl; ?>" class="amd-lms-btn amd-lms-next-btn px-4 py-2" target="_blank">
            <span class="amd-lms-icon"><i class="fa-solid fa-certificate"></i></span>
            <span class="amd-lms-text">View Certificate</span>
        </a>
        <?php endif; ?>

        <?php if ($lessonurl): ?>
        <a href="<?php echo $lessonurl; ?>" class="amd-lms-btn amd-lms-prev-btn px-4 py-2">
            <span class="amd-lms-icon"><i class="fa-solid fa-arrow-left"></i></span>
            <span class="amd-lms-text">Back to Lesson</span>
        </a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>