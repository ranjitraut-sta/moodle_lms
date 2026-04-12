<?php

use theme_mytheme\lesson\LessonFactory;
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/completionlib.php');

function theme_mytheme_get_last_accessed_lesson($courseid, $userid): ?int
{
    global $DB;
    $sql = "SELECT cm.id
            FROM {course_modules} cm
            JOIN {logstore_standard_log} l ON l.contextinstanceid = cm.id
            WHERE cm.course = :courseid
              AND l.userid = :userid
              AND l.target = 'course_module'
              AND l.action = 'viewed'
            ORDER BY l.timecreated DESC
            LIMIT 1";
    $result = $DB->get_record_sql($sql, ['courseid' => $courseid, 'userid' => $userid]);
    return $result ? $result->id : null;
}

function theme_mytheme_get_course_context($courseid): array
{
    global $DB, $USER;

    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    $context = context_course::instance($courseid);
    $course_obj = new core_course_list_element($course);

    $courseimage = '';
    foreach ($course_obj->get_course_overviewfiles() as $file) {
        $courseimage = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);
        break;
    }

    $modinfo = get_fast_modinfo($course);
    $sections = [];
    $firstlessoncmid = null;
    $lastlessoncmid = theme_mytheme_get_last_accessed_lesson($courseid, $USER->id);

    foreach ($modinfo->get_section_info_all() as $section) {
        if (!$section->uservisible)
            continue;
        $modules = [];
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $cmid) {
                $cm = $modinfo->cms[$cmid];
                if ($cm->uservisible) {
                    if (!$firstlessoncmid)
                        $firstlessoncmid = $cm->id;
                    $modules[] = [
                        'name' => $cm->name,
                        'url' => (new moodle_url('/theme/mytheme/pages/lesson.php', ['id' => $courseid, 'cmid' => $cm->id]))->out(false),
                        'modname' => $cm->modname,
                        'icon' => 'bi bi-file-earmark-text',
                    ];
                }
            }
        }
        if (!empty($modules)) {
            $sections[] = ['name' => get_section_name($course, $section), 'modules' => $modules];
        }
    }

    $enrol = $DB->get_record('enrol', ['courseid' => $courseid], '*', IGNORE_MULTIPLE);
    $enrolledcount = $enrol ? $DB->count_records('user_enrolments', ['enrolid' => $enrol->id]) : 0;
    $category = $DB->get_record('course_categories', ['id' => $course->category]);
    $continuecmid = $lastlessoncmid && isset($modinfo->cms[$lastlessoncmid]) ? $lastlessoncmid : $firstlessoncmid;
    $continueurl = $continuecmid
        ? (new moodle_url('/theme/mytheme/pages/lesson.php', ['id' => $courseid, 'cmid' => $continuecmid]))->out(false)
        : (new moodle_url('/course/view.php', ['id' => $courseid]))->out(false);

    $completion = new completion_info($course);
    $iscompleted = $completion->is_course_complete($USER->id);

    $certurl = null;
    if ($iscompleted) {
        $certcm = $DB->get_record_sql(
            "SELECT cm.id FROM {course_modules} cm
             JOIN {modules} m ON m.id = cm.module
             WHERE cm.course = ? AND m.name = 'customcert' AND cm.visible = 1 LIMIT 1",
            [$courseid]
        );
        if ($certcm) {
            $certurl = (new moodle_url('/mod/customcert/view.php', ['id' => $certcm->id]))->out(false);
        }
    }

    $tagobjects = core_tag_tag::get_item_tags('core', 'course', $courseid);
    $tags = [];
    foreach ($tagobjects as $tag) {
        $tags[] = ['tagname' => $tag->get_display_name()];
    }

    return [
        'courseid' => $courseid,
        'coursename' => format_string($course->fullname),
        'coursesummary' => format_text($course->summary, FORMAT_HTML),
        'courseimage' => $courseimage,
        'courseurl' => (new moodle_url('/theme/mytheme/pages/course.php', ['id' => $courseid]))->out(false),
        'firstlessonurl' => $continueurl,
        'sections' => $sections,
        'enrolledcount' => $enrolledcount,
        'modulecount' => count($modinfo->cms),
        'category' => $category ? format_string($category->name) : 'General',
        'isenrolled' => is_enrolled($context, $USER) ? true : null,
        'sesskey' => sesskey(),
        'iscompleted' => $iscompleted ? true : null,
        'certurl' => $certurl,
        'tags' => !empty($tags) ? $tags : null,
        'hastags' => !empty($tags) ? true : null,
        'wwwroot' => (new moodle_url('/'))->out(false),
    ];
}

