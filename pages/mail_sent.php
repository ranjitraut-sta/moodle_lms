<?php
// theme/mytheme/pages/mail_sent.php

require_once(__DIR__ . '/../../../config.php');

// URL बाट युजरको इमेल र टाइप थाहा पाउने
$email = optional_param('email', '', PARAM_EMAIL);
$type = optional_param('type', 'register', PARAM_ALPHA); // register वा reset

$PAGE->set_url(new moodle_url('/theme/mytheme/pages/mail_sent.php', ['email' => $email, 'type' => $type]));
$PAGE->set_pagelayout('login'); // थिमको लगिन वा कस्टुम लेआउट
$PAGE->set_context(context_system::instance());

// टाइप अनुसार हेडिङ र म्यासेज तय गर्ने
if ($type === 'reset') {
    $heading = "Reset link sent!";
    $message = "A password reset link has been successfully sent to<br>"
        . "<strong style='color: #007bff; word-break: break-all;'>" . s($email) . "</strong>.<br>"
        . "Please check your inbox and click the link to set a new password.";
} else {
    // Default: Register को लागि
    $heading = "Check your inbox!";
    $message = "A verification email has been successfully sent to<br>"
        . "<strong style='color: #007bff; word-break: break-all;'>" . s($email) . "</strong>.<br>"
        . "Please check your email and click the confirmation link to activate your account.";
}

echo $OUTPUT->header();
?>

<div class="custom-mail-sent-container" style="
    text-align: center; 
    max-width: 500px; 
    margin: 80px auto; 
    padding: 40px 20px; 
    background: #ffffff; 
    border-radius: 12px; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
">
    <div class="icon-box" style="font-size: 64px; color: #28a745; margin-bottom: 20px;">
        ✉️
    </div>

    <!-- डाइनामिक हेडिङ -->
    <h2 style="font-size: 26px; color: #111; font-weight: 600; margin-bottom: 15px;">
        <?php echo $heading; ?>
    </h2>

    <!-- डाइनामिक म्यासेज -->
    <p style="font-size: 16px; color: #555; line-height: 1.6; margin-bottom: 25px;">
        <?php echo $message; ?>
    </p>

    <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 25px;">

    <a href="<?php echo new moodle_url('/login/index.php'); ?>" class="btn-goto-login" style="
        background-color: #007bff; 
        color: #ffffff; 
        padding: 12px 35px; 
        border-radius: 8px; 
        text-decoration: none; 
        font-weight: 500;
        display: inline-block;
        transition: background 0.2s ease;
        box-shadow: 0 4px 6px rgba(0,123,255,0.15);
    ">
        Back to Login
    </a>
</div>

<?php
echo $OUTPUT->footer();
exit;