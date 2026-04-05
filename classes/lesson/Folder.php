<?php 
namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;

class Folder implements LessonModuleInterface {

    protected $cm;
    protected $DB;
    protected $context;

    public function __construct($cm, $DB, $context) {
        $this->cm = $cm;
        $this->DB = $DB;
        $this->context = $context;
    }

    public function getData(): array {

        $folder = $this->DB->get_record('folder', ['id' => $this->cm->instance]);

        if (!$folder) return [];

        $fs    = get_file_storage();
        $files = $fs->get_area_files($this->context->id, 'mod_folder', 'content', 0, 'filename ASC', false);

        $filelist = [];

        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));

            $fileurl = \moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            )->out(false);

            $filelist[] = [
                'filename' => $file->get_filename(),
                'fileurl'  => $fileurl,
                'ispdf'    => $ext === 'pdf',
                'isimage'  => in_array($ext, ['jpg','jpeg','png','gif','webp']),
                'isvideo'  => in_array($ext, ['mp4','webm']),
                'isaudio'  => $ext === 'mp3',
                'isother'  => !in_array($ext, ['pdf','jpg','jpeg','png','gif','webp','mp4','webm','mp3']),
            ];
        }

        return [
            'isfolder'    => true,
            'folderintro' => format_text($folder->intro, $folder->introformat),
            'folderfiles' => $filelist
        ];
    }
}