function theme_mytheme_get_lesson_context($cmid): array
{
    global $DB, $USER;

    $cm = get_coursemodule_from_id('', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $context = context_module::instance($cmid);
    $modinfo = get_fast_modinfo($course);
    $currentcm = $modinfo->get_cm($cmid);
    $modtype = $currentcm->modname;

    // ✅ MODULE DATA (Factory) (classes / lesson / LessonModuleInterface.php)
    $module = LessonFactory::create($modtype, $cm, $DB, $context, $cmid);
    $moduledata = $module->getData();

    // --- Completion ---
    $completion = new completion_info($course);
    $ismanualcompletion = ($currentcm->completion == COMPLETION_TRACKING_MANUAL);

    $cdata = $completion->get_data($currentcm, false, $USER->id);

    $ismarkedcomplete = $ismanualcompletion
        && isset($cdata->completionstate)
        && $cdata->completionstate == COMPLETION_COMPLETE
        && $DB->record_exists('course_modules_completion', [
            'coursemoduleid' => $cmid,
            'userid' => $USER->id,
            'completionstate' => COMPLETION_COMPLETE,
        ]);

    $iscurrentcomplete = ($currentcm->completion == COMPLETION_TRACKING_NONE)
        || ($currentcm->completion == COMPLETION_TRACKING_AUTOMATIC && in_array($cdata->completionstate, [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS]))
        || ($currentcm->completion == COMPLETION_TRACKING_MANUAL && $ismarkedcomplete);

    $toggleurl = (new moodle_url('/theme/mytheme/pages/togglecompletion.php', [
        'id' => $course->id,
        'cmid' => $cmid,
        'state' => $ismarkedcomplete ? 0 : 1,
    ]))->out(false);

    // --- Navigation ---
    $iconmap = [
        'page' => 'file-earmark-text',
        'resource' => 'file-earmark-pdf',
        'url' => 'link-45deg',
        'quiz' => 'question-circle',
        'forum' => 'chat-dots',
        'book' => 'book'
    ];

    $allmodules = [];
    $currentindex = -1;

    foreach ($modinfo->get_section_info_all() as $section) {
        if (!$section->uservisible)
            continue;

        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($mod->uservisible) {
                    $allmodules[] = $mod;

                    if ($mod->id == $cmid) {
                        $currentindex = count($allmodules) - 1;
                    }
                }
            }
        }
    }

    $sections = [];

    foreach ($modinfo->get_section_info_all() as $section) {
        if (!$section->uservisible)
            continue;

        $modules = [];

        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {

                $mod = $modinfo->cms[$modnumber];
                if (!$mod->uservisible)
                    continue;

                $modindex = array_search($mod, $allmodules);

                $modcompleted = $DB->record_exists('course_modules_completion', [
                    'coursemoduleid' => $mod->id,
                    'userid' => $USER->id,
                    'completionstate' => COMPLETION_COMPLETE,
                ]);

                $islocked = false;

                if ($modindex > 0) {
                    $prevmod = $allmodules[$modindex - 1];

                    if ($prevmod->completion != COMPLETION_TRACKING_NONE) {
                        $prevdone = $DB->record_exists('course_modules_completion', [
                            'coursemoduleid' => $prevmod->id,
                            'userid' => $USER->id,
                            'completionstate' => COMPLETION_COMPLETE,
                        ]);

                        if (!$prevdone) {
                            $islocked = true;
                        }
                    }
                }

                $modules[] = [
                    'name' => $mod->name,
                    'url' => $islocked ? '#' : (new moodle_url('/theme/mytheme/pages/lesson.php', [
                        'id' => $course->id,
                        'cmid' => $mod->id
                    ]))->out(false),
                    'icon' => $iconmap[$mod->modname] ?? 'file-earmark',
                    'active' => ($mod->id == $cmid) ? true : null,
                    'completed' => $modcompleted ? true : null,
                    'locked' => $islocked ? true : null,
                ];
            }
        }

        if (!empty($modules)) {
            $sections[] = [
                'name' => get_section_name($course, $section),
                'modules' => $modules
            ];
        }
    }

    // --- Prev / Next ---
    $prevmodule = null;
    $nextmodule = null;
    $islastlesson = false;

    if ($currentindex > 0) {
        $prev = $allmodules[$currentindex - 1];

        $prevmodule = [
            'name' => $prev->name,
            'url' => (new moodle_url('/theme/mytheme/pages/lesson.php', [
                'id' => $course->id,
                'cmid' => $prev->id
            ]))->out(false)
        ];
    }

    if ($currentindex < count($allmodules) - 1) {
        $next = $allmodules[$currentindex + 1];

        $nextmodule = $iscurrentcomplete
            ? [
                'name' => $next->name,
                'url' => (new moodle_url('/theme/mytheme/pages/lesson.php', [
                    'id' => $course->id,
                    'cmid' => $next->id
                ]))->out(false)
            ]
            : [
                'name' => $next->name,
                'url' => null,
                'locked' => true
            ];
    } else {
        $islastlesson = true;

        $nextmodule = $iscurrentcomplete
            ? [
                'url' => (new moodle_url('/theme/mytheme/pages/complete.php', [
                    'id' => $course->id
                ]))->out(false)
            ]
            : [
                'url' => null,
                'locked' => true
            ];
    }

    $category = $DB->get_record('course_categories', ['id' => $course->category]);
    $iscompleted = $completion->is_course_complete($USER->id);

    $certurl = null;

    if ($iscompleted) {
        $certcm = $DB->get_record_sql(
            "SELECT cm.id FROM {course_modules} cm
             JOIN {modules} m ON m.id = cm.module
             WHERE cm.course = ? AND m.name = 'customcert' AND cm.visible = 1 LIMIT 1",
            [$course->id]
        );

        if ($certcm) {
            $certurl = (new moodle_url('/mod/customcert/view.php', [
                'id' => $certcm->id
            ]))->out(false);
        }
    }

    return array_merge($moduledata, [
        'coursename' => format_string($course->fullname),
        'courseurl' => (new moodle_url('/theme/mytheme/pages/course.php', ['id' => $course->id]))->out(false),
        'category' => $category ? format_string($category->name) : 'General',
        'modulename' => format_string($currentcm->name),
        'sections' => $sections,
        'prevmodule' => $prevmodule,
        'nextmodule' => $nextmodule,
        'islastlesson' => $islastlesson ? true : null,
        'iscompleted' => $iscompleted ? true : null,
        'certurl' => $certurl,
        'ismanualcompletion' => $ismanualcompletion ? true : null,
        'ismarkedcomplete' => $ismarkedcomplete ? true : null,
        'toggleurl' => $toggleurl,
        'cmid' => $cmid,
        'wwwroot' => (new moodle_url('/'))->out(false),
    ]);
}
