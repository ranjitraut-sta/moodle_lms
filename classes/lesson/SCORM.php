<?php

namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;

class SCORM implements LessonModuleInterface
{

    protected $cm;
    protected $DB;
    protected $cmid;

    public function __construct($cm, $DB, $cmid)
    {
        $this->cm   = $cm;
        $this->DB   = $DB;
        $this->cmid = $cmid;
    }

    public function getData(): array
    {
        global $USER, $CFG;

        $scorm = $this->DB->get_record('scorm', ['id' => $this->cm->instance]);

        if (!$scorm) {
            return [];
        }

        // Force hide Moodle UI components in the database to ensure player.php hides them natively
        $update = false;
        if (isset($scorm->hidetoc) && $scorm->hidetoc != 3) {
            $scorm->hidetoc = 3; // 3 means Disabled
            $update = true;
        }

        if (isset($scorm->hidebrowse) && $scorm->hidebrowse != 1) {
            $scorm->hidebrowse = 1; // 1 means Yes (disable preview)
            $update = true;
        }

        if ($update) {
            $this->DB->update_record('scorm', $scorm);
        }

        require_once($CFG->dirroot . '/mod/scorm/locallib.php');

        $attempt = scorm_get_last_attempt($scorm->id, $USER->id);
        $toc_data = scorm_get_toc_object($USER, $scorm, '', '', 'normal', $attempt);

        $scoid = !empty($toc_data['scoid']) ? $toc_data['scoid'] : $scorm->launch;

        // Flatten the TOC tree for our custom sidebar
        $flatten_scoes = function ($scoes) use (&$flatten_scoes, $toc_data, $scoid) {
            $result = [];
            foreach ($scoes as $sco) {
                if (isset($sco->isvisible) && $sco->isvisible === 'false') {
                    continue;
                }

                if ($sco->scormtype == 'sco' && !empty($sco->launch)) {
                    $status = 'notattempted';
                    if (isset($toc_data['usertracks'][$sco->identifier])) {
                        $status = $toc_data['usertracks'][$sco->identifier]->status;
                        if (!$status) {
                            $status = 'notattempted';
                        }
                    }

                    // FontAwesome classes for status
                    $icon = 'fa-circle-o';
                    $status_class = 'status-notattempted';
                    if ($status === 'passed' || $status === 'completed') {
                        $icon = 'fa-check-circle text-success';
                        $status_class = 'status-completed';
                    } elseif ($status === 'failed') {
                        $icon = 'fa-times-circle text-danger';
                        $status_class = 'status-failed';
                    } elseif ($status === 'incomplete' || $status === 'browsed' || $status === 'suspend') {
                        $icon = 'fa-play-circle-o text-primary';
                        $status_class = 'status-incomplete';
                    }

                    $result[] = [
                        'id' => $sco->id,
                        'title' => format_string($sco->title),
                        'status' => $status,
                        'status_class' => $status_class,
                        'icon' => $icon,
                        'is_active' => ($sco->id == $scoid),
                        'url' => (new \moodle_url('/mod/scorm/player.php', [
                            'cm'      => $this->cmid,
                            'scoid'   => $sco->id,
                            'display' => 'popup',   // Keep popup to strip layout
                            'popup'   => 1,         // CRITICAL: Tells Moodle to output ONLY player content, no head/title
                            'mode'    => 'normal'
                        ]))->out(false)
                    ];
                }

                if (!empty($sco->children)) {
                    $result = array_merge($result, $flatten_scoes($sco->children));
                }
            }
            return $result;
        };

        $formatted_toc = $flatten_scoes($toc_data['scoes']);

        // Calculate progress percentage
        $completed_count = 0;
        foreach ($formatted_toc as $item) {
            if ($item['status'] === 'completed' || $item['status'] === 'passed') {
                $completed_count++;
            }
        }
        $progress = count($formatted_toc) > 0 ? round(($completed_count / count($formatted_toc)) * 100) : 0;

        // Default UI visibility settings for Mustache template
        $show_title = false;
        $show_scorm_mode = false;

        if ($scoid) {
            return [
                'isscorm'         => true,
                'scormurl'        => (new \moodle_url('/mod/scorm/player.php', [
                    'cm'          => $this->cmid,
                    'scoid'       => $scoid,
                    'display'     => 'popup', // Keep popup to strip headers
                    'popup'       => 1,       // CRITICAL: Remaps Moodle core to hide titles/review mode natively
                    'mode'        => 'normal'
                ]))->out(false),
                'toc'             => $formatted_toc,
                'progress'        => $progress,
                'scorm_name'      => format_string($scorm->name),
                'show_title'      => $show_title,
                'show_scorm_mode' => $show_scorm_mode
            ];
        }

        return [
            'isscormlink'     => true,
            'scormurl'        => (new \moodle_url('/mod/scorm/view.php', [
                'id' => $this->cmid
            ]))->out(false),
            'show_title'      => $show_title,
            'show_scorm_mode' => $show_scorm_mode
        ];
    }
}