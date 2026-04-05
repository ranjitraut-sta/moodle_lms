<?php 
namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;

class Book implements LessonModuleInterface {

    protected $cm;
    protected $DB;

    public function __construct($cm, $DB) {
        $this->cm = $cm;
        $this->DB = $DB;
    }

    public function getData(): array {

        $book = $this->DB->get_record('book', ['id' => $this->cm->instance]);

        if (!$book) return [];

        $chapters = $this->DB->get_records('book_chapters', ['bookid' => $book->id], 'pagenum ASC');

        $chapterlist = [];
        $i = 1;

        foreach ($chapters as $chapter) {
            if ($chapter->hidden) continue;

            $chapterlist[] = [
                'num'     => $i++,
                'title'   => format_string($chapter->title),
                'content' => format_text($chapter->content, $chapter->contentformat),
            ];
        }

        return [
            'isbook'    => true,
            'bookintro' => format_text($book->intro, $book->introformat),
            'chapters'  => $chapterlist
        ];
    }
}