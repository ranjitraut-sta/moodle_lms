<?php 
namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;

class Forum implements LessonModuleInterface {

    protected $cm;
    protected $DB;
    protected $cmid;

    public function __construct($cm, $DB, $cmid) {
        $this->cm   = $cm;
        $this->DB   = $DB;
        $this->cmid = $cmid;
    }

    public function getData(): array {

        $forum = $this->DB->get_record('forum', ['id' => $this->cm->instance]);

        if (!$forum) return [];

        return [
            'isforum'    => true,
            'forumintro' => format_text($forum->intro, $forum->introformat),
            'forumurl'   => (new \moodle_url('/mod/forum/view.php', ['id' => $this->cmid]))->out(false)
        ];
    }
}