<?php
namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;

class CustomCert implements LessonModuleInterface {

    protected $cm;
    protected $DB;
    protected $cmid;

    public function __construct($cm, $DB, $cmid) {
        $this->cm   = $cm;
        $this->DB   = $DB;
        $this->cmid = $cmid;
    }

    public function getData(): array {
        global $USER;

        $cert = $this->DB->get_record('customcert', ['id' => $this->cm->instance]);
        if (!$cert) return [];

        $issues = $this->DB->get_records('customcert_issues', [
            'userid' => $USER->id,
            'customcertid' => $cert->id
        ]);

        if (!$issues) return [];

        $data = [];
        foreach ($issues as $issue) {
            // Generate PDF URL using view.php (on-demand)
            $url = new \moodle_url('/mod/customcert/view.php', [
                'id' => $this->cmid,
                'downloadown' => 1
            ]);
            $data[] = $url->out(false);
        }

        return [
            'iscustomcert' => true,
            'certpdfurls' => $data // multiple certificates
        ];
    }

    public static function debug($var) {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
    }

}