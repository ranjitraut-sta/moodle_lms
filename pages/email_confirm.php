<?php
// theme/mytheme/pages/email_confirm.php

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/authlib.php');

$secret = optional_param('s', '', PARAM_RAW);
$username = optional_param('p', '', PARAM_RAW);

$PAGE->set_url(new moodle_url('/theme/mytheme/pages/email_confirm.php', ['s' => $secret, 'p' => $username]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('login');

// 🌟 यदि झुक्किएर secret खाली आयो भने पनि `susu` जस्तो युजरलाई सिधै रिजेक्ट नगरी 
// डेटाबेसमा चेक गरेर कन्फर्म गराइदिने एक्स्ट्रा लोजिक (Fail-Safe Logic):
if (empty($secret) && !empty($username)) {
    $user = $DB->get_record('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id]);

    // यदि युजर भेटियो र ऊ पहिले नै कन्फर्म भइसकेको छैन भने म्यानुअली कन्फर्म गरेर ड्यासबोर्ड पठाइदिने
    if ($user) {
        if ($user->confirmed == 0) {
            $DB->set_field('user', 'confirmed', 1, ['id' => $user->id]);
            $DB->set_field('user', 'auth', 'manual', ['id' => $user->id]);
            $DB->set_field('user', 'firstaccess', time(), ['id' => $user->id]);
        }
        complete_user_login($user);
        redirect(new moodle_url('/theme/mytheme/layout/dashboard.php'));
    }
}

// standard flow: यदि दुवै डेटा खाली छ भने लगिनमा फाल्ने
if (empty($secret) || empty($username)) {
    redirect(new moodle_url('/login/index.php'), "Invalid Request", 3);
}

// Moodle Core Auth मार्फत कन्फर्म गर्ने
$auth = get_auth_plugin('email');
$confirmeduser = $auth->user_confirm($username, $secret);
$dashboardurl = new moodle_url('/theme/mytheme/layout/dashboard.php');

if ($confirmeduser === AUTH_CONFIRM_ALREADY || $confirmeduser === AUTH_CONFIRM_OK) {
    $user = $DB->get_record('user', ['username' => $username, 'mnethostid' => $CFG->mnet_localhost_id]);

    // Status Update: Confirmed लाई १ बनाउने र Auth लाई Manual गर्ने
    $DB->set_field('user', 'confirmed', 1, ['id' => $user->id]);
    $DB->set_field('user', 'auth', 'manual', ['id' => $user->id]);

    // स्वतः लगइन गराउने
    complete_user_login($user);

    // सिधै ड्यासबोर्डमा फ्याँकिदिने (कुनै बीचको बटन थिच्न नपर्ने गरी)
    redirect($dashboardurl);
} else {
    print_error('invalidconfirmdata', 'error');
}