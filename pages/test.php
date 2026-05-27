<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

$PAGE->set_url('/theme/mytheme/pages/register.php');
$PAGE->set_context(context_system::instance());

require_sesskey();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(new moodle_url('/login/index.php'));
}

if (!empty($errors)) {

    $_SESSION['register_error'] = implode(', ', $errors);

    redirect(new moodle_url('/theme/mytheme/pages/register.php'));
}

// if (empty($CFG->registerauth)) {
//     throw new moodle_exception('registrationdisabled', 'error');
// }

/* ================= BASIC USER ================= */
$username = required_param('username', PARAM_USERNAME);
$email = required_param('email', PARAM_EMAIL);
$email2 = required_param('email2', PARAM_EMAIL);
$firstname = required_param('firstname', PARAM_TEXT);
$lastname = required_param('lastname', PARAM_TEXT);
$password = required_param('password', PARAM_RAW);

/* ================= VALIDATION ================= */
$errors = [];

if ($email !== $email2) {
    $errors[] = "Email mismatch";
}

if ($DB->record_exists('user', ['username' => $username])) {
    $errors[] = "Username already exists";
}

if ($DB->record_exists('user', ['email' => $email])) {
    $errors[] = "Email already exists";
}

$errmsg = '';
if (!check_password_policy($password, $errmsg)) {
    $errors[] = $errmsg;
}

// if (!empty($errors)) {
//     throw new moodle_exception(implode(', ', $errors));
// }

/* ================= CREATE USER ================= */
// Email block/divert garna ko lagi temporary config overwrite
$CFG->noemailever = true; // Moodle lai kunai pani obit email pathauna bata roki dinchha


/* ================= CREATE USER ================= */
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
/* ================= PROFILE FIELDS ================= */
$profiledata = new stdClass();
$profiledata->id = $userid;

// Middle Name (Moodle default field haina bhane profile_field_ thapne)
$profiledata->profile_field_middle_name = optional_param('middle_name', '', PARAM_TEXT);

/* Permanent Address - HTML ko name sanga match gareko */
$profiledata->profile_field_province_id = optional_param('province_id', 0, PARAM_INT);
$profiledata->profile_field_district_id = optional_param('district_id', 0, PARAM_INT);
$profiledata->profile_field_municipality_id = optional_param('municipality_id', 0, PARAM_INT);
$profiledata->profile_field_ward = optional_param('ward', '', PARAM_TEXT);
$profiledata->profile_field_tole = optional_param('tole', '', PARAM_TEXT);

/* Temporary Address */
$profiledata->profile_field_temp_province_id = optional_param('temp_province_id', 0, PARAM_INT);
$profiledata->profile_field_temp_district_id = optional_param('temp_district_id', 0, PARAM_INT);
$profiledata->profile_field_temp_municipality_id = optional_param('temp_municipality_id', 0, PARAM_INT);
$profiledata->profile_field_temp_ward = optional_param('temp_ward', '', PARAM_TEXT);
$profiledata->profile_field_temp_tole = optional_param('temp_tole', '', PARAM_TEXT);

/* Extra Info */
$profiledata->profile_field_citizenship_no = optional_param('citizenship_no', '', PARAM_TEXT);
$profiledata->profile_field_citizenship_district = optional_param('citizenship_district', 0, PARAM_INT); // Tapaiko Issued District
$profiledata->profile_field_nid_no = optional_param('nid_no', '', PARAM_TEXT);
$profiledata->profile_field_pan_no = optional_param('pan_no', '', PARAM_TEXT);
$profiledata->profile_field_employed = optional_param('employed', '', PARAM_TEXT);

/* Contact */
$profiledata->profile_field_phone_number = optional_param('phone_number', '', PARAM_TEXT);
$profiledata->profile_field_mobile_number = optional_param('mobile_number', '', PARAM_TEXT);

/* Professional/Social */
$profiledata->profile_field_organization_name = optional_param('organization_name', '', PARAM_TEXT);
$profiledata->profile_field_designation = optional_param('designation', '', PARAM_TEXT);
$profiledata->profile_field_expertise = optional_param('expertise', '', PARAM_TEXT);
$profiledata->profile_field_years_experience = optional_param('years_experience', 0, PARAM_INT);
$profiledata->profile_field_gender = optional_param('gender', '', PARAM_TEXT);
$profiledata->profile_field_age_group = optional_param('age_group', '', PARAM_TEXT);
$profiledata->profile_field_ethnicity = optional_param('ethnicity', '', PARAM_TEXT);
$profiledata->profile_field_ethnicity_others = optional_param('ethnicity_others', '', PARAM_TEXT);
$profiledata->profile_field_qualification = optional_param('qualification', '', PARAM_TEXT);

/* SAVE PROFILE */
profile_save_data($profiledata);

/* ================= LOGIN USER ================= */
complete_user_login(get_complete_user_data('id', $userid));

redirect(
    new moodle_url('/theme/mytheme/layout/dashboard.php'),
    'Registration successful',
    2
);