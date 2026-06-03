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
        $page = $this->DB->get_record('page', [
            'id' => $this->cm->instance
        ]);

        $content = '';

        if ($page) {

            $context = \context_module::instance($this->cm->id);

            // Replace @@PLUGINFILE@@ URLs
            $content = file_rewrite_pluginfile_urls(
                $page->content,
                'pluginfile.php',
                $context->id,
                'mod_page',
                'content',
                0
            );

            // Moodle formatting
            $content = format_text(
                $content,
                $page->contentformat,
                ['context' => $context]
            );

            // Convert PDF links into iframe viewers
            $content = preg_replace_callback(
                '/<a[^>]+href=["\']([^"\']+\.pdf(?:\?[^"\']*)?)["\'][^>]*>.*?<\/a>/is',
                function ($matches) {

                    $pdfurl = html_entity_decode($matches[1]);

                    // Remove forced download if present
                    $pdfurl = preg_replace(
                        '/([?&])forcedownload=1/',
                        '$1forcedownload=0',
                        $pdfurl
                    );

                    if (strpos($pdfurl, 'forcedownload=') === false) {
                        $pdfurl .= (strpos($pdfurl, '?') !== false ? '&' : '?')
                            . 'forcedownload=0';
                    }

                    return '
                    <div class="pdf-viewer-wrapper" style="margin:20px 0;">
                        <iframe
                            src="' . $pdfurl . '"
                            width="100%"
                            height="800"
                            style="border:1px solid #ddd;border-radius:8px;">
                        </iframe>
                    </div>';
                },
                $content
            );
        }

        return [
            'ispage' => true,
            'pagecontent' => $content,
        ];
    }
}