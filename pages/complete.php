<?php
require_once('../../../config.php');
require_once($CFG->libdir . '/completionlib.php');

$courseid  = required_param('id', PARAM_INT);
$from_cmid = optional_param('from_cmid', 0, PARAM_INT); // function बाट पठाइएको ID समात्ने

require_login($courseid);

$course  = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

$PAGE->set_url('/theme/mytheme/pages/complete.php', ['id' => $courseid]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('coursecompleted', 'completion'));

$completion = new completion_info($course);
$certurl = null;
$lessonurl = null;

// सुरक्षित रूपमा कोर्स होमपेजमा फर्किने URL
$backtocourseurl = (new moodle_url('/theme/mytheme/pages/course.php', ['id' => $courseid]))->out(false);

// १. मुख्य फिक्स: यदि from_cmid आएको छ भने त्यसैलाई सिधै ब्याक बटनको लिङ्क बनाउने
if ($from_cmid > 0) {
    $lessonurl = (new moodle_url('/theme/mytheme/pages/lesson.php', [
        'id' => $courseid,
        'cmid' => $from_cmid
    ]))->out(false);
}

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

    // २. फलब्याक फिक्स: यदि कुनै कारणले $from_cmid खाली भएमा मात्र यो डेटाबेस लुप चल्छ
    if (empty($lessonurl)) {
        $lastlesson = null;
        $allcms = $modinfo->get_cms(); 
        foreach ($allcms as $cm) {
            if ($cm->uservisible && $cm->modname !== 'customcert') {
                $completiondata = $completion->get_data($cm, true, $USER->id);
                if (!empty($completiondata->timemodified)) {
                    $lastlesson = $cm; 
                }
            }
        }

        if ($lastlesson) {
            $lessonurl = (new moodle_url('/theme/mytheme/pages/lesson.php', [
                'id' => $courseid,
                'cmid' => $lastlesson->id
            ]))->out(false);
        }
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

        <!-- सर्टिफिकेट बटन -->
        <?php if ($certurl): ?>
        <a href="<?php echo $certurl; ?>" class="btn btn-success px-4 py-2 text-white">
            <span class="me-2"><i class="fa-solid fa-file-pdf"></i></span>
            <span>Download Certificate</span>
        </a>
        <?php endif; ?>

        <!-- 'Back to Lesson' बटन (केस सेन्सिटिभिटी फिक्स गरिएको र रिएकटिभेट) -->
        <?php if ($lessonurl): ?>
        <a href="<?php echo $lessonurl; ?>" class="amd-lms-btn amd-lms-prev-btn px-4 py-2">
            <span class="amd-lms-icon"><i class="fa-solid fa-arrow-left"></i></span>
            <span class="amd-lms-text">Back to Lesson</span>
        </a>
        <?php endif; ?>

        <!-- सिधै मुख्य कोर्स होमपेजमा फर्किने सुरक्षित बटन -->
        <a href="<?php echo $backtocourseurl; ?>" class="btn btn-outline-secondary px-4 py-2">
            <span class="me-2"><i class="fa-solid fa-house"></i></span>
            <span>Back to Course Home</span>
        </a>
    </div>
</div>

</body>
</html>