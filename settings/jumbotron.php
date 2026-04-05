<?php
defined('MOODLE_INTERNAL') || die;

$page = new admin_settingpage('theme_mytheme_jumbotron', get_string('jumbotron', 'theme_mytheme'));

// Jumbotron Heading
$name = 'theme_mytheme/jumbotronheading';
$title = 'Jumbotron Heading';
$description = 'Main heading for hero section';
$default = 'Welcome to Our Learning Platform';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

// Jumbotron Description
$name = 'theme_mytheme/jumbotrondesc';
$title = 'Jumbotron Description';
$description = 'Description text for hero section';
$default = 'Start your learning journey today';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$page->add($setting);

// Jumbotron Button Text
$name = 'theme_mytheme/jumbotronbtntext';
$title = 'Button Text';
$description = 'Text for call-to-action button';
$default = 'Get Started';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

// Jumbotron Button Link
$name = 'theme_mytheme/jumbotronbtnlink';
$title = 'Button Link';
$description = 'URL for call-to-action button';
$default = '#';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$page->add($setting);

$settings->add($page);
