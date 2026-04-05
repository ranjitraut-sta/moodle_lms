<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/theme/mytheme/lib.php');

$templatecontext = theme_mytheme_get_base_context();

// Check if signup is enabled
$signupurl  = null;
$authplugin = get_auth_plugin('email');
if (!empty($CFG->registerauth) && $authplugin->can_signup()) {
    $signupurl = (new moodle_url('/login/signup.php'))->out(false);
}

$templatecontext['signupurl']    = $signupurl;
$templatecontext['loginerrors']  = $OUTPUT->login_info();
$templatecontext['wwwroot']      = $CFG->wwwroot;
$templatecontext['sesskey']      = sesskey();
$templatecontext['logintoken']   = \core\session\manager::get_login_token();
$templatecontext['forgotpassurl']= (new moodle_url('/login/forgot_password.php'))->out(false);

$bootstrapcss = $CFG->wwwroot . '/theme/mytheme/styles/bootstrap.min.css';
$allcss       = $CFG->wwwroot . '/theme/mytheme/styles/all.min.css';
$logincss     = $CFG->wwwroot . '/theme/mytheme/styles/login.css';

echo $OUTPUT->doctype();
?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $PAGE->title; ?></title>
    <?php echo $OUTPUT->standard_head_html(); ?>
    <link rel="stylesheet" href="<?php echo $bootstrapcss; ?>">
    <link rel="stylesheet" href="<?php echo $allcss; ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $logincss; ?>">
    <style>
        /* Force override Moodle/Boost styles */
        body { background: var(--amd-body-bg) !important; }
        #page, #page-wrapper, .drawers, nav.navbar { display: none !important; }
.amd-auth-wrap {
    background-color: var(--amd-body-bg);
    background-image:
        radial-gradient(circle at 5% 15%, var(--amd-fade-primary), transparent 40%),
        radial-gradient(circle at 95% 85%, var(--amd-fade-secondary), transparent 40%);
    min-height: 100vh;
    width: 100vw !important;
}
.amd-auth-card {
    background: color-mix(in srgb, var(--amd-secondary) 15%, #0d0d1a);
    backdrop-filter: blur(14px);
    border: 1px solid color-mix(in srgb, var(--amd-primary) 20%, transparent);
    border-radius: 16px;
    padding: 2.5rem 2rem;
    width: 100%;
    max-width: 500px !important;
    color: var(--amd-light);
    overflow-y: auto;
    max-height: 95vh;
}
    </style>
</head>
<body <?php echo $OUTPUT->body_attributes(); ?>>
<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<div class="amd-auth-wrap d-flex align-items-center justify-content-center min-vh-100">
    <div class="amd-auth-card shadow-lg">

        <!-- Logo -->
        <?php if (!empty($templatecontext['setting']['logo'])): ?>
        <div class="text-center mb-4">
            <a href="<?php echo $CFG->wwwroot; ?>">
                <img src="<?php echo $templatecontext['setting']['logo']; ?>" alt="Logo" style="max-height:60px;">
            </a>
        </div>
        <?php endif; ?>

        <!-- Tabs -->
        <ul class="nav nav-pills amd-auth-tabs mb-4" id="authTabs">
            <li class="nav-item flex-fill">
                <button class="nav-link active w-100" data-bs-toggle="pill" data-bs-target="#loginTab">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </button>
            </li>
            <li class="nav-item flex-fill">
                <button class="nav-link w-100" data-bs-toggle="pill" data-bs-target="#registerTab" id="register-tab-trigger">    
                    <i class="bi bi-person-plus me-1"></i> Register
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- Login Tab -->
            <div class="tab-pane fade show active" id="loginTab">
                <h5 class="fw-bold mb-4 text-center">Welcome Back!</h5>
                <form action="<?php echo $CFG->wwwroot; ?>/login/index.php" method="post" id="loginForm">
                    <input type="hidden" name="logintoken" value="<?php echo \core\session\manager::get_login_token(); ?>">
                    <input type="hidden" name="anchor" value="">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" id="username" class="form-control"
                                placeholder="Enter username" autocomplete="username" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="password" class="form-control"
                                placeholder="Enter password" autocomplete="current-password" required>
                            <button type="button" class="input-group-text bg-white border-start-0"
                                onclick="togglePass('password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="rememberusername" id="rememberMe" value="1">
                            <label class="form-check-label small" for="rememberMe">Remember me</label>
                        </div>
                        <a href="<?php echo (new moodle_url('/login/forgot_password.php'))->out(false); ?>"
                            class="small text-primary text-decoration-none">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login
                    </button>
                </form>
            </div>

            <!-- Register Tab -->
            <div class="tab-pane fade" id="registerTab">
                <h5 class="fw-bold mb-4 text-center">Create Account</h5>
                <form action="<?php echo $CFG->wwwroot; ?>/theme/mytheme/pages/register.php" method="post" id="registerForm">
                    <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email (confirm)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope-check"></i></span>
                            <input type="email" name="email2" class="form-control" placeholder="Confirm your email" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">First Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                            <input type="text" name="firstname" class="form-control" placeholder="First name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Last Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                            <input type="text" name="lastname" class="form-control" placeholder="Last name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" id="regpassword" class="form-control" placeholder="Choose a password" required>
                            <button type="button" class="input-group-text bg-white border-start-0"
                                onclick="togglePass('regpassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </button>
                </form>
            </div>

        </div>

        <div class="text-center mt-4">
            <a href="<?php echo $CFG->wwwroot; ?>" class="small text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Back to Home
            </a>
        </div>

    </div>
</div>

<div style="display:none;">
    <?php echo $OUTPUT->main_content(); ?>
</div>

<?php echo $OUTPUT->standard_end_of_body_html(); ?>

<script>
function togglePass(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
// Auto switch to register tab if #register in URL
if (window.location.hash === '#register') {
document.getElementById('register-tab-trigger')?.click();
}
</script>
</body>
</html>