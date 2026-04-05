<?php
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');

/**
 * Helper: Get image URL from plugin file area
 */
function theme_mytheme_get_file_url(string $filearea, int $itemid = 0, string $component = 'theme_mytheme'): string {
    $context = context_system::instance();
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, $component, $filearea, $itemid, 'id DESC', false);

    if ($files) {
        $file = reset($files);
        return moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);
    }

    return '';
}

/**
 * Get course list context with optional category filter
 */
function theme_mytheme_get_course_list_context(int $categoryid = 0): array {
    global $DB;

    $where = ['visible' => 1];
    if ($categoryid) $where['category'] = $categoryid;

    $courselist = $DB->get_records('course', $where, 'sortorder ASC');
    $courses = [];

    foreach ($courselist as $course) {
        if ($course->id == SITEID) continue;
        $course_obj = new core_course_list_element($course);
        $courseimage = '';
        foreach ($course_obj->get_course_overviewfiles() as $file) {
            $courseimage = moodle_url::make_pluginfile_url(
                $file->get_contextid(), $file->get_component(),
                $file->get_filearea(), $file->get_itemid(),
                $file->get_filepath(), $file->get_filename()
            )->out(false);
            break;
        }
        $enrol = $DB->get_record('enrol', ['courseid' => $course->id], '*', IGNORE_MULTIPLE);
        $enrolledcount = $enrol ? $DB->count_records('user_enrolments', ['enrolid' => $enrol->id]) : 0;
        $category = $DB->get_record('course_categories', ['id' => $course->category]);

        // Get teacher
        $context = context_course::instance($course->id);
        $teachers = get_role_users(3, $context, false, 'u.id, u.firstname, u.lastname, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename', null, false, '', 0, 1);
        $teacher = reset($teachers);

        $courses[] = [
            'id'           => $course->id,
            'fullname'     => format_string($course->fullname),
            'image'        => $courseimage ?: 'https://images.pexels.com/photos/267885/pexels-photo-267885.jpeg',
            'url'          => (new moodle_url('/theme/mytheme/pages/course.php', ['id' => $course->id]))->out(false),
            'enrolledcount'=> $enrolledcount,
            'category'     => $category ? format_string($category->name) : 'General',
            'teachername'  => $teacher ? fullname($teacher) : null,
        ];
    }

    // Categories for filter dropdown
    $catlist = $DB->get_records('course_categories', ['visible' => 1], 'sortorder ASC', 'id,name');
    $categories = [];
    foreach ($catlist as $cat) {
        $categories[] = [
            'id'       => $cat->id,
            'name'     => format_string($cat->name),
            'selected' => ($cat->id == $categoryid),
        ];
    }

    return [
        'courses'      => $courses,
        'totalcourses' => count($courses),
        'categories'   => $categories,
        'wwwroot'      => (new moodle_url('/'))->out(false),
    ];
}

/**
 * Get frontpage courses
 */
function theme_mytheme_get_courses(int $limit = 6): array {
    global $DB, $CFG;

    $courses = [];
    $courselist = $DB->get_records('course', ['visible' => 1], 'sortorder ASC', '*', 0, $limit);

    foreach ($courselist as $course) {
        if ($course->id == SITEID) continue;

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

        $enrol = $DB->get_record('enrol', ['courseid' => $course->id], '*', IGNORE_MULTIPLE);
        $enrolledcount = $enrol ? $DB->count_records('user_enrolments', ['enrolid' => $enrol->id]) : 0;

        $category = $DB->get_record('course_categories', ['id' => $course->category]);
        $courses[] = [
            'id' => $course->id,
            'fullname' => format_string($course->fullname),
            'shortname' => format_string($course->shortname),
            'summary' => strip_tags(format_text($course->summary, FORMAT_HTML)),
            'image' => $courseimage ?: 'https://images.pexels.com/photos/267885/pexels-photo-267885.jpeg',
            'url' => (new moodle_url('/theme/mytheme/pages/course.php', ['id' => $course->id]))->out(false),
            'enrolledcount' => $enrolledcount,
            'category' => $category ? format_string($category->name) : 'General',
        ];
    }

    return $courses;
}

/**
 * Get sliders data
 */
