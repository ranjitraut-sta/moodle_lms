<?php 
namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;

class H5P implements LessonModuleInterface {

    protected $cm;
    protected $DB;
    protected $context;
    protected $cmid;

    public function __construct($cm, $DB, $context, $cmid) {
        $this->cm      = $cm;
        $this->DB      = $DB;
        $this->context = $context;
        $this->cmid    = $cmid;
    }

    public function getData(): array {

        // Try h5pactivity
        $h5p = $this->DB->get_record('h5pactivity', ['id' => $this->cm->instance]);

        if ($h5p) {
            $fs    = get_file_storage();
            $files = $fs->get_area_files($this->context->id, 'mod_h5pactivity', 'package', 0, 'id', false);

            $embedurl = null;

            if ($files) {
                $file = reset($files);

                $embedurl = (new \moodle_url('/h5p/embed.php', [
                    'url' => \moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename()
                    )->out(false),
                    'preventredirect' => 1,
                ]))->out(false);
            }

            return [
                'ish5p'    => true,
                'h5pintro' => format_text($h5p->intro, $h5p->introformat),
                'h5purl'   => $embedurl ?: (new \moodle_url('/mod/h5pactivity/view.php', ['id' => $this->cmid]))->out(false)
            ];
        }

        // fallback hvp
        $hvp = $this->DB->get_record('hvp', ['id' => $this->cm->instance]);

        if ($hvp) {
            return [
                'ish5p'    => true,
                'h5pintro' => format_text($hvp->intro, $hvp->introformat),
                'h5purl'   => (new \moodle_url('/mod/hvp/embed.php', ['id' => $this->cmid]))->out(false)
            ];
        }

        return [];
    }
}