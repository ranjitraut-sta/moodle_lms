<?php 

namespace theme_mytheme\lesson;


use theme_mytheme\lesson\contracts\LessonModuleInterface;

class Page implements LessonModuleInterface {

    protected $cm;
    protected $DB;

    public function __construct($cm, $DB) {
        $this->cm = $cm;
        $this->DB = $DB;
    }

    public function getData(): array {
        $page = $this->DB->get_record('page', ['id' => $this->cm->instance]);

        return [
            'ispage' => true,
            'pagecontent' => $page ? format_text($page->content, $page->contentformat) : ''
        ];
    }
}