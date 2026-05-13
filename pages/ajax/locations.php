<?php
require_once('../../../../config.php');

global $DB;

$action = required_param('action', PARAM_ALPHA);

header('Content-Type: application/json');

if ($action === 'districts') {
    // optional_param use garne, jasle province_id nabhaye pani error didaina
    $provinceid = optional_param('province_id', 0, PARAM_INT);

    if ($provinceid > 0) {
        // Yadi province_id chha bhane (Permanent/Temporary address ko lagi)
        $districts = $DB->get_records('local_location_districts', [
            'province_id' => $provinceid
        ]);
    } else {
        // Yadi province_id chhaina bhane (Citizenship Issued District ko lagi - Sabai district dine)
        $districts = $DB->get_records('local_location_districts', [], 'name ASC');
    }

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