<?php
defined('MOODLE_INTERNAL') || die;

$page = new admin_settingpage('theme_mytheme_menu', get_string('menusettings', 'theme_mytheme'));

// Number of menu items
$name        = 'theme_mytheme/menucount';
$title       = 'Number of Menu Items';
$description = 'How many menu items to show (max 10)';
$setting     = new admin_setting_configselect($name, $title, $description, 5, [
    1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5,
    6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10,
]);
$page->add($setting);

$menucount = get_config('theme_mytheme', 'menucount') ?: 5;

for ($i = 1; $i <= 10; $i++) {

    // Menu item heading
    $page->add(new admin_setting_heading(
        "theme_mytheme/menuheading{$i}",
        "Menu Item {$i}",
        ''
    ));

    // Label
    $name        = "theme_mytheme/menulabel{$i}";
    $title       = "Label";
    $description = '';
    $defaults    = ['Home', 'Courses', 'Categories', 'About', 'Contact', '', '', '', '', ''];
    $setting     = new admin_setting_configtext($name, $title, $description, $defaults[$i - 1] ?? '');
    $page->add($setting);

    // URL
    $name        = "theme_mytheme/menuurl{$i}";
    $title       = "URL";
    $description = '';
    $defaulturls = ['/', '/course/index.php', '/course/index.php', '/about', '/contact', '', '', '', '', ''];
    $setting     = new admin_setting_configtext($name, $title, $description, $defaulturls[$i - 1] ?? '');
    $page->add($setting);

    // Open in new tab
    $name        = "theme_mytheme/menunewtab{$i}";
    $title       = "Open in new tab";
    $description = '';
    $setting     = new admin_setting_configcheckbox($name, $title, $description, 0);
    $page->add($setting);
}

$settings->add($page);
