<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

// Page context & setup
$PAGE->set_url('/theme/mytheme/pages/register.php', ['register' => 1]);
$PAGE->set_context(context_system::instance());

// Only POST requests allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(new moodle_url('/login/index.php', ['register' => 1]));
}

// Session key verification for security
require_sesskey();

// ==========================================
// ०. EMAIL VERIFICATION CONFIGURATION FLAG
// ==========================================
// 🔥 यो Flag लाई true गराए इमेल भेरिफिकेसन लिंक जान्छ, false राखे सिधै दर्ता हुन्छ।
$isEmailSend = false; 

// ==========================================
// 1. INPUT PARAMETERS
// ==========================================
$username              = required_param('username', PARAM_USERNAME);
$email                 = required_param('email', PARAM_EMAIL);
$email2                = required_param('email2', PARAM_EMAIL);
$firstname             = required_param('firstname', PARAM_TEXT);
$lastname              = required_param('lastname', PARAM_TEXT);
$password              = required_param('password', PARAM_RAW);
$password_confirmation = required_param('password_confirmation', PARAM_RAW);

// ==========================================
// 2. VALIDATION LOGIC
// ==========================================
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

if (empty($firstname)) $errors['firstname'] = "First name is required.";
if (empty($lastname))  $errors['lastname'] = "Last name is required.";
if (empty($password))  $errors['password'] = "Password is required.";

// Handle Validation Errors
if (!empty($errors)) {
    $_SESSION['register_errors']    = $errors;
    $_SESSION['register_form_data'] = $_POST;
    redirect(new moodle_url('/login/index.php', ['register' => 1]));
}

// ==========================================
// 3. CREATE USER OBJECT
// ==========================================
$user = new stdClass();
$user->username    = $username;
$user->email       = $email;
$user->firstname   = $firstname;
$user->lastname    = $lastname;
$user->auth        = 'manual'; 
$user->mnethostid  = $CFG->mnet_localhost_id;
$user->password    = hash_internal_user_password($password);

// झण्डा (Flag) अनुसार अकाउन्टको status तय गर्ने
if ($isEmailSend) {
    $user->confirmed = 0; // इमेल नबाँधिएसम्म बन्द रहने
} else {
    $user->confirmed = 1; // सिधै खुल्ला (Active) हुने
}

// Insert into Moodle database
$userid = user_create_user($user);

// Fetch full user object needed for profile saving and email functions
$userobj = $DB->get_record('user', ['id' => $userid]);

// ==========================================
// 4. SAVE CUSTOM PROFILE FIELDS
// ==========================================
$profiledata = new stdClass();
$profiledata->id = $userid;

// Permanent Address
$profiledata->profile_field_middle_name       = optional_param('middle_name', '', PARAM_TEXT);
$profiledata->profile_field_province_id       = optional_param('province_id', 0, PARAM_INT);
$profiledata->profile_field_district_id       = optional_param('district_id', 0, PARAM_INT);
$profiledata->profile_field_municipality_id   = optional_param('municipality_id', 0, PARAM_INT);
$profiledata->profile_field_ward              = optional_param('ward', '', PARAM_TEXT);
$profiledata->profile_field_tole              = optional_param('tole', '', PARAM_TEXT);

// Temporary Address
$profiledata->profile_field_temp_province_id     = optional_param('temp_province_id', 0, PARAM_INT);
$profiledata->profile_field_temp_district_id     = optional_param('temp_district_id', 0, PARAM_INT);
$profiledata->profile_field_temp_municipality_id = optional_param('temp_municipality_id', 0, PARAM_INT);
$profiledata->profile_field_temp_ward            = optional_param('temp_ward', '', PARAM_TEXT);
$profiledata->profile_field_temp_tole            = optional_param('temp_tole', '', PARAM_TEXT);

// Documentation & Others
$profiledata->profile_field_citizenship_no       = optional_param('citizenship_no', '', PARAM_TEXT);
$profiledata->profile_field_citizenship_district = optional_param('citizenship_district', 0, PARAM_INT);
$profiledata->profile_field_nid_no               = optional_param('nid_no', '', PARAM_TEXT);
$profiledata->profile_field_pan_no               = optional_param('pan_no', '', PARAM_TEXT);
$profiledata->profile_field_employed             = optional_param('employed', '', PARAM_TEXT);
$profiledata->profile_field_phone_number         = optional_param('phone_number', '', PARAM_TEXT);
$profiledata->profile_field_mobile_number        = optional_param('mobile_number', '', PARAM_TEXT);
$profiledata->profile_field_organization_name    = optional_param('organization_name', '', PARAM_TEXT);
$profiledata->profile_field_designation          = optional_param('designation', '', PARAM_TEXT);
$profiledata->profile_field_expertise            = optional_param('expertise', '', PARAM_TEXT);
$profiledata->profile_field_years_experience     = optional_param('years_experience', 0, PARAM_INT);
$profiledata->profile_field_gender               = optional_param('gender', '', PARAM_TEXT);
$profiledata->profile_field_age_group            = optional_param('age_group', '', PARAM_TEXT);
$profiledata->profile_field_ethnicity            = optional_param('ethnicity', '', PARAM_TEXT);
$profiledata->profile_field_ethnicity_others     = optional_param('ethnicity_others', '', PARAM_TEXT);
$profiledata->profile_field_qualification        = optional_param('qualification', '', PARAM_TEXT);

profile_save_data($profiledata);

// ==========================================
// 5. HANDLING REDIRECTION & CONDITIONAL EMAIL
// ==========================================
// सेसन खाली गर्ने (दुवै केसमा चाहिन्छ)
unset($_SESSION['register_errors']);
unset($_SESSION['register_form_data']);

$loginurl = new moodle_url('/login/index.php');

if ($isEmailSend) {
    // यदि Flag TRUE छ भने मात्र मेल पठाउने प्रयास गर्ने
    if (send_confirmation_email($userobj)) {
        $notice_message = "Registration successful! A verification email has been sent to " . s($userobj->email) . ". Please check your inbox and confirm your account before logging in.";
        redirect($loginurl, $notice_message, 15);
    } else {
        // यदि SMTP वा कुनै कारणले मेल जान सकेन भने एरर फाल्ने
        print_error('emailnoerr', 'auth_email');
    }
} else {
    // यदि Flag FALSE छ भने मेल नपठाई सिधै सफलताको सन्देश दिने
    $notice_message = "Registration successful! You can now log in using your username and password.";
    redirect($loginurl, $notice_message, 5);
}