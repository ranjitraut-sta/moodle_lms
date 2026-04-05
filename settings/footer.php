<?php
defined('MOODLE_INTERNAL') || die;

// Create the footer settings page
$temp = new admin_settingpage('theme_mytheme_footer', get_string('footerheading', 'theme_mytheme'));

// --- Section 1: General Footer Settings ---
$name = 'theme_mytheme_footergeneralheading';
$heading = "General Footer Settings";
$setting = new admin_setting_heading($name, $heading, '');
$temp->add($setting);

// Footer background image
$name = 'theme_mytheme/footerbgimg';
$title = "Footer Background Image";
$description = "Upload a background image for the footer.";
$setting = new admin_setting_configstoredfile($name, $title, $description, 'footerbgimg');
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Footer background Overlay Opacity
$name = 'theme_mytheme/footerbgOverlay';
$title = "Background Opacity";
$description = "Set the transparency of the footer background overlay.";
$opacity = array_combine(range(0, 1, 0.1), range(0, 1, 0.1));
$setting = new admin_setting_configselect($name, $title, $description, '0.4', $opacity);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Copyright Text
$name = 'theme_mytheme/footercopyright';
$title = 'Copyright Text';
$description = 'Copyright text for footer';
$default = '© 2026 All rights reserved';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$temp->add($setting);

// --- Section 2: Footer Block 1 (Logo & Info) ---
$setting = new admin_setting_heading('theme_mytheme_f1', "Footer Block 1 (Logo & Text)", '');
$temp->add($setting);

// Footer Logo
$name = 'theme_mytheme/footerlogo';
$title = "Footer Logo";
$setting = new admin_setting_configstoredfile($name, $title, '', 'footerlogo');
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// Footnote (HTML Editor)
$name = 'theme_mytheme/footnote';
$title = "About Us / Description";
$default = "Welcome to our LMS platform.";
$setting = new admin_setting_confightmleditor($name, $title, '', $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$temp->add($setting);

// --- Section 3: Footer Block 2 (Links) ---
$setting = new admin_setting_heading('theme_mytheme_f2', "Footer Block 2 (Links)", '');
$temp->add($setting);

$name = 'theme_mytheme/infolink';
$title = "Information Links (Textarea)";
$description = "Enter links line by line.";
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$temp->add($setting);

// --- Section 4: Footer Block 3 (Contact) ---
$setting = new admin_setting_heading('theme_mytheme_f3', "Footer Block 3 (Contact)", '');
$temp->add($setting);

$temp->add(new admin_setting_configtext('theme_mytheme/address', 'Address', '', 'Kathmandu, Nepal'));
$temp->add(new admin_setting_configtext('theme_mytheme/footeremail', 'Email', '', 'support@lms.com'));
$temp->add(new admin_setting_configtext('theme_mytheme/phoneno', 'Phone Number', '', '+977-123456789'));

// --- Section 5: Social Media (Loop) ---
$setting = new admin_setting_heading('theme_mytheme_f4', "Footer Block 4 (Social Media)", '');
$temp->add($setting);

$name = 'theme_mytheme/numofsocialmedia';
$title = "Number of Social Icons";
$choices = array_combine(range(1, 6), range(1, 6));
$setting = new admin_setting_configselect($name, $title, '', 4, $choices);
$temp->add($setting);

$num = get_config('theme_mytheme', 'numofsocialmedia') ?: 4;
for ($i = 1; $i <= $num; $i++) {
    $temp->add(new admin_setting_configtext("theme_mytheme/socialmedia{$i}_icon", "Icon {$i} (e.g. fa-facebook)", '', 'fa-facebook'));
    $temp->add(new admin_setting_configtext("theme_mytheme/socialmedia{$i}_url", "URL {$i}", '', 'https://facebook.com'));
    $temp->add(new admin_setting_configcolourpicker("theme_mytheme/socialmedia{$i}_color", "Color {$i}", '', '#ffffff'));
}

$settings->add($temp);