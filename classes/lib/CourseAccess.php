<?php

namespace theme_mytheme\lib;

defined('MOODLE_INTERNAL') || die();
use theme_config; // 🔥 theme_config क्लास प्रयोग गर्नका लागि

class CourseAccess
{
    /**
     * Check user course access state
     */
    public static function checkUserCourseAccess(int $courseid, int $userid = null): array
    {
        global $USER, $DB;

        $userid = $userid ?? $USER->id;

        // 1. Enrollment check
        $enrolment = $DB->get_record_sql("
            SELECT ue.id, ue.timestart
            FROM {user_enrolments} ue
            JOIN {enrol} e ON e.id = ue.enrolid
            WHERE ue.userid = :userid
              AND e.courseid = :courseid
            ORDER BY ue.timestart DESC
            LIMIT 1
        ", [
            'userid' => $userid,
            'courseid' => $courseid
        ]);

        if (!$enrolment) {
            return [
                'status' => 'not_enrolled',
                'message' => 'You are not enrolled in this course'
            ];
        }

        $timestart = (int) $enrolment->timestart;

        // 2. Course validity (best: custom field later)
        $validDays = self::getCourseValidityDays($courseid);

        $expiryTime = $timestart + ($validDays * DAYSECS);
        $now = time();

        $isExpired = $now > $expiryTime;
        $daysLeft = (int) floor(($expiryTime - $now) / DAYSECS);

        // 3. Completion check (optional but important)
        $completed = $DB->record_exists('course_completions', [
            'userid' => $userid,
            'course' => $courseid,
        ]);

        // 4. Expired state
        if ($isExpired) {
            return [
                'status' => 'expired',
                'message' => 'Course access expired. Please restart the course.',
                'action' => 'reset_course',
                'days_left' => 0,

                // UI helpers
                'is_expired' => true,
                'is_active' => false,
                'is_completed' => $completed
            ];
        }

        // 5. Active state
        return [
            'status' => 'active',
            'message' => 'You can continue learning',
            'days_left' => $daysLeft,

            // UI helpers
            'is_expired' => false,
            'is_active' => true,
            'is_completed' => $completed
        ];
    }

    /**
     * Course validity (future-ready)
     */
    private static function getCourseValidityDays(int $courseid): int
    {
        // 🔥 थिमको नाम 'mytheme' राखेर त्यसको कन्फिगरेसन लोड गर्ने
        $themeconfig = theme_config::load('mytheme');

        // config.php मा 'courseCompleteInBetween' सेट छ कि छैन चेक गर्ने
        if (!empty($themeconfig->courseCompleteInBetween)) {
            return (int) $themeconfig->courseCompleteInBetween; // सेट छ भने १ (वा जे छ त्यो) दिन्छ
        }

        return 1; // यदि कुनै कारणले भेटिएन भने Default १ दिन सुरक्षित राख्ने
    }
}