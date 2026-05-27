<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

$PAGE->set_url('/theme/mytheme/pages/register.php', ['register' => 1]);
$PAGE->set_context(context_system::instance());

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(new moodle_url('/login/index.php', ['register' => 1]));
}

require_sesskey();

// ================= INPUT PARAMETERS =================
$username = required_param('username', PARAM_USERNAME);
$email = required_param('email', PARAM_EMAIL);
$email2 = required_param('email2', PARAM_EMAIL);
$firstname = required_param('firstname', PARAM_TEXT);
$lastname = required_param('lastname', PARAM_TEXT);
$password = required_param('password', PARAM_RAW);
$password_confirmation = required_param('password_confirmation', PARAM_RAW);

// ================= VALIDATION =================
$errors = [];

if ($email !== $email2) {
    $errors['email'] = "Email address and confirmation do not match.";
}

if ($password !== $password_confirmation) {
    $errors['password'] = "Password and confirmation do not match.";
}

if ($DB->record_exists('user', ['username' => $username])) {
    $errors['username'] = "This username is already taken.";
}

if ($DB->record_exists('user', ['email' => $email])) {
    $errors['email'] = "This email is already registered.";
}

$errmsg = '';
if (!check_password_policy($password, $errmsg)) {
    $errors['password'] = $errmsg;
}

if (empty($firstname))
    $errors['firstname'] = "First name is required.";
if (empty($lastname))
    $errors['lastname'] = "Last name is required.";
if (empty($password))
    $errors['password'] = "Password is required.";

// ================= ERROR HANDLING =================
if (!empty($errors)) {
    $_SESSION['register_errors'] = $errors;
    $_SESSION['register_form_data'] = $_POST;

    redirect(new moodle_url('/login/index.php', ['register' => 1]));
}

// ================= CREATE USER =================
$CFG->noemailever = true;

$user = new stdClass();
$user->username = $username;
$user->email = $email;
$user->firstname = $firstname;
$user->lastname = $lastname;
$user->auth = 'manual';
$user->confirmed = 1;
$user->mnethostid = $CFG->mnet_localhost_id;
$user->password = hash_internal_user_password($password);

$userid = user_create_user($user);

// ================= PROFILE FIELDS =================
$profiledata = new stdClass();
$profiledata->id = $userid;

$profiledata->profile_field_middle_name = optional_param('middle_name', '', PARAM_TEXT);
$profiledata->profile_field_province_id = optional_param('province_id', 0, PARAM_INT);
$profiledata->profile_field_district_id = optional_param('district_id', 0, PARAM_INT);
$profiledata->profile_field_municipality_id = optional_param('municipality_id', 0, PARAM_INT);
$profiledata->profile_field_ward = optional_param('ward', '', PARAM_TEXT);
$profiledata->profile_field_tole = optional_param('tole', '', PARAM_TEXT);

$profiledata->profile_field_temp_province_id = optional_param('temp_province_id', 0, PARAM_INT);
$profiledata->profile_field_temp_district_id = optional_param('temp_district_id', 0, PARAM_INT);
$profiledata->profile_field_temp_municipality_id = optional_param('temp_municipality_id', 0, PARAM_INT);
$profiledata->profile_field_temp_ward = optional_param('temp_ward', '', PARAM_TEXT);
$profiledata->profile_field_temp_tole = optional_param('temp_tole', '', PARAM_TEXT);

$profiledata->profile_field_citizenship_no = optional_param('citizenship_no', '', PARAM_TEXT);
$profiledata->profile_field_citizenship_district = optional_param('citizenship_district', 0, PARAM_INT);
$profiledata->profile_field_nid_no = optional_param('nid_no', '', PARAM_TEXT);
$profiledata->profile_field_pan_no = optional_param('pan_no', '', PARAM_TEXT);
$profiledata->profile_field_employed = optional_param('employed', '', PARAM_TEXT);
$profiledata->profile_field_phone_number = optional_param('phone_number', '', PARAM_TEXT);
$profiledata->profile_field_mobile_number = optional_param('mobile_number', '', PARAM_TEXT);
$profiledata->profile_field_organization_name = optional_param('organization_name', '', PARAM_TEXT);
$profiledata->profile_field_designation = optional_param('designation', '', PARAM_TEXT);
$profiledata->profile_field_expertise = optional_param('expertise', '', PARAM_TEXT);
$profiledata->profile_field_years_experience = optional_param('years_experience', 0, PARAM_INT);
$profiledata->profile_field_gender = optional_param('gender', '', PARAM_TEXT);
$profiledata->profile_field_age_group = optional_param('age_group', '', PARAM_TEXT);
$profiledata->profile_field_ethnicity = optional_param('ethnicity', '', PARAM_TEXT);
$profiledata->profile_field_ethnicity_others = optional_param('ethnicity_others', '', PARAM_TEXT);
$profiledata->profile_field_qualification = optional_param('qualification', '', PARAM_TEXT);

profile_save_data($profiledata);

// ================= SUCCESS =================
complete_user_login(get_complete_user_data('id', $userid));

unset($_SESSION['register_errors']);
unset($_SESSION['register_form_data']);

redirect(new moodle_url('/theme/mytheme/layout/dashboard.php'), 'Registration successful!', 2);