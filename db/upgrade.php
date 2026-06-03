<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_theme_mytheme_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026060304) {

        $table = new xmldb_table('course_attempts');

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026060305, 'theme', 'mytheme');
    }

    return true;
}