<?php

require_once(__DIR__ . '/../../../config.php');

global $DB;

require_login();

$fs = get_file_storage();

$courses = $DB->get_records('course', ['visible' => 1], 'id ASC');

echo "<h2 style='color:blue'>Course Image Diagnostic Report</h2>";

foreach ($courses as $course) {

    if ($course->id == SITEID) {
        continue;
    }

    echo "<div style='border:1px solid #ccc; padding:10px; margin-bottom:10px;'>";

    echo "<strong>Course ID:</strong> {$course->id}<br>";
    echo "<strong>Course Name:</strong> {$course->fullname}<br>";

    $courseobj = new core_course_list_element($course);
    $files = $courseobj->get_course_overviewfiles();

    echo "<strong>DB Overview Files:</strong> " . count($files) . "<br>";

    if (empty($files)) {
        echo "<span style='color:red'>❌ NO COURSE IMAGE IN DB</span><br>";
        echo "</div>";
        continue;
    }

    foreach ($files as $file) {

        echo "File Name: " . $file->get_filename() . "<br>";
        echo "Is Image: " . ($file->is_valid_image() ? 'YES' : 'NO') . "<br>";

        // file storage check
        $storedfile = $fs->get_file(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        );

        if ($storedfile) {
            echo "<span style='color:green'>✔ File EXISTS in storage</span><br>";
        } else {
            echo "<span style='color:red'>❌ Missing in file storage</span><br>";
        }

        // pluginfile URL
        $url = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        )->out(false);

        echo "<strong>URL:</strong> <a href='{$url}' target='_blank'>{$url}</a><br>";

        echo "<img src='{$url}' width='150' style='margin-top:10px;' onerror=\"this.style.border='2px solid red'\" /><br>";

        break;
    }

    echo "</div>";
}