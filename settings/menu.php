<?php
defined('MOODLE_INTERNAL') || die;

$page = new admin_settingpage('theme_mytheme_menu', get_string('menusettings', 'theme_mytheme'));

// Number of menu items
$page->add(new admin_setting_configselect(
    'theme_mytheme/menucount',
    'Number of Menu Items',
    'How many menu items to show (max 10)',
    5,
    [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        10 => 10
    ]
));

for ($i = 1; $i <= 10; $i++) {

    // Heading
    $page->add(new admin_setting_heading(
        "theme_mytheme/menuheading{$i}",
        "Menu Item {$i}",
        ''
    ));

    // Label
    $page->add(new admin_setting_configtext(
        "theme_mytheme/menulabel{$i}",
        "Label",
        '',
        ''
    ));

    // URL
    $page->add(new admin_setting_configtext(
        "theme_mytheme/menuurl{$i}",
        "URL",
        '',
        ''
    ));

    // Open in new tab
    $page->add(new admin_setting_configcheckbox(
        "theme_mytheme/menunewtab{$i}",
        "Open in new tab",
        '',
        0
    ));

    // =========================
    // 📄 PDF UPLOAD (FIXED)
    // =========================
    $page->add(new admin_setting_configstoredfile(
        "theme_mytheme/menupdf{$i}",
        "Menu PDF File",
        "Upload PDF file for Menu Item {$i}",
        "menupdf{$i}",
        0,
        [
            'maxfiles' => 1,
            'accepted_types' => ['application/pdf']
        ]
    ));
}

$settings->add($page);