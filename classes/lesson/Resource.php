<?Php 
namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;

class Resource implements LessonModuleInterface {

    protected $cm;
    protected $DB;
    protected $context;

    public function __construct($cm, $DB, $context) {
        $this->cm = $cm;
        $this->DB = $DB;
        $this->context = $context;
    }

    public function getData(): array {
        $resource = $this->DB->get_record('resource', ['id' => $this->cm->instance]);

        $data = [
            'isresource' => true,
            'resourceintro' => $resource ? format_text($resource->intro, $resource->introformat) : ''
        ];

        $fs    = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false);

        if ($files) {
            $file = reset($files);

            $fileurl = \moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            )->out(false);

            $ext = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));

            $data['filename'] = $file->get_filename();
            $data['fileurl']  = $fileurl;

            if ($ext === 'pdf') $data['ispdf'] = true;
            elseif (in_array($ext, ['jpg','jpeg','png','gif','webp'])) $data['isimage'] = true;
            elseif (in_array($ext, ['mp4','webm'])) $data['isvideo'] = true;
            elseif ($ext === 'mp3') $data['isaudio'] = true;
            else $data['isdownload'] = true;
        }

        return $data;
    }
}