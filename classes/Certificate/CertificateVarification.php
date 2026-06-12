<?php

namespace theme_mytheme\Certificate;

defined('MOODLE_INTERNAL') || die();

class CertificateVarification
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