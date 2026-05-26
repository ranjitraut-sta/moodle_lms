<?php
header('Content-Type: application/json; charset=utf-8');
require_once('../../../config.php');

try {
    require_login();
    // user library provides helper for updating internal user passwords
    require_once($CFG->dirroot . '/user/lib.php');
    
    $action = optional_param('action', '', PARAM_TEXT);

    // Validate required parameters exist
    if (empty($action)) {
        throw new Exception('Missing action parameter.');
    }

    // Validate session using explicit sesskey submitted by the form (safer
    // for AJAX): use PARAM_ALPHANUM because sesskeys are alphanumeric.
    $sesskey = optional_param('sesskey', '', PARAM_ALPHANUM);
    if (empty($sesskey) || !confirm_sesskey($sesskey)) {
        error_log('Session key validation failed for user id: ' . ($USER->id ?? 'unknown') . ' sesskey:' . substr($sesskey, 0, 10));
        throw new Exception('Session expired. Please refresh the page and try again.');
    }
    
    global $USER, $DB;

    
    if ($action === 'update_profile') {
        // Only allow updating first name, last name and email
        $firstname = optional_param('firstname', $USER->firstname ?? '', PARAM_TEXT);
        $lastname = optional_param('lastname', $USER->lastname ?? '', PARAM_TEXT);
        $email = optional_param('email', $USER->email ?? '', PARAM_EMAIL);

        // Basic validation
        if (empty($firstname)) {
            throw new Exception('First name is required.');
        }
        if (!empty($email) && !validate_email($email)) {
            throw new Exception('Invalid email address provided.');
        }

        // Check for duplicate email
        if (!empty($email) && $email !== $USER->email) {
            $emailexists = $DB->count_records_select('user', 'email = ? AND id != ?', [$email, $USER->id]);
            if ($emailexists > 0) {
                throw new Exception('Email address is already in use by another account.');
            }
            $USER->email = $email;
        }

        // Update name fields
        $USER->firstname = $firstname;
        $USER->lastname = $lastname;

        user_update_user($USER, true);

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
        exit;
    }
    
    else if ($action === 'update_profile_and_credentials') {
        // Update profile fields and optionally update password in one request
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

        // Check for duplicate email
        if (!empty($email) && $email !== $USER->email) {
            $emailexists = $DB->count_records_select('user', 'email = ? AND id != ?', [$email, $USER->id]);
            if ($emailexists > 0) {
                throw new Exception('Email address is already in use by another account.');
            }
            $USER->email = $email;
        }

        // Update name fields
        $USER->firstname = $firstname;
        $USER->lastname = $lastname;

        // If password change requested, validate current and new password
        if (!empty($new_password)) {
            // Only allow changing password here for 'manual' (internal) auth accounts.
            if (!empty($USER->auth) && $USER->auth !== 'manual') {
                throw new Exception("Your account uses external authentication ('" . $USER->auth . "'). Password changes must be done via that provider or by an administrator.");
            }

            if (empty($current_password)) {
                throw new Exception('Current password is required to change your password.');
            }

            $auth_plugin = get_auth_plugin($USER->auth);
            if (!$auth_plugin->user_login($USER->username, $current_password)) {
                throw new Exception('Current password is incorrect.');
            }

            // Validate password strength
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

            // Set new password using Moodle user lib (internal accounts only)
            update_internal_user_password($USER, $new_password);
            // Log that a password change was attempted (do NOT log the password itself)
            error_log('Profile AJAX: password change succeeded for user id=' . $USER->id . ' auth=' . ($USER->auth ?? 'unknown'));
        }

        // Save user changes
        user_update_user($USER, true);

        echo json_encode(['success' => true, 'message' => 'Profile and credentials updated successfully!']);
        exit;
    }
    
    else if ($action === 'update_credentials') {
        // Only allow password change here (verify current password first)
        $current_password = optional_param('current_password', '', PARAM_RAW);
        $new_password = optional_param('new_password', '', PARAM_RAW);

        if (empty($current_password) || empty($new_password)) {
            throw new Exception('Current password and new password are required.');
        }

        // Validate current password via auth plugin
        $auth_plugin = get_auth_plugin($USER->auth);
        if (!$auth_plugin->user_login($USER->username, $current_password)) {
            throw new Exception('Current password is incorrect.');
        }

        // Validate password strength
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

        // Update password using Moodle user lib
        update_internal_user_password($USER, $new_password);
        user_update_user($USER, true);

        echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);
        exit;
    }
    
    throw new Exception('Invalid action specified.');
    
} catch (Exception $e) {
    $error_msg = $e->getMessage();
    $action = optional_param('action', 'unknown', PARAM_TEXT);
    error_log('Profile AJAX Error [' . $action . ']: ' . $error_msg);
    error_log('POST data: ' . json_encode($_POST, JSON_UNESCAPED_SLASHES));
    // Return 400 only for validation errors, 200 for auth errors
    if (strpos($error_msg, 'Invalid') !== false || strpos($error_msg, 'already') !== false) {
        http_response_code(200);
    } else {
        http_response_code(400);
    }
    echo json_encode(['success' => false, 'message' => $error_msg]);
    exit;
} catch (Throwable $e) {
    error_log('Profile AJAX Fatal Error: ' . $e->getMessage());
    error_log('Stack: ' . $e->getTraceAsString());
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage()]);
    exit;
}
?>
