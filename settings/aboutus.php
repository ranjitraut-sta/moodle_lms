<?php
defined('MOODLE_INTERNAL') || die;

$page = new admin_settingpage('theme_mytheme_aboutus', 'About Us Settings');

// About Image
$name = 'theme_mytheme/aboutimage';
$title = 'About Image';
$description = 'Upload image for about section';
$setting = new admin_setting_configstoredfile($name, $title, $description, 'aboutimage');
$page->add($setting);

// About Us Heading
$name = 'theme_mytheme/aboutheading';
$title = 'About Heading';
$description = 'Heading for about section';
$default = 'Best Places For Learn Everything';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

// About Us Description
$name = 'theme_mytheme/aboutdesc';
$title = 'About Description';
$description = 'Description for about section';
$default = 'A Learning platform based on practical knowledge with world class mentors.';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

// Stat 1 Number
$name = 'theme_mytheme/stat1_number';
$title = 'Stat 1 Number';
$description = 'First statistic number (e.g., 25+)';
$default = '25+';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

// Stat 1 Description
$name = 'theme_mytheme/stat1_desc';
$title = 'Stat 1 Description';
$description = 'First statistic description';
$default = 'Years of eLearning<br>Education Experience';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

// Stat 2 Number
$name = 'theme_mytheme/stat2_number';
$title = 'Stat 2 Number';
$description = 'Second statistic number (e.g., 56k)';
$default = '56k';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

// Stat 2 Description
$name = 'theme_mytheme/stat2_desc';
$title = 'Stat 2 Description';
$description = 'Second statistic description';
$default = 'Students Enrolled in<br>Our Courses';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

// Stat 3 Number
$name = 'theme_mytheme/stat3_number';
$title = 'Stat 3 Number';
$description = 'Third statistic number (e.g., 170+)';
$default = '170+';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

// Stat 3 Description
$name = 'theme_mytheme/stat3_desc';
$title = 'Stat 3 Description';
$description = 'Third statistic description';
$default = 'Experienced Teacher\'s<br>service';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

$settings->add($page);
