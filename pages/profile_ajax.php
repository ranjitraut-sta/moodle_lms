<?php
header('Content-Type: application/json; charset=utf-8');
require_once('../../../config.php');

try {
    require_login();
    require_once($CFG->dirroot . '/user/lib.php');

    $action = optional_param('action', '', PARAM_TEXT);

    if (empty($action)) {
        throw new Exception('Missing action parameter.');
    }

    // Sesskey Validation
    $sesskey = optional_param('sesskey', '', PARAM_ALPHANUM);
    if (empty($sesskey) || !confirm_sesskey($sesskey)) {
        error_log('Session key validation failed for user id: ' . ($USER->id ?? 'unknown'));
        throw new Exception('Session expired. Please refresh the page and try again.');
    }

    global $USER, $DB;

    // ====================== UPDATE PROFILE ONLY ======================
    if ($action === 'update_profile') {

        $firstname = optional_param('firstname', $USER->firstname ?? '', PARAM_TEXT);
        $lastname = optional_param('lastname', $USER->lastname ?? '', PARAM_TEXT);
        $email = optional_param('email', $USER->email ?? '', PARAM_EMAIL);

        if (empty($firstname)) {
            throw new Exception('First name is required.');
        }
        if (!empty($email) && !validate_email($email)) {
            throw new Exception('Invalid email address provided.');
        }

        // Duplicate email check
        if (!empty($email) && $email !== $USER->email) {
            $emailexists = $DB->count_records_select('user', 'email = ? AND id != ?', [$email, $USER->id]);
            if ($emailexists > 0) {
                throw new Exception('Email address is already in use by another account.');
            }
            $USER->email = $email;
        }

        $USER->firstname = $firstname;
        $USER->lastname = $lastname;

        user_update_user($USER, true);

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
        exit;
    }

    // ====================== UPDATE PROFILE + PASSWORD ======================
    else if ($action === 'update_profile_and_credentials') {

        $firstname = optional_param('firstname', $USER->firstname ?? '', PARAM_TEXT);
        $lastname = optional_param('lastname', $USER->lastname ?? '', PARAM_TEXT);
        $email = optional_param('email', $USER->email ?? '', PARAM_EMAIL);
        $current_password = optional_param('current_password', '', PARAM_RAW);
        $new_password = optional_param('new_password', '', PARAM_RAW);

        if (empty($firstname)) {
            throw new Exception('First name is required.');
        }
        if (!empty($email) && !validate_email($email)) {
            throw new Exception('Invalid email address provided.');
        }

        // Duplicate email check
        if (!empty($email) && $email !== $USER->email) {
            $emailexists = $DB->count_records_select('user', 'email = ? AND id != ?', [$email, $USER->id]);
            if ($emailexists > 0) {
                throw new Exception('Email address is already in use by another account.');
            }
            $USER->email = $email;
        }

        $USER->firstname = $firstname;
        $USER->lastname = $lastname;

        $passwordChanged = false;

        // Password Change Logic
        if (!empty($new_password)) {

            if ($USER->auth !== 'manual') {
                throw new Exception("Password change is only allowed for manual authentication accounts.");
            }

            if (empty($current_password)) {
                throw new Exception('Current password is required to change your password.');
            }

            // Verify current password
            $auth_plugin = get_auth_plugin($USER->auth);
            if (!$auth_plugin->user_login($USER->username, $current_password)) {
                throw new Exception('Current password is incorrect.');
            }

            // Password Policy
            if (strlen($new_password) < 8) {
                throw new Exception('Password must be at least 8 characters long.');
            }
            if (!preg_match('/[0-9]/', $new_password)) {
                throw new Exception('Password must contain at least one number.');
            }
            if (!preg_match('/[A-Z]/', $new_password)) {
                throw new Exception('Password must contain at least one uppercase letter.');
            }
            if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
                throw new Exception('Password must contain at least one special character.');
            }

            // Fresh user record for password update
            $user = $DB->get_record('user', ['id' => $USER->id], '*', MUST_EXIST);

            update_internal_user_password($user, $new_password);

            // Update timestamp
            // $user->timemodified = time();
            // $DB->update_record('user', $user);

            // Kill all sessions (force re-login)
            // \core\session\manager::kill_user_sessions($user->id);

            $passwordChanged = true;
        }

        // Update Profile
        user_update_user($USER, true);

        $message = $passwordChanged
            ? 'Profile and password updated successfully. Please login again with your new password.'
            : 'Profile updated successfully!';

        echo json_encode(['success' => true, 'message' => $message]);
        exit;
    }

    // ====================== UPDATE PASSWORD ONLY ======================
    else if ($action === 'update_credentials') {

        $current_password = required_param('current_password', PARAM_RAW);
        $new_password = required_param('new_password', PARAM_RAW);

        if ($USER->auth !== 'manual') {
            throw new Exception("Password change is only allowed for manual authentication accounts.");
        }

        // Verify current password
        if (!validate_internal_user_password($USER, $current_password)) {
            throw new Exception('Current password is incorrect.');
        }

        // Password Policy
        if (strlen($new_password) < 8) {
            throw new Exception('Password must be at least 8 characters long.');
        }
        if (!preg_match('/[0-9]/', $new_password)) {
            throw new Exception('Password must contain at least one number.');
        }
        if (!preg_match('/[A-Z]/', $new_password)) {
            throw new Exception('Password must contain at least one uppercase letter.');
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
            throw new Exception('Password must contain at least one special character.');
        }

        $user = $DB->get_record('user', ['id' => $USER->id], '*', MUST_EXIST);

        update_internal_user_password($user, $new_password);

        // $user->timemodified = time();
        // $DB->update_record('user', $user);

        // Kill all sessions
        // \core\session\manager::kill_user_sessions($user->id);

        echo json_encode([
            'success' => true,
            'message' => 'Password updated successfully. Please login again.'
        ]);
        exit;
    }

    // Invalid Action
    throw new Exception('Invalid action specified.');

} catch (Exception $e) {
    $error_msg = $e->getMessage();
    $action = optional_param('action', 'unknown', PARAM_TEXT);

    error_log('Profile AJAX Error [' . $action . ']: ' . $error_msg);
    error_log('POST data: ' . json_encode($_POST, JSON_UNESCAPED_SLASHES));

    http_response_code(200); // Always return 200 for better JS handling

    echo json_encode(['success' => false, 'message' => $error_msg]);
    exit;

} catch (Throwable $e) {
    error_log('Profile AJAX Fatal Error: ' . $e->getMessage());
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
    exit;
}