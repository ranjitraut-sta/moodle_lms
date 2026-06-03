<?php

require_once('../../../config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

global $DB;

echo "<h3>Fixing Moodle Upgrade Lock...</h3>";

try {
    $exists = $DB->record_exists('config', ['name' => 'upgraderunning']);

    if ($exists) {
        $DB->delete_records('config', ['name' => 'upgraderunning']);
        echo "<p style='color:green;'>✔ upgraderunning removed successfully</p>";
    } else {
        echo "<p style='color:blue;'>ℹ upgraderunning not found</p>";
    }

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<a href='" . $CFG->wwwroot . "/admin/index.php'>Go to Admin</a>";