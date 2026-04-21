<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/theme/mytheme/lib.php');

// १. URL मा #register छ कि भनेर चेक गर्ने logic
// नोट: ब्राउजरले सर्भरमा ह्यास (#) पठाउँदैन, त्यसैले हामीले 
// 'wantsurl' वा 'register' प्यारामिटर चेक गर्नु राम्रो हुन्छ।
$is_register = optional_param('register', 0, PARAM_INT) || (strpos($_SERVER['REQUEST_URI'], 'register') !== false);

if ($is_register) {
    // यदि रजिस्टर मोड हो भने register.php लोड गर्ने
    include('register.php');
    exit;
}

// --- लगइन मोडको कोड यहाँबाट सुरु हुन्छ ---

// Moodle context and tokens
$logintoken = \core\session\manager::get_login_token();
$forgotpassurl = (new moodle_url('/login/forgot_password.php'))->out(false);
$PAGE->requires->css('/theme/mytheme/styles/login.css');

// Theme settings
$templatecontext = theme_mytheme_get_base_context();
$logo = !empty($templatecontext['setting']['logo']) ? $templatecontext['setting']['logo'] : false;

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $PAGE->title; ?></title>
    <?php echo $OUTPUT->standard_head_html(); ?>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">

</head>

<body>
    <main class="p-4">
        <div class="amd-lms-login-main-container">
            <!-- This entire container is now hidden on small screens -->
            <div class="amd-lms-login-form-container">
                <div class="amd-login-top-part">
                    <!-- Form Controls (Buttons) -->
                    <div class="amd-lms-login-form-controls">
                        <a href="?login=1"><button class="amd-lms-login-control-btn amd-lms-login-active-btn"
                                data-form="login">Login</button></a>
                        <a href="?register=1"> <button class="amd-lms-login-control-btn"
                                data-form="register">Register</button>
                        </a>
                    </div>
                </div>
                <!-- Wrapper for the sliding forms -->
                <div class="amd-lms-login-forms-wrapper amd-login-bottom-part">

                    <form action="<?php echo $CFG->wwwroot; ?>/theme/mytheme/pages/login.php" method="post"
                        class="amd-lms-login-form amd-lms-login-active" id="login-form">
                        <input type="hidden" name="logintoken" value="<?php echo $logintoken; ?>">

                        <input type="hidden" name="wantsurl"
                            value="<?php echo $CFG->wwwroot; ?>/theme/mytheme/pages/dashboard.php">
                        <h2>Welcome Back!</h2>
                        <p>Enter your credentials to access your account.</p>
                        <div class="amd-lms-login-input-group">
                            <input type="text" name="username" id="login-user" required placeholder=" ">
                            <label for="login-user">User Name</label>
                        </div>
                        <div class="amd-lms-login-input-group" style="position: relative;">
                            <input type="password" id="login-password" name="password" required placeholder=" ">
                            <label for="login-password">Password</label>

                            <!-- Login Form toggle -->
                            <span class="amd-eye-toggle togglePassword"
                                style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); cursor: pointer; font-size: 1.3rem; color: var(--amd-muted);">
                                <i class="fas fa-eye-slash"></i>
                            </span>
                        </div>
                        <button type="submit" class="amd-lms-login-submit-btn">Login</button>

                    </form>

                </div>
            </div>

            <!-- This container is now the ONLY thing visible on small screens -->
            <div class="amd-lms-login-image-container">
                <div>
                    <div class="amd-right-side-logo border-bottom mb-4 pb-3 pt-2">
                        <?php if ($logo): ?>
                            <img src="<?php echo $logo; ?>" alt="Logo">
                        <?php else: ?>
                            <h3 class="fw-bold text-primary">LOGIN</h3>
                        <?php endif; ?>
                    </div>
                    <div class="amd-right-side-content">
                        <img src="https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcS9kZBLfczhN4oUfH6gvV9k4sZ6ZyWOvdc1v7Y7mWbKerWFO8-l"
                            alt="">
                    </div>
                </div>
            </div>
    </main>

    <div style="display:none;"><?php echo $OUTPUT->main_content(); ?></div>
    <?php echo $OUTPUT->standard_end_of_body_html(); ?>

    <script>
        function togglePass(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    </script>
</body>

</html>