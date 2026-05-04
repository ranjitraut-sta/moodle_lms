<?php

namespace theme_mytheme\Report;

defined('MOODLE_INTERNAL') || die();

class UserPrepare
{
    /**
     * Main function to get report data
     */
    public function getData(): array
    {
        return [
            'users' => $this->getUserList(),
        ];
    }

    /**
     * Get user list from Moodle DB
     */
    protected function getUserList(): array
    {
        global $DB;

        $fields = \core_user\fields::for_name()->get_sql('u')->selects;

        $sql = "SELECT 
                u.id,
                u.username,
                u.email,
                u.lastaccess,
                u.timecreated
                $fields
            FROM {user} u
            WHERE u.deleted = 0 
                AND u.suspended = 0
                AND u.id > 2
            ORDER BY u.firstname ASC";

        $users = $DB->get_records_sql($sql);

        $result = [];

        foreach ($users as $user) {

            $profile = profile_user_record($user->id);

            $result[] = [
                'id' => $user->id,
                'fullname' => fullname($user),
                'username' => $user->username,
                'email' => $user->email,

                // basic profile
                'phone' => $profile->phone ?? 'N/A',
                'pan_no' => $profile->pan_no ?? 'N/A',
                'nid_no' => $profile->nid_no ?? 'N/A',

                // 🏠 permanent address (CORRECT KEYS)
                'province' => $profile->province ?? 'N/A',
                'district' => $profile->district ?? 'N/A',
                'municipality' => $profile->municipality ?? 'N/A',
                'ward' => $profile->ward ?? 'N/A',
                'tole' => $profile->tole ?? 'N/A',

                // 🏠 temporary address
                'temp_province_id' => $profile->temp_province_id ?? 'N/A',
                'temp_district_id' => $profile->temp_district_id ?? 'N/A',
                'temp_municipality_id' => $profile->temp_municipality_id ?? 'N/A',
                'temp_ward' => $profile->temp_ward ?? 'N/A',
                'temp_tole' => $profile->temp_tole ?? 'N/A',

                // extra fields
                'gender' => $profile->gender ?? 'N/A',
                'ethnicity' => $profile->ethnicity ?? 'N/A',
                'organization_name' => $profile->organization_name ?? 'N/A',
                'qualification' => $profile->qualification ?? 'N/A',
                'designation' => $profile->designation ?? 'N/A',
                'expertise' => $profile->expertise ?? 'N/A',
                'years_experience' => $profile->years_experience ?? 'N/A',

                // activity
                'last_access' => $user->lastaccess
                    ? userdate($user->lastaccess)
                    : 'Never',

                'joined_on' => userdate($user->timecreated),
            ];
        }

        return $result;
    }
}