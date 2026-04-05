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
    
    <style>
        :root {
            --primary-color: #4361ee;
            --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            background: var(--bg-gradient) !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        #page, #page-wrapper, .drawers, nav.navbar { display: none !important; }

        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 30px;
            width: 100%;
            max-width: 480px;
        }

        .form-label { font-weight: 600; font-size: 0.85rem; color: #444; }
        .form-control { border-radius: 10px; padding: 10px 15px; border: 1px solid #ddd; }
        .btn-auth { padding: 12px; border-radius: 10px; font-weight: 600; transition: 0.3s; }
        .input-group-text { background: #f8f9fa; border-radius: 10px 0 0 10px; color: #666; }
    </style>
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>
    <?php echo $OUTPUT->standard_top_of_body_html(); ?>

    <div class="auth-card">
        <div class="text-center mb-4">
            <?php if ($logo): ?>
                <img src="<?php echo $logo; ?>" alt="Logo" style="max-height: 60px;">
            <?php else: ?>
                <h3 class="fw-bold text-primary">MY SCHOOL - LOGIN</h3>
            <?php endif; ?>
        </div>

        <form action="<?php echo $CFG->wwwroot; ?>/theme/mytheme/pages/login.php" method="post">
            <input type="hidden" name="logintoken" value="<?php echo $logintoken; ?>">
            
            <input type="hidden" name="wantsurl" value="<?php echo $CFG->wwwroot; ?>/theme/mytheme/pages/dashboard.php">
            
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="loginpass" class="form-control" placeholder="Password" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePass('loginpass', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="rememberusername" id="rememberMe">
                    <label class="form-check-label small" for="rememberMe">Remember me</label>
                </div>
                <a href="<?php echo $forgotpassurl; ?>" class="small text-decoration-none">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-auth shadow-sm">Login</button>
            
            <div class="text-center mt-3">
                <p class="small text-muted">खाता छैन? 
                    <a href="?register=1" class="text-decoration-none fw-bold text-primary">Register Here</a>
                </p>
            </div>
        </form>
    </div>

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