<?php 
namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;

class Url implements LessonModuleInterface {

    protected $cm;
    protected $DB;

    public function __construct($cm, $DB) {
        $this->cm = $cm;
        $this->DB = $DB;
    }

    public function getData(): array {
        $url = $this->DB->get_record('url', ['id' => $this->cm->instance]);

        if (!$url) return [];

        $data = [
            'isurlmod' => true,
            'urlintro' => format_text($url->intro, $url->introformat),
        ];

        $externalurl = $url->externalurl;

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $externalurl, $matches)) {
            $data['isyoutube'] = true;
            $data['youtubeid'] = $matches[1];
        } else {
            $data['isexternalurl'] = true;
            $data['externalurl'] = $externalurl;
        }

        return $data;
    }
}