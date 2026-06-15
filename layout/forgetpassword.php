<?php
defined('MOODLE_INTERNAL') || die();

$PAGE->requires->css('/theme/mytheme/styles/login.css');
$templatecontext = theme_mytheme_get_base_context();
$logo = !empty($templatecontext['setting']['logo']) ? $templatecontext['setting']['logo'] : false;
$forgotpassurl = (new moodle_url('/theme/mytheme/pages/forgetpassword.php'))->out(false);
// लगइन र रजिष्ट्रेसन पेजका लागि सही मडुल URL हरू तयार गर्ने
$loginurl = new moodle_url('/login/index.php');
$registerurl = new moodle_url('/login/signup.php'); // अथवा तपाईंको कस्टुम रजिष्टर पेज छ भने त्यसको पाथ (जस्तै: /theme/mytheme/pages/register.php)

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo $PAGE->title; ?></title>
    <?php echo $OUTPUT->standard_head_html(); ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&display=swap" rel="stylesheet">
</head>

<body>
    <main class="p-4">
        <div class="amd-lms-login-main-container">
            <div class="amd-lms-login-form-container">
                         <div class="amd-login-top-part">

                    <!-- Form Controls (Buttons) -->
                    <div class="amd-lms-login-form-controls">
                        <a href="<?php echo $loginurl; ?>"><button class="amd-lms-login-control-btn"
                                data-form="login">Login</button></a>
                        <a href="<?php echo $loginurl; ?>/register=1"> <button class="amd-lms-login-control-btn"
                                data-form="register">Register</button>
                        </a>
                        <a href="<?php echo $forgotpassurl; ?>"> <button class="amd-lms-login-control-btn amd-lms-login-active-btn"
                                data-form="forgot">Forgot Password</button>
                        </a>
                    </div>
                </div>
                
                <div class="amd-lms-login-forms-wrapper p-4">

                    <!-- Global Error Messages -->
                    <?php if (!empty($errors['global'])): ?>
                        <div class="alert alert-danger small"><?php echo s($errors['global']); ?></div>
                    <?php endif; ?>

                    <!-- ==========================================
                         CASE 1: REQUEST EMAIL FORM (इमेल माग्ने फारम)
                         ========================================== -->
                    <?php if ($action === 'request'): ?>
                        <form action="<?php echo $CFG->wwwroot; ?>/theme/mytheme/pages/forgetpassword.php?action=send_email"
                            method="post" class="amd-lms-login-form amd-lms-login-active">

                            <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">

                            <h2>Forgot Password?</h2>
                            <p>Enter your registered email address to receive a password reset link.</p>

                            <div class="amd-lms-login-input-group mt-4">
                                <input type="email" name="email" id="reset-email" required placeholder=" "
                                    class="form-control">
                                <label for="reset-email">Email Address</label>
                            </div>

                            <?php if (!empty($errors['email'])): ?>
                                <div class="text-danger small mt-1"><?php echo s($errors['email']); ?></div>
                            <?php endif; ?>

                            <button type="submit" class="amd-lms-login-submit-btn mt-4">Send Reset Link</button>

                            <div class="text-center mt-3">
                                <a href="<?php echo $CFG->wwwroot; ?>/login/index.php" class="small text-decoration-none"><i
                                        class="fas fa-arrow-left"></i> Back to Login</a>
                            </div>
                        </form>

                        <!-- ==========================================
                         CASE 2: SUCCESS MESSAGE (लिङ्क सफलतापूर्वक गएपछि)
                         ========================================== -->
                    <?php elseif ($action === 'message'): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-paper-plane fa-3x text-success mb-3"></i>
                            <h2>Check Your Email</h2>
                            <p class="text-muted"><?php echo s($success_message); ?></p>
                            <a href="<?php echo $CFG->wwwroot; ?>/login/index.php" class="btn btn-primary mt-3">Go to
                                Login</a>
                        </div>

                        <!-- ==========================================
                         CASE 3: RESET PASSWORD FORM (नयाँ पासवर्ड हाल्ने फारम)
                         ========================================== -->
                    <?php elseif ($action === 'reset_form'): ?>
                        <form action="forgetpassword.php?action=update_password" method="post"
                            class="amd-lms-login-form amd-lms-login-active">
                            <input type="hidden" name="userid" value="<?php echo $userid; ?>">
                            <input type="hidden" name="token" value="<?php echo s($token); ?>">

                            <h2>Set New Password</h2>
                            <p>Please enter your new password below.</p>

                            <!-- New Password Input -->
                            <div class="amd-lms-login-input-group mt-3" style="position: relative;">
                                <input type="password" id="new-password" name="password" required placeholder=" ">
                                <label for="new-password">New Password</label>
                                <span class="amd-eye-toggle" id="togglePass1"
                                    style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer; color: #aaa;">
                                    <i class="fas fa-eye-slash" id="eyeIcon1"></i>
                                </span>
                            </div>

                            <!-- Confirm Password Input -->
                            <div class="amd-lms-login-input-group mt-3" style="position: relative;">
                                <input type="password" id="confirm-password" name="password_confirmation" required
                                    placeholder=" ">
                                <label for="confirm-password">Confirm Password</label>
                                <span class="amd-eye-toggle" id="togglePass2"
                                    style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer; color: #aaa;">
                                    <i class="fas fa-eye-slash" id="eyeIcon2"></i>
                                </span>
                            </div>

                            <?php if (!empty($errors['password'])): ?>
                                <div class="text-danger small mt-2"><?php echo s($errors['password']); ?></div>
                            <?php endif; ?>

                            <button type="submit" class="amd-lms-login-submit-btn mt-4">Update Password</button>
                        </form>

                        <script>
                            // Eye button toggle logic for both fields
                            function setupPasswordToggle(toggleId, inputId, iconId) {
                                const toggle = document.getElementById(toggleId);
                                const input = document.getElementById(inputId);
                                const icon = document.getElementById(iconId);

                                toggle.addEventListener('click', function () {
                                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                                    input.setAttribute('type', type);
                                    if (type === 'password') {
                                        icon.classList.replace('fa-eye', 'fa-eye-slash');
                                    } else {
                                        icon.classList.replace('fa-eye-slash', 'fa-eye');
                                    }
                                });
                            }
                            setupPasswordToggle('togglePass1', 'new-password', 'eyeIcon1');
                            setupPasswordToggle('togglePass2', 'confirm-password', 'eyeIcon2');
                        </script>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Right side Banner (तपाईँकै लगइन थिम जस्तै) -->
            <div class="amd-lms-login-image-container">
                <div>
                    <div class="amd-right-side-logo border-bottom mb-4 pb-3 pt-2">
                        <?php if ($logo): ?>
                            <a href="<?php echo $CFG->wwwroot; ?>" class="text-decoration-none">
                                <img src="<?php echo $logo; ?>" alt="Logo">
                            </a>
                        <?php else: ?>
                            <h3 class="fw-bold text-primary">PASSWORD RESET</h3>
                        <?php endif; ?>
                    </div>
                    <div class="amd-right-side-content">
                        <img src="<?php echo $CFG->wwwroot . '/theme/mytheme/amd/pix/login.jpg'; ?>" alt="Reset Image">
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div style="display:none;"><?php echo $OUTPUT->main_content(); ?></div>
    <?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>

</html>