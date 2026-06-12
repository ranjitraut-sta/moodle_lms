<?php
// theme/mytheme/pages/mail_sent.php

require_once(__DIR__ . '/../../../config.php');

// URL बाट युजरको इमेल थाहा पाउने (UI मा देखाउनको लागि)
$email = optional_param('email', '', PARAM_EMAIL);

$PAGE->set_url(new moodle_url('/theme/mytheme/pages/mail_sent.php', ['email' => $email]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login'); // थिमको लगिन वा कस्टुम लेआउट

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

    <h2 style="font-size: 26px; color: #111; font-weight: 600; margin-bottom: 15px;">
        Check your inbox!
    </h2>

    <p style="font-size: 16px; color: #555; line-height: 1.6; margin-bottom: 25px;">
        A verification email has been successfully sent to <br>
        <strong style="color: #007bff; word-break: break-all;"><?php echo s($email); ?></strong>.<br>
        Please check your email and click the confirmation link to activate your account.
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
    }">
        Back to Login
    </a>
</div>

<?php
echo $OUTPUT->footer();
exit;