function theme_mytheme_get_sliders_data(): array {
    $sliders = [];
    for ($i = 1; $i <= 3; $i++) {
        $title = get_config('theme_mytheme', "slide{$i}_title");
        $desc = get_config('theme_mytheme', "slide{$i}_desc");
        if (empty($title) && empty($desc)) continue;

        $sliders[] = [
            'title' => $title ?: 'Welcome to Our Platform',
            'desc' => format_text($desc ?: "<p>Start your learning journey today</p>", FORMAT_HTML),
            'btntext' => get_config('theme_mytheme', "slide{$i}_btntext"),
            'btnlink' => get_config('theme_mytheme', "slide{$i}_btnlink") ?: '#',
            'imageurl' => theme_mytheme_get_file_url("slide{$i}_image"),
            'index' => $i - 1,
            'index_display' => $i,
            'is_first' => ($i === 1),
        ];
    }
    return $sliders;
}

/**
 * Get about section data
 */
function theme_mytheme_get_about_data(): array {
    $stats = [];
    for ($i = 1; $i <= 3; $i++) {
        $number = get_config('theme_mytheme', "stat{$i}_number");
        $desc = get_config('theme_mytheme', "stat{$i}_desc");
        if ($number || $desc) {
            $stats[] = [
                'number' => $number ?: '0',
                'description' => $desc ?: 'Statistic',
            ];
        }
    }

    return [
        'heading' => get_config('theme_mytheme', 'aboutheading') ?: 'About Us',
        'desc' => get_config('theme_mytheme', 'aboutdesc') ?: 'Welcome to our platform',
        'imageurl' => theme_mytheme_get_file_url('aboutimage'),
        'stats' => $stats,
    ];
}

/**
 * Get frontpage template context
 */
function theme_mytheme_get_frontpage_context(): array {
    global $SITE, $OUTPUT;

    $socials = theme_mytheme_get_social_links();
    
    return [
        'sitename' => format_string($SITE->shortname),
        'output' => $OUTPUT,
        'year' => date('Y'),
        'courses' => theme_mytheme_get_courses(20),
        'wwwroot' => (new moodle_url('/'))->out(false),
        'facebook' => $socials['facebook'],
        'twitter' => $socials['twitter'],
        'instagram' => $socials['instagram'],
        'linkedin' => $socials['linkedin'],
        'sliders' => theme_mytheme_get_sliders_data(),
        'about' => theme_mytheme_get_about_data(),
        'jumbotron' => theme_mytheme_get_jumbotron_context(),
        'setting' => theme_mytheme_get_general_context(),
    ];
}

// footer template context
function theme_mytheme_get_footer_context(): array {
    global $SITE, $OUTPUT;

    $theme = theme_config::load('mytheme');
    $sociallinks = theme_mytheme_get_social_links();

    $socials = [];
    $num = $theme->settings->numofsocialmedia ?? 4;
    for ($i = 1; $i <= $num; $i++) {
        $socials[] = [
            'icon' => $theme->settings->{"socialmedia{$i}_icon"} ?? 'fa-facebook',
            'url' => $theme->settings->{"socialmedia{$i}_url"} ?? '#',
            'color' => $theme->settings->{"socialmedia{$i}_color"} ?? '#fff',
        ];
    }

    return [
        'sitename' => format_string($SITE->shortname),
        'year' => date('Y'),
        'wwwroot' => (new moodle_url('/'))->out(false),
        'facebook' => $sociallinks['facebook'],
        'twitter' => $sociallinks['twitter'],
        'instagram' => $sociallinks['instagram'],
        'linkedin' => $sociallinks['linkedin'],
        'footerlogo' => theme_mytheme_get_file_url('footerlogo'),
        'footerbgimg' => theme_mytheme_get_file_url('footerbgimg'),
        'footercopyright' => $theme->settings->footercopyright ?? '',
        'socials' => $socials
    ];
}

// jumbotoron content
function theme_mytheme_get_jumbotron_context(): array {
    $theme = theme_config::load('mytheme');

    return [
        'heading' => $theme->settings->jumbotronheading ?? 'Welcome to Our Learning Platform',
        'desc'    => $theme->settings->jumbotrondesc ?? 'Start your learning journey today',
        'btntext' => $theme->settings->jumbotronbtntext ?? 'Get Started',
        'btnlink' => $theme->settings->jumbotronbtnlink ?? '#',
    ];
}

/**
 * Get base context (setting + jumbotron + footer) - use everywhere
 */
function theme_mytheme_get_base_context(): array {
    return [
        'setting'  => theme_mytheme_get_general_context(),
        'jumbotron'=> theme_mytheme_get_jumbotron_context(),
        'footer'   => theme_mytheme_get_footer_context(),
    ];
}

// general setting
function theme_mytheme_get_general_context(): array {
    global $USER, $DB;
    $theme = theme_config::load('mytheme');
    $socials = theme_mytheme_get_social_links();

    $isloggedin = isloggedin() && !isguestuser();
    $isadmin = is_siteadmin($USER);

    // Get complete user object to avoid missing fields warning
    $fulluser = $isloggedin ? get_complete_user_data('id', $USER->id) : null;

    $mycourses = [];
    if ($isloggedin) {
        $courses = enrol_get_my_courses(null, 'fullname ASC', 5);
        foreach ($courses as $course) {
            $mycourses[] = [
                'id'   => $course->id,
                'name' => format_string($course->fullname),
                'url'  => (new moodle_url('/theme/mytheme/pages/course.php', ['id' => $course->id]))->out(false),
            ];
        }
    }

    $categories = [];
    $catlist = $DB->get_records('course_categories', ['visible' => 1, 'parent' => 0], 'sortorder ASC', 'id,name', 0, 8);
    foreach ($catlist as $cat) {
        $categories[] = [
            'id'   => $cat->id,
            'name' => format_string($cat->name),
            'url'  => (new moodle_url('/course/index.php', ['categoryid' => $cat->id]))->out(false),
        ];
    }

    // Custom menu from theme menu settings
    $custommenu = [];
    $menucount  = (int)(get_config('theme_mytheme', 'menucount') ?: 5);
    for ($i = 1; $i <= $menucount; $i++) {
        $label  = get_config('theme_mytheme', "menulabel{$i}");
        $url    = get_config('theme_mytheme', "menuurl{$i}");
        $newtab = get_config('theme_mytheme', "menunewtab{$i}");
        if (empty($label)) continue;
        $custommenu[] = [
            'label'  => $label,
            'url'    => $url ?: '#',
            'newtab' => !empty($newtab),
        ];
    }

    return [
        'logo'           => theme_mytheme_get_file_url('logo'),
        'favicon'        => theme_mytheme_get_file_url('favicon'),
        'facebook'       => $socials['facebook'],
        'twitter'        => $socials['twitter'],
        'instagram'      => $socials['instagram'],
        'linkedin'       => $socials['linkedin'],
        'primarycolor'   => $theme->settings->primarycolor ?? '#0f6cbf',
        'secondarycolor' => $theme->settings->secondarycolor ?? '#6c757d',
        'preloader'      => !empty($theme->settings->preloader),
        'isloggedin'     => $isloggedin ? true : null,
        'isadmin'        => $isadmin ? true : null,
        'userfullname'   => $isloggedin ? fullname($fulluser) : '',
        'useravatar'     => $isloggedin ? (new moodle_url('/user/pix.php/' . $USER->id . '/f1.jpg'))->out(false) : '',
        'profileurl'     => $isloggedin ? (new moodle_url('/user/profile.php', ['id' => $USER->id]))->out(false) : '',
'dashboardurl'   => (new moodle_url('/theme/mytheme/pages/dashboard.php'))->out(false),
        'adminurl'       => (new moodle_url('/admin/index.php'))->out(false),
        'logouturl'      => $isloggedin ? (new moodle_url('/login/logout.php', ['sesskey' => sesskey()]))->out(false) : '',
'loginurl'       => (new moodle_url('/theme/mytheme/pages/login_redirect.php'))->out(false),
        'wwwroot'        => (new moodle_url('/'))->out(false),
        'mycourses'      => $mycourses ?: null,
        'categories'     => $categories ?: null,
        'custommenu'     => $custommenu ?: null,
    ];
}

// Social Links Decode
function theme_mytheme_get_social_links(): array {
    $theme = theme_config::load('mytheme');

    $socials = ['facebook','twitter','instagram','linkedin'];
    $result = [];

    foreach ($socials as $s) {
        $url = $theme->settings->{$s} ?? '#';
        // Ensure URL has protocol
        if ($url !== '#' && !preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }
        $result[$s] = $url;
    }

    return $result;
}

/**
 * Serve theme plugin files
 */
function theme_mytheme_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []): bool {
    if ($context->contextlevel === CONTEXT_SYSTEM) {
        $theme = theme_config::load('mytheme');
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    }
    return false;
}
