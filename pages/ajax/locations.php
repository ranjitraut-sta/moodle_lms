<?php
require_once('../../../../config.php');

global $DB;

$action = required_param('action', PARAM_ALPHA);

header('Content-Type: application/json');

if ($action === 'districts') {

    $provinceid = required_param('province_id', PARAM_INT);

    $districts = $DB->get_records('local_location_districts', [
        'province_id' => $provinceid
    ]);

    echo json_encode(array_values($districts));
    exit;
}

if ($action === 'municipalities') {

    $districtid = required_param('district_id', PARAM_INT);

    $munis = $DB->get_records('local_location_municipalities', [
        'district_id' => $districtid
    ]);

    echo json_encode(array_values($munis));
    exit;
}