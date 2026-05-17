<?php

namespace theme_mytheme;

use core\hook\output\before_footer_html_generation;
use moodle_url;

class hooks {
    /**
     * Callback for before_footer_html_generation hook.
     * Redirect user logic: Logged in user lai /my/ bata homepage ma dhalkiuna.
     *
     * @param before_footer_html_generation $hook
     */
    public static function before_footer_html_generation(before_footer_html_generation $hook): void {
        global $PAGE;

        // Check if user is logged in and specifically on /my/index.php or /my/
        if (isloggedin() && !isguestuser()) {
            // Moodle le default dashboard load garna khojda yo catch hunchha
            if ($PAGE->pagetype === 'my-index' || strpos($_SERVER['REQUEST_URI'], '/my/') !== false) {
                // Yadi user manually dashboard ma jana khojeko hoina (referral chaina) bhane root ma pathaune
                if (!isset($_GET['redirect'])) {
                    redirect(new moodle_url('/?redirect=0'));
                }
            }
        }
    }
}
