<?php

namespace theme_mytheme\StudentDashboard;

defined('MOODLE_INTERNAL') || die();

class ProfileData
{
    protected $user;

    public function __construct($user = null)
    {
        global $USER;
        $this->user = $user ?? $USER;
    }

    /**
     * MAIN DASHBOARD DATA
     */
    public function getData(): array
    {
        return [

        ];
    }

  
}