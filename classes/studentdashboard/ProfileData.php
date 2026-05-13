<?php

namespace theme_mytheme\StudentDashboard;

defined('MOODLE_INTERNAL') || die();

class ProfileData
{
    protected $user;

    public function __construct($user = null)
    {
        global $USER;
        // User parameter pathako chaina vane logged-in user line
        $this->user = $user ?? $USER;
    }

    /**
     * MAIN DASHBOARD DATA FOR SINGLE USER
     */
    public function getData(): array
    {
        global $DB, $OUTPUT;

        // Custom profile fields haru fetch garna
        // Moodle ko native function jasle 'user_info_data' table bata data tanchha
        $profile = profile_user_record($this->user->id);

        return [
            'id' => $this->user->id,
            'fullname' => fullname($this->user),
            'firstname' => $this->user->firstname,
            'lastname' => $this->user->lastname,
            'email' => $this->user->email,
            'username' => $this->user->username,
            'profile_pix' => $this->get_user_picture_url(),

            // basic info
            'phone' => $profile->phone ?? 'N/A',
            'pan_no' => $profile->pan_no ?? 'N/A',
            'nid_no' => $profile->nid_no ?? 'N/A',

            // Permanent Address
            'province' => $profile->province ?? 'N/A',
            'district' => $profile->district ?? 'N/A',
            'municipality' => $profile->municipality ?? 'N/A',
            'ward' => $profile->ward ?? 'N/A',
            'tole' => $profile->tole ?? 'N/A',

            // Extra Profile Fields
            'gender' => $profile->gender ?? 'N/A',
            'ethnicity' => $profile->ethnicity ?? 'N/A',
            'organization_name' => $profile->organization_name ?? 'N/A',
            'qualification' => $profile->qualification ?? 'N/A',
            'designation' => $profile->designation ?? 'N/A',
            'expertise' => $profile->expertise ?? 'N/A',
            'years_experience' => $profile->years_experience ?? 'N/A',

            // System info
            'last_access' => $this->user->lastaccess ? userdate($this->user->lastaccess) : 'Never',
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
        return $OUTPUT->user_picture($this->user, array('size' => 100, 'link' => false));
    }

    /**
     * Get actual Image URL of the user
     */
    protected function get_user_picture_url()
    {
        global $PAGE;
        // User picture URL generate garne standard Moodle way
        $userpicture = new \user_picture($this->user);
        $userpicture->size = 150; // Size as needed
        return $userpicture->get_url($PAGE)->out(false);
    }
}