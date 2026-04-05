<?php 
namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;

class DefaultModule implements LessonModuleInterface {

    protected $cm;

    public function __construct($cm) {
        $this->cm = $cm;
    }

    public function getData(): array {
        return [
            'isother' => true,
            'otherurl' => $this->cm->url ? $this->cm->url->out(false) : '#',
            'othertype' => $this->cm->modname
        ];
    }
}