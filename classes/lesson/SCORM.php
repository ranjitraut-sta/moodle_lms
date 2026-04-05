<?php
namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;

class SCORM implements LessonModuleInterface {

    protected $cm;
    protected $DB;
    protected $cmid;

    public function __construct($cm, $DB, $cmid) {
        $this->cm   = $cm;
        $this->DB   = $DB;
        $this->cmid = $cmid;
    }

    public function getData(): array {

        $scorm = $this->DB->get_record('scorm', ['id' => $this->cm->instance]);

        if (!$scorm) return [];

        $sco = $this->DB->get_record_select(
            'scorm_scoes',
            'scorm = ? AND scormtype = ?',
            [$scorm->id, 'sco'],
            'id',
            IGNORE_MULTIPLE
        );

        if ($sco) {
            return [
                'isscorm'  => true,
                'scormurl' => (new \moodle_url('/mod/scorm/player.php', [
                    'cm' => $this->cmid,
                    'scoid' => $sco->id,
                    'display' => 'popup'
                ]))->out(false)
            ];
        }

        return [
            'isscormlink' => true,
            'scormurl' => (new \moodle_url('/mod/scorm/view.php', [
                'id' => $this->cmid
            ]))->out(false)
        ];
    }
}