<?php

namespace theme_mytheme\lesson;


use theme_mytheme\lesson\contracts\LessonModuleInterface;

class Page implements LessonModuleInterface
{

    protected $cm;
    protected $DB;

    public function __construct($cm, $DB)
    {
        $this->cm = $cm;
        $this->DB = $DB;
    }

    public function getData(): array
    {
        global $CFG;

        $page = $this->DB->get_record('page', ['id' => $this->cm->instance]);

        $content = '';

        if ($page) {

            $context = \context_module::instance($this->cm->id);

            $content = file_rewrite_pluginfile_urls(
                $page->content,
                'pluginfile.php',
                $context->id,
                'mod_page',
                'content',
                0
            );

            $content = format_text(
                $content,
                $page->contentformat,
                ['context' => $context]
            );
        }

        return [
            'ispage' => true,
            'pagecontent' => $content,
        ];
    }
}