<?php
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // Create the footer settings page
    $temp = new admin_settingpage('theme_mytheme_footer', get_string('footerheading', 'theme_mytheme'));

    // --- Section 1: General Footer Settings ---
    $temp->add(new admin_setting_heading('theme_mytheme_footergeneralheading', "General Footer Settings", ''));

    // Footer Background
    $setting = new admin_setting_configstoredfile('theme_mytheme/footerbgimg', "Footer Background Image", "", 'footerbgimg');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Opacity
    $opacity = array_combine(range(0, 1, 0.1), range(0, 1, 0.1));
    $temp->add(new admin_setting_configselect('theme_mytheme/footerbgOverlay', "Background Opacity", "", '0.4', $opacity));

    // Copyright & Powered By
    $temp->add(new admin_setting_configtext('theme_mytheme/footercopyright', 'Copyright Text', '', '© 2026 All rights reserved'));
    $temp->add(new admin_setting_configtext('theme_mytheme/poweredby_name', 'Powered By (Name)', '', 'LMS Expert'));
    $temp->add(new admin_setting_configtext('theme_mytheme/poweredby_url', 'Powered By (URL)', '', 'https://example.com'));

    // --- Section 2: Footer Branding (Column 1) ---
    $temp->add(new admin_setting_heading('theme_mytheme_f1', "Column 1: Logo & About", ''));
    $temp->add(new admin_setting_configstoredfile('theme_mytheme/footerlogo', "Copy Right Logo", '', 'footerlogo'));
    $temp->add(new admin_setting_confightmleditor('theme_mytheme/footnote', "About Us Description", '', 'Welcome to our platform.'));

    // --- Section 3: Navigation Links (Column 2) ---
    $temp->add(new admin_setting_heading('theme_mytheme_f2', "Column 2: Quick Links", 'Format: Name|URL (One per line)'));
    $temp->add(new admin_setting_configtextarea('theme_mytheme/footerlinks_col1', "Links", "Home|https://site.com", ""));

    // --- Section 4: Opening Hours (Column 3) ---
    $temp->add(new admin_setting_heading('theme_mytheme_f3', "Column 3: Opening Hours", 'Text to show the operation time'));
    $temp->add(new admin_setting_configtext('theme_mytheme/opening_weekdays', 'Weekdays (Mon-Fri)', '', 'Monday - Friday: 9:00 AM - 5:00 PM'));
    $temp->add(new admin_setting_configtext('theme_mytheme/opening_weekends', 'Weekends (Sat-Sun)', '', 'Saturday & Sunday - Closed'));

    // --- Section 5: Supported By (Column 4) ---
    $temp->add(new admin_setting_heading('theme_mytheme_f4', "Column 4: Supported By (3 Logos)", 'Upload partner or supporter logos'));

    for ($i = 1; $i <= 3; $i++) {
        $name = "theme_mytheme/supported_logo_{$i}";
        $temp->add(new admin_setting_configstoredfile($name, "Supporter Logo {$i}", '', "supported_logo_{$i}"));
        $temp->add(new admin_setting_configtext("theme_mytheme/supported_url_{$i}", "Supporter URL {$i}", '', '#'));
    }

    // --- Section 6: Contact Info ---
    $temp->add(new admin_setting_heading('theme_mytheme_f5', "Contact Details", ''));
    $temp->add(new admin_setting_configtext('theme_mytheme/address', 'Address', '', 'Kathmandu, Nepal'));
    $temp->add(new admin_setting_configtext('theme_mytheme/footeremail', 'Email', '', 'support@lms.com'));
    $temp->add(new admin_setting_configtext('theme_mytheme/phoneno', 'Phone', '', '+977-123456789'));

    // --- Section 7: Social Media ---
    $temp->add(new admin_setting_heading('theme_mytheme_f6', "Social Media Icons", ''));
    for ($i = 1; $i <= 4; $i++) {
        $temp->add(new admin_setting_configtext("theme_mytheme/socialmedia{$i}_icon", "Icon {$i} (e.g. fa-facebook)", '', ''));
        $temp->add(new admin_setting_configtext("theme_mytheme/socialmedia{$i}_url", "URL {$i}", '', ''));
        $temp->add(new admin_setting_configcolourpicker("theme_mytheme/socialmedia{$i}_color", "Color {$i}", '', '#ffffff'));
    }

    $settings->add($temp);
}