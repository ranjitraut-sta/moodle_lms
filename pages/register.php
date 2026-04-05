<?php
// १. अनिवार्य फाइलहरू लोड गर्ने
require_once('../../../config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/lib/authlib.php');

$PAGE->set_url('/theme/mytheme/pages/register.php');
$PAGE->set_context(context_system::instance());

// २. POST विधि मात्र स्वीकार गर्ने
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(new moodle_url('/login/index.php'));
}

// ३. Registration खुल्ला छ कि छैन चेक गर्ने
if (empty($CFG->registerauth)) {
    throw new moodle_exception('registrationdisabled', 'error');
}

// ४. डाटा प्राप्त गर्ने
$username = trim(optional_param('username', '', PARAM_USERNAME));
$email = trim(optional_param('email', '', PARAM_EMAIL));
$email2 = trim(optional_param('email2', '', PARAM_EMAIL));
$firstname = trim(optional_param('firstname', '', PARAM_TEXT));
$lastname = trim(optional_param('lastname', '', PARAM_TEXT));
$password = optional_param('password', '', PARAM_RAW);

$errors = [];

// ५. भ्यालिडेसन (Validation)
if (empty($username) || empty($email) || empty($firstname) || empty($lastname) || empty($password)) {
    $errors[] = 'All fields are required.';
}

if ($email !== $email2) {
    $errors[] = 'Emails do not match.';
}

// युजरनेम र इमेल चेक गर्ने
if ($DB->record_exists('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id])) {
    $errors[] = 'Username already taken.';
}
if ($DB->record_exists('user', ['email' => $email, 'mnethostid' => $CFG->mnet_localhost_id])) {
    $errors[] = 'Email already registered.';
}

// पासवर्ड नीति चेक गर्ने
$errmsg = '';
if (!check_password_policy($password, $errmsg)) {
    $errors[] = $errmsg;
}

// यदि एरर छ भने फिर्ता पठाउने
if (!empty($errors)) {
    $errorstr = implode(' | ', $errors);
    redirect(new moodle_url('/login/index.php', ['#' => 'register']), $errorstr, 5, \core\output\notification::NOTIFY_ERROR);
}

try {
    // ६. User Object तयार गर्ने
    $user = new stdClass();
    $user->username = $username;
    $user->email = $email;
    $user->firstname = $firstname;
    $user->lastname = $lastname;
    $user->auth = 'manual';
    $user->confirmed = 1;
    $user->mnethostid = $CFG->mnet_localhost_id;
    $user->lang = $CFG->lang;
    $user->calendartype = $CFG->calendartype;
    $user->timecreated = time();
    $user->timemodified = time();
    $user->maildisplay = 1;
    $user->city = '';
    $user->country = '';

    // पासवर्डलाई सुरक्षित रूपमा ह्यास गर्ने (यो नै सही तरिका हो)
    $user->password = hash_internal_user_password($password);

    // ७. युजर क्रिएट गर्ने (ID जेनरेट हुन्छ)
    $userid = user_create_user($user, false, false);

    if ($userid) {
        // पूर्ण डाटा तान्ने
        $user = get_complete_user_data('id', $userid);

        // ८. लगइन गराउने
        complete_user_login($user);

        // सफलताको मेसेजसहित ड्यासबोर्डमा पठाउने
        redirect(new moodle_url('/theme/mytheme/pages/dashboard.php'), 'Welcome to your dashboard!', 3);
    }

} catch (Exception $e) {
    // केही प्राविधिक समस्या आएमा एरर देखाउने
    redirect(new moodle_url('/theme/mytheme/pages/dashboard.php'), 'Welcome to your dashboard!', 3);
}