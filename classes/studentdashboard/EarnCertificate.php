<?php

namespace theme_mytheme\StudentDashboard;

defined('MOODLE_INTERNAL') || die();

class EarnCertificate
{
    protected $user;

    public function __construct($user = null)
    {
        global $USER;
        $this->user = $user ?? $USER;
    }

    public function getData(): array
    {
        $certificates = $this->getCertificates();

        // Summary Counts
        $total = count($certificates);

        return [
            'certificates' => $certificates,
            'total_certs_count' => $total,
        ];
    }

    public function getCertificates(): array
    {
        global $DB, $CFG;

        $sql = "SELECT ci.id,
                   ci.timecreated,
                   ci.code,
                   c.name AS certificate_name,
                   co.fullname AS course_name,
                   co.id AS courseid,
                   cm.id AS cmid
            FROM {customcert_issues} ci
            JOIN {customcert} c ON c.id = ci.customcertid
            JOIN {course} co ON co.id = c.course
            JOIN {modules} m ON m.name = 'customcert'
            JOIN {course_modules} cm ON cm.course = co.id AND cm.instance = c.id AND cm.module = m.id
            WHERE ci.userid = ?
            ORDER BY ci.timecreated DESC";

        $records = $DB->get_records_sql($sql, [$this->user->id]);

        $prepared_certs = [];
        foreach ($records as $cert) {
            $obj = new \stdClass();
            $obj->id = $cert->id;
            $obj->course_name = $cert->course_name;
            $obj->cert_name = $cert->certificate_name;

            // Moodle date format: 10 Oct, 2025
            $obj->issued_date = userdate($cert->timecreated, get_string('strftimedate', 'langconfig'));
            $obj->year = date('Y', $cert->timecreated);

            // Download URL (mod/customcert link)
            $obj->download_url = (new \moodle_url('/mod/customcert/view.php', [
                'id' => $cert->cmid,
                'downloadown' => 1
            ]))->out(false);

            // Thumbnail - yadi custom xaina vane default theme image
            $obj->thumb_url = $CFG->wwwroot . '/theme/mytheme/pix/certificate_thumb.png';

            $prepared_certs[] = $obj;
        }

        return $prepared_certs;
    }

}