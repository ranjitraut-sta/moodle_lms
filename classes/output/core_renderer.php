<?php
namespace theme_mytheme\output;

class core_renderer extends \theme_boost\output\core_renderer {

    public function firstview_fakeblocks() {
        return false;
    }

    public function initial_page_setup() {
        global $USER, $CFG;
        parent::initial_page_setup();

        // Student login redirect to dashboard - /my/ page
        if ($this->page->url->compare(new moodle_url('/my'))) {
            $context = \context_system::instance();
            if (user_has_role_assignment($USER->id, 5, $context->id)) { // Student role ID 5
                redirect(new moodle_url('/theme/mytheme/layout/dashboard.php'));
            }
        }
    }
}
?>
