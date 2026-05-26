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

    public function getData(): array
    {
        global $DB;

        $profile = profile_user_record($this->user->id);

        // =========================
        // ADDRESS LOOKUPS
        // =========================

        $province = null;
        $district = null;
        $municipality = null;

        if (!empty($profile->province_id)) {
            $province = $DB->get_field(
                'local_location_provinces',
                'name',
                ['id' => $profile->province_id]
            );
        }

        if (!empty($profile->district_id)) {
            $district = $DB->get_field(
                'local_location_districts',
                'name',
                ['id' => $profile->district_id]
            );
        }

        if (!empty($profile->municipality_id)) {
            $municipality = $DB->get_field(
                'local_location_municipalities',
                'name',
                ['id' => $profile->municipality_id]
            );
        }

        // =========================
        // ADDRESS LOOKUPS
        // =========================
        $province = null;
        $district = null;
        $municipality = null;

        if (!empty($profile->temp_province_id)) {
            $province = $DB->get_field(
                'local_location_provinces',
                'name',
                ['id' => $profile->temp_province_id]
            );
        }

        if (!empty($profile->temp_district_id)) {
            $district = $DB->get_field(
                'local_location_districts',
                'name',
                ['id' => $profile->temp_district_id]
            );
        }

        if (!empty($profile->temp_municipality_id)) {
            $municipality = $DB->get_field(
                'local_location_municipalities',
                'name',
                ['id' => $profile->temp_municipality_id]
            );
        }

        return [

            'id' => $this->user->id,

            'fullname' => fullname($this->user),

            'firstname' => $this->user->firstname,
            'lastname' => $this->user->lastname,

            'email' => $this->user->email,
            'username' => $this->user->username,

            'profile_pix' => $this->get_user_picture_url(),

            // =========================
            // BASIC INFO
            // =========================

            'phone_number' => $profile->phone_number ?? 'N/A',

            'pan_no' => $profile->pan_no ?? 'N/A',
            'nid_no' => $profile->nid_no ?? 'N/A',

            // =========================
            // ADDRESS
            // =========================

            'province' => $province ?? 'N/A',
            'district' => $district ?? 'N/A',
            'municipality' => $municipality ?? 'N/A',
            'temp_province_id' => $profile->temp_province ?? 'N/A',
            'temp_district_id' => $profile->temp_district ?? 'N/A',
            'temp_municipality_id' => $profile->temp_municipality ?? 'N/A',
            'temp_ward' => $profile->temp_ward ?? 'N/A',
            'temp_tole' => $profile->temp_tole ?? 'N/A',

            'ward' => $profile->ward ?? 'N/A',
            'tole' => $profile->tole ?? 'N/A',

            // =========================
            // EXTRA INFO
            // =========================

            'gender' => $profile->gender ?? 'N/A',
            'ethnicity' => $profile->ethnicity ?? 'N/A',

            'organization_name' => $profile->organization_name ?? 'N/A',

            'qualification' => $profile->qualification ?? 'N/A',

            'designation' => $profile->designation ?? 'N/A',

            'expertise' => $profile->expertise ?? 'N/A',

            'years_experience' => $profile->years_experience ?? 'N/A',

            // =========================
            // SYSTEM INFO
            // =========================

            'last_access' => $this->user->lastaccess
                ? userdate($this->user->lastaccess)
                : 'Never',

            'joined_on' => userdate($this->user->timecreated),

            'city' => $this->user->city,
            'country' => $this->user->country,

            'user_fullname' => fullname($this->user),

            'user_firstname' => $this->user->firstname,

            'user_profile_pix' => $this->get_user_picture(),
        ];
    }

    protected function get_user_picture()
    {
        global $OUTPUT;

        return $OUTPUT->user_picture(
            $this->user,
            [
                'size' => 100,
                'link' => false
            ]
        );
    }

    protected function get_user_picture_url()
    {
        global $PAGE;

        $userpicture = new \user_picture($this->user);

        $userpicture->size = 150;

        return $userpicture->get_url($PAGE)->out(false);
    }
}