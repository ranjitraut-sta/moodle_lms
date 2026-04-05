<?php
defined('MOODLE_INTERNAL') || die;

$page = new admin_settingpage('theme_mytheme_slider', 'Home Slider Settings');

// Slider settings common function to avoid repetition
for ($i = 1; $i <= 3; $i++) { // Vertical slider list for 3 slides
    $page->add(new admin_setting_heading("theme_mytheme/slide{$i}_heading_info", "Slide {$i}", ""));

    // Slide Image
    $page->add(new admin_setting_configstoredfile(
        "theme_mytheme/slide{$i}_image",
        "Slide {$i} Image",
        "Upload background image for slide {$i}",
        "slide{$i}_image"
    ));

    // Slide Title
    $page->add(new admin_setting_configtext(
        "theme_mytheme/slide{$i}_title",
        "Slide {$i} Title",
        "Main title for slide {$i}",
        "Learning is Easy with Us"
    ));

    // Slide Description (HTML Editor enabled)
    $page->add(new admin_setting_confightmleditor(
        "theme_mytheme/slide{$i}_desc",
        "Slide {$i} Description",
        "Small description for slide {$i}",
        "<p>Explore our wide range of courses.</p>"
    ));

    // Slide Button Text
    $page->add(new admin_setting_configtext(
        "theme_mytheme/slide{$i}_btntext",
        "Slide {$i} Button Text",
        "Text for the button",
        "Enroll Now"
    ));

    // Slide Button Link
    $page->add(new admin_setting_configtext(
        "theme_mytheme/slide{$i}_btnlink",
        "Slide {$i} Link",
        "URL for the button (e.g. # or /course/index.php)",
        "#"
    ));
}

$settings->add($page);