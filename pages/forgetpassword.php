<?php
require_once('../../../config.php');
require_once($CFG->dirroot . '/user/lib.php');
// थिमका कस्टुम फङ्गसनहरू लोड गर्न यो आवश्यक छ
require_once($CFG->dirroot . '/theme/mytheme/lib.php');

$PAGE->set_url('/theme/mytheme/pages/forgetpassword.php');
$PAGE->set_context(context_system::instance());

$action = optional_param('action', 'request', PARAM_ALPHA); // request, verify, update
$token = optional_param('token', '', PARAM_ALPHANUM);
$userid = optional_param('u', 0, PARAM_INT);

$errors = [];
$success_message = '';


// ==========================================
// १. इमेल अनुरोध गर्ने फारम प्रोसेसिङ (DEBUGGING VERSION)
// ==========================================
if ($action === 'sendemail' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div class='alert alert-info'><strong>Debug 1:</strong> Submitted Email: " . s('POST') . "</div>";

    if (!confirm_sesskey()) {
        print_error('invalidsesskey');
    }

    $email = required_param('email', PARAM_EMAIL);


    // डेटाबेसमा एक्टिभ युजर छ कि छैन चेक गर्ने
    $user = $DB->get_record('user', ['email' => $email, 'deleted' => 0, 'suspended' => 0]);

    if ($user) {
        // नयाँ सेक्रेट टोकन बनाउने र डेटाबेसमा अपडेट गर्ने
        $secret = random_string(15);
        $DB->set_field('user', 'secret', $secret, ['id' => $user->id]);

        $userobj = $DB->get_record('user', ['id' => $user->id]);

        // रिसेट लिङ्क तयार गर्ने
        $reseturl = new moodle_url('/theme/mytheme/pages/forgetpassword.php', [
            'action' => 'verify',
            'token' => $userobj->secret,
            'u' => $userobj->id
        ]);

        $site = get_site();
        $from = core_user::get_support_user();
        $subject = format_string($site->fullname) . ": Password Reset Request";

        $message = "Hi " . fullname($userobj) . ",\n\n";
        $message .= "Someone requested a password reset for your account.\n";
        $message .= "If this was you, please click the link below to set a new password:\n\n";
        $message .= $reseturl->out(false) . "\n\n";
        $message .= "If you didn't request this, just ignore this email.\n";


        // इमेल पठाउने प्रयास गर्ने
        $mailresult = email_to_user($userobj, $from, $subject, $message);

        if ($mailresult) {
            // 🛠️ DEBUG 4: इमेल सफलतापूर्वक गयो
            echo "<div class='alert alert-success'><strong>Debug 4:</strong> email_to_user() returned TRUE. Redirecting...</div>";

            $sentpageurl = new moodle_url('/theme/mytheme/pages/mail_sent.php', [
                'email' => $userobj->email,
                'type' => 'reset'
            ]);

            // तपाईंलाई तत्काल स्क्रीनमा के प्रिन्ट भयो हेर्न सजिलो होस् भनेर २ सेकेन्ड ढिलो रिडाइरेक्ट गरिएको
            sleep(2);
            redirect($sentpageurl);
        } else {
            $errors['email'] = "Could not send email. Please check your Moodle Outgoing Mail (SMTP) configuration.";
            $action = 'request';
        }
    } else {

        $errors['email'] = "This email address is not registered in our system.";
        $action = 'request';
    }
}

// ==========================================
// २. इमेलको लिङ्क क्लिक गरेर आएपछि टोकन चेक गर्ने
// ==========================================
if ($action === 'verify') {
    if (empty($token) || empty($userid)) {
        print_error('invalidurl', 'auth');
    }

    $user = $DB->get_record('user', ['id' => $userid, 'secret' => $token, 'deleted' => 0]);
    if (!$user) {
        $errors['global'] = "This reset link is invalid or has already been used.";
        $action = 'request';
    } else {
        $action = 'reset_form'; // टोकन म्याच भएमा मात्र नयाँ पासवर्ड हाल्ने फारम खुल्छ
    }
}

// ==========================================
// ३. नयाँ पासवर्ड सेभ गर्ने प्रोसेसिङ
// ==========================================
if ($action === 'updatepassword' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $userid = required_param('userid', PARAM_INT);
    $token = required_param('token', PARAM_ALPHANUM);
    $password = required_param('password', PARAM_RAW);
    $password_confirmation = required_param('password_confirmation', PARAM_RAW);

    $user = $DB->get_record('user', ['id' => $userid, 'secret' => $token, 'deleted' => 0]);

    if (!$user) {
        $errors['global'] = "Invalid session or token. Please try again.";
        $action = 'request';
    } else {
        if ($password !== $password_confirmation) {
            $errors['password'] = "Password and confirmation do not match.";
            $action = 'reset_form';
        } else {
            $errmsg = '';
            if (!check_password_policy($password, $errmsg)) {
                $errors['password'] = $errmsg;
                $action = 'reset_form';
            } else {
                // पासवर्ड अपडेट गर्ने र सुरक्षाका लागि टोकन (secret) खाली गर्ने
                $updateuser = new stdClass();
                $updateuser->id = $user->id;
                $updateuser->password = hash_internal_user_password($password);
                $updateuser->secret = '';

                user_update_user($updateuser, false);

                // सफलतापूर्वक पासवर्ड परिवर्तन भएपछि मुख्य लगइन पेजमा पठाउने
                $loginurl = new moodle_url('/login/index.php');
                redirect($loginurl, "Password updated successfully! Please login with your new password.", 5);
            }
        }
    }
}

// डेटा लेआउटमा पठाउनको लागि variable मा सेट गर्ने
include($CFG->dirroot . '/theme/mytheme/layout/forgetpassword.php');