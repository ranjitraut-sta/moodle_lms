<?php
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettingmytheme', get_string('configtitle', 'theme_mytheme'));
    
    // Include all settings pages
    include(dirname(__FILE__) . '/settings/general.php');
    include(dirname(__FILE__) . '/settings/menu.php');
    include(dirname(__FILE__) . '/settings/aboutus.php');
    include(dirname(__FILE__) . '/settings/jumbotron.php');
    include(dirname(__FILE__) . '/settings/footer.php');
    include(dirname(__FILE__) . '/settings/homeslider.php');
}