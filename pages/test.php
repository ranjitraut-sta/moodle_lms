<?php

require_once(__DIR__ . '/../../../config.php');

global $DB, $USER;

// =====================
// LOGIN USER FETCH
// =====================
$user = $DB->get_record('user', ['id' => $USER->id]);

echo "<h2 style='color:green'>Logged User Info</h2>";

echo "<div style='margin-bottom:20px; padding:10px; border:1px solid #ccc;'>";

echo "Username: " . $user->username . "<br>";
echo "Full Name: " . $user->firstname . " " . $user->lastname . "<br>";
echo "Email: " . $user->email . "<br>";
echo "City: " . $user->city . "<br>";
echo "Country: " . $user->country . "<br>";

echo "</div>";

