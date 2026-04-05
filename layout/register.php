<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/theme/mytheme/lib.php');

$sesskey = sesskey();
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
        :root { --primary-color: #4361ee; --bg-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        body { background: var(--bg-gradient) !important; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
        #page, #page-wrapper, .drawers, nav.navbar { display: none !important; }
        .auth-card { background: white; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.2); padding: 30px; width: 100%; max-width: 500px; }
        .form-label { font-weight: 600; font-size: 0.85rem; color: #444; }
        .form-control { border-radius: 10px; padding: 10px 15px; border: 1px solid #ddd; }
        .btn-auth { padding: 12px; border-radius: 10px; font-weight: 600; }
    </style>
</head>
<body <?php echo $OUTPUT->body_attributes(); ?>>
    <?php echo $OUTPUT->standard_top_of_body_html(); ?>

    <div class="auth-card">
        <div class="text-center mb-4">
            <?php if ($logo): ?>
                <img src="<?php echo $logo; ?>" alt="Logo" style="max-height: 60px;">
            <?php else: ?>
                <h3 class="fw-bold text-success">CREATE ACCOUNT</h3>
            <?php endif; ?>
        </div>

        <form action="<?php echo $CFG->wwwroot; ?>/theme/mytheme/pages/register.php" method="post">
            <input type="hidden" name="sesskey" value="<?php echo $sesskey; ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="firstname" class="form-control" placeholder="John" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="lastname" class="form-control" placeholder="Doe" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-at"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Choose username" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Confirm Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-check-circle"></i></span>
                    <input type="email" name="email2" class="form-control" placeholder="Repeat email" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                    <input type="password" name="password" id="regpass" class="form-control" placeholder="Create password" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePass('regpass', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 btn-auth shadow-sm">Create Account</button>
            
            <div class="text-center mt-3">
                <p class="small">Already have an account? <a href="<?php echo $CFG->wwwroot; ?>/login/index.php" class="text-decoration-none">Login here</a></p>
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