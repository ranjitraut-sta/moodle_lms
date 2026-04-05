<?php
defined('MOODLE_INTERNAL') || die;

$page = new admin_settingpage('theme_mytheme_general', get_string('generalsettings', 'theme_mytheme'));

// Logo
$name = 'theme_mytheme/logo';
$title = get_string('logo', 'theme_mytheme');
$description = get_string('logodesc', 'theme_mytheme');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
$page->add($setting);

// Favicon
$name = 'theme_mytheme/favicon';
$title = get_string('favicon', 'theme_mytheme');
$description = get_string('favicondesc', 'theme_mytheme');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'favicon');
$page->add($setting);

// Facebook
$name = 'theme_mytheme/facebook';
$title = 'Facebook URL';
$description = 'Enter your Facebook page link';
$setting = new admin_setting_configtext($name, $title, $description, '');
$page->add($setting);

// Twitter
$name = 'theme_mytheme/twitter';
$title = 'Twitter URL';
$description = 'Enter your Twitter link';
$setting = new admin_setting_configtext($name, $title, $description, '');
$page->add($setting);

// Instagram
$name = 'theme_mytheme/instagram';
$title = 'Instagram URL';
$description = 'Enter your Instagram link';
$setting = new admin_setting_configtext($name, $title, $description, '');
$page->add($setting);

// LinkedIn
$name = 'theme_mytheme/linkedin';
$title = 'LinkedIn URL';
$description = 'Enter your LinkedIn link';
$setting = new admin_setting_configtext($name, $title, $description, '');
$page->add($setting);

// Primary Color
$name = 'theme_mytheme/primarycolor';
$title = 'Primary Color';
$description = 'Main theme color';
$default = '#0f6cbf';

$setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
$page->add($setting);

// Secondary Color
$name = 'theme_mytheme/secondarycolor';
$title = 'Secondary Color';
$description = 'Secondary theme color';
$default = '#6c757d';

$setting = new admin_setting_configcolourpicker($name, $title, $description, $default);
$page->add($setting);

$name = 'theme_mytheme/preloader';
$title = 'Enable Preloader';
$description = 'Show loading animation';
$setting = new admin_setting_configcheckbox($name, $title, $description, 0);
$page->add($setting);

// Custom Menu
$name = 'theme_mytheme/custommenu';
$title = 'Custom Menu Items';
$description = 'One item per line. Format: Label|URL<br>Example:<br>Home|/<br>Courses|/course/index.php<br>About|/about';
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$page->add($setting);

$settings->add($page);
