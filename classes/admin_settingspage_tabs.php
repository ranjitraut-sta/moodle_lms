<?php
defined('MOODLE_INTERNAL') || die();

class theme_mytheme_admin_settingspage_tabs extends admin_settingpage {
    protected $tabs = [];

    public function __construct($name, $heading) {
        $this->tabs = [];
        parent::__construct($name, $heading);
    }

public function add($tab) {
    if ($tab instanceof admin_settingpage) {
        foreach ($tab->settings as $setting) {
            $this->settings->{$setting->name} = $setting;
        }
        $this->tabs[] = $tab;
        return true;
    }
    // default behavior for other tab-like objects
    if (method_exists($tab, 'get_settings')) {
        foreach ($tab->get_settings() as $setting) {
            $this->settings->{$setting->name} = $setting;
        }
        $this->tabs[] = $tab;
        return true;
    }
    return false;
}

    public function output_html($data, $query = '') {
        global $OUTPUT, $PAGE;

        $activetab = optional_param('activetab', '', PARAM_TEXT);
        $context = ['tabs' => []];

        if (empty($this->tabs)) {
            return '';
        }

        foreach ($this->tabs as $tab) {
            $active = false;
            if (empty($activetab) && empty($context['tabs'])) {
                $active = true;
            }
            if ($activetab === $tab->name) {
                $active = true;
            }

            $context['tabs'][] = [
                'name' => $tab->name,
                'displayname' => $tab->visiblename,
                'html' => $tab->output_html($data[$tab->name] ?? null),
                'active' => $active,
            ];
        }

        if (empty($activetab)) {
            $context['tabs'][0]['active'] = true;
        }

        return $OUTPUT->render_from_template('theme_mytheme/admin_setting_tabs', $context);
    }
}
