<?php
require_once('../../../config.php');
require_once($CFG->libdir . '/authlib.php');

// १. सुरक्षा जाँच
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(new moodle_url('/login/index.php'));
}

// २. डाटा प्राप्त गर्ने
$username = optional_param('username', '', PARAM_RAW);
$password = optional_param('password', '', PARAM_RAW);

// ३. युजर अथेन्टिकेसन
$user = authenticate_user_login($username, $password);

if ($user) {
    // खाता सस्पेन्ड वा डिलिट छ कि चेक गर्ने
    if (isguestuser($user) || $user->suspended || $user->deleted) {
        redirect(new moodle_url('/login/index.php'), "Your account is disabled.", 5);
    }

    // ४. मडल सेसनमा लगइन गराउने
    complete_user_login($user);

    // ५. रोल अनुसार Redirect गर्ने Logic
    // यहाँ हामी चेक गर्छौं कि युजर एडमिन हो कि होइन
    if (is_siteadmin($user)) {
        // यदि एडमिन हो भने मडलको डिफोल्ट एडमिन पेजमा पठाउने
        $redirecturl = new moodle_url('/admin/index.php');
    } else {
        // यदि विद्यार्थी वा सामान्य युजर हो भने तपाईंको कस्टम ड्यासबोर्डमा पठाउने
$redirecturl = new moodle_url('/theme/mytheme/layout/dashboard.php');
    }

    redirect($redirecturl);

} else {
    // लगइन फेल भयो भने
    $loginurl = new moodle_url('/login/index.php', ['errorcode' => 3]);
    redirect($loginurl, "Invalid username or password.", 3);
}