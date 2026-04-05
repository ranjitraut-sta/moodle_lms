<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/lib/authlib.php');

$PAGE->set_url('/theme/mytheme/pages/register.php');
$PAGE->set_context(context_system::instance());

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(new moodle_url('/login/index.php'));
}

// Check self-registration enabled
if (empty($CFG->registerauth)) {
    redirect(new moodle_url('/login/index.php'), 'Registration is disabled.', null, \core\output\notification::NOTIFY_ERROR);
}

$username  = trim(optional_param('username', '', PARAM_USERNAME));
$email     = trim(optional_param('email', '', PARAM_EMAIL));
$email2    = trim(optional_param('email2', '', PARAM_EMAIL));
$firstname = trim(optional_param('firstname', '', PARAM_TEXT));
$lastname  = trim(optional_param('lastname', '', PARAM_TEXT));
$password  = optional_param('password', '', PARAM_RAW);

$errors = [];

// Validate
if (empty($username))  $errors[] = 'Username is required.';
if (empty($email))     $errors[] = 'Email is required.';
if ($email !== $email2) $errors[] = 'Emails do not match.';
if (empty($firstname)) $errors[] = 'First name is required.';
if (empty($lastname))  $errors[] = 'Last name is required.';
if (empty($password))  $errors[] = 'Password is required.';

// Check username exists
if (!empty($username) && $DB->record_exists('user', ['username' => $username])) {
    $errors[] = 'Username already taken.';
}

// Check email exists
if (!empty($email) && $DB->record_exists('user', ['email' => $email])) {
    $errors[] = 'Email already registered.';
}

// Check password policy
if (!empty($password)) {
    $errmsg = '';
    if (!check_password_policy($password, $errmsg)) {
        $errors[] = $errmsg;
    }
}

if (!empty($errors)) {
    $errorstr = implode('<br>', $errors);
    redirect(
        new moodle_url('/login/index.php', ['#' => 'register']),
        $errorstr,
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

// Create user
$user = new stdClass();
$user->username   = $username;
$user->email      = $email;
$user->firstname  = $firstname;
$user->lastname   = $lastname;
$user->password   = hash_internal_user_password($password);
$user->auth       = 'email';
$user->confirmed  = 1; // auto confirm — no email verification
$user->mnethostid = $CFG->mnet_localhost_id;
$user->timecreated = time();
$user->timemodified = time();
$user->lang       = $CFG->lang;

$user->id = user_create_user($user, false, false);

// Fetch full user object from DB before login
$user = get_complete_user_data('id', $user->id);

// Auto login
complete_user_login($user);

redirect(new moodle_url('/my/'), 'Welcome, ' . fullname($user) . '!', 3, \core\output\notification::NOTIFY_SUCCESS);
