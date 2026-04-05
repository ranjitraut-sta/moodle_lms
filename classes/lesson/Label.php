<?Php 
namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;

class Label implements LessonModuleInterface {

    protected $cm;
    protected $DB;

    public function __construct($cm, $DB) {
        $this->cm = $cm;
        $this->DB = $DB;
    }

    public function getData(): array {

        $label = $this->DB->get_record('label', ['id' => $this->cm->instance]);

        return [
            'islabel'      => true,
            'labelcontent' => $label ? format_text($label->intro, $label->introformat) : ''
        ];
    }
}