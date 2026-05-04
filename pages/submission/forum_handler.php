<?php
define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');

header('Content-Type: application/json');

// Security Check: Sesskey अनिवार्य छ
if (!confirm_sesskey()) {
    echo json_encode(['success' => false, 'message' => 'Invalid Session Key']);
    exit;
}

$action = optional_param('action', 'adddiscussion', PARAM_ALPHA);
$forumid = required_param('forum', PARAM_INT);

try {
    // आधारभूत डेटा लोड गर्ने
    $forum = $DB->get_record('forum', array('id' => $forumid), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $forum->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);

    // Permission चेक
    require_login($course, false, $cm);

    // १. नयाँ Discussion थप्ने (New Topic)
    if ($action === 'adddiscussion') {
        $subject = required_param('subject', PARAM_TEXT);
        $message = required_param('message', PARAM_RAW);

        $discussion = new \stdClass();
        $discussion->course = $course->id;
        $discussion->forum = $forum->id;
        $discussion->name = $subject;
        $discussion->intro = $message; // केही भर्सनमा intro चाहिन्छ
        $discussion->message = $message;
        $discussion->messageformat = FORMAT_HTML;
        $discussion->messagetrust = 0;
        $discussion->mailnow = 0;
        $discussion->groupid = -1;
        $discussion->timestart = 0;
        $discussion->timeend = 0;
        $discussion->itemid = 0; // Draft files छैन भने ०

        $discussionid = forum_add_discussion($discussion, null, null, $USER->id);

        echo json_encode(['success' => true, 'id' => $discussionid, 'type' => 'discussion']);
        exit;
    }

    // २. रिप्लाई थप्ने (Reply to Discussion)
    else if ($action === 'reply') {
        $discussionid = required_param('discussion', PARAM_INT);
        $message = required_param('message', PARAM_RAW);

        // पहिलो पोस्ट (Parent Post) पत्ता लगाउने
        $discussion_rec = $DB->get_record('forum_discussions', ['id' => $discussionid], '*', MUST_EXIST);
        $parentid = $discussion_rec->firstpost;

        $post = new \stdClass();
        $post->discussion = $discussionid;
        $post->parent = $parentid;
        $post->userid = $USER->id;
        $post->subject = "Re: " . $discussion_rec->name;
        $post->message = $message;
        $post->messageformat = FORMAT_HTML;
        $post->messagetrust = 0;
        $post->mailnow = 0;
        $post->attachment = 0;
        $post->itemid = 0; // Attachment नभएकोले ०

        // रिप्लाई पोस्ट गर्ने
        $postid = forum_add_new_post($post, null);

        echo json_encode(['success' => true, 'id' => $postid, 'type' => 'reply']);
        exit;
    } else if ($action === 'delete') {
        $postid = required_param('postid', PARAM_INT);
        $post = $DB->get_record('forum_posts', ['id' => $postid], '*', MUST_EXIST);
        $discussion = $DB->get_record('forum_discussions', ['id' => $post->discussion], '*', MUST_EXIST);

        // Permission Check: आफैले लेखेको हुनुपर्छ वा म्यानेजर हुनुपर्छ
        $canmanage = ($USER->id == $post->userid) || has_capability('mod/forum:manageanydiscussion', $cm->context);

        if ($canmanage) {
            // यदि यो डिस्कसनको पहिलो पोस्ट हो भने पूरै डिस्कसन डिलिट हुन्छ
            if ($discussion->firstpost == $post->id) {
                forum_delete_discussion($discussion, false, $course, $cm, $forum);
            } else {
                forum_delete_post($post, has_capability('mod/forum:deleteanypost', $cm->context), $course, $cm, $forum);
            }
            echo json_encode(['success' => true, 'message' => 'Post deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
        }
        exit;
    }

    // ४. पोस्ट अपडेट गर्ने (Update/Edit)
    else if ($action === 'update') {
        $postid = required_param('postid', PARAM_INT);
        $subject = optional_param('subject', '', PARAM_TEXT);
        $message = required_param('message', PARAM_RAW);

        $post = $DB->get_record('forum_posts', ['id' => $postid], '*', MUST_EXIST);

        // Permission Check
        if ($USER->id != $post->userid && !has_capability('mod/forum:editanypost', $cm->context)) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        $updatepost = new \stdClass();
        $updatepost->id = $postid;
        $updatepost->message = $message;
        if (!empty($subject)) {
            $updatepost->subject = $subject;
            // यदि पहिलो पोस्ट हो भने डिस्कसनको नाम पनि फेर्ने
            $DB->set_field('forum_discussions', 'name', $subject, ['firstpost' => $postid]);
        }
        $updatepost->modified = time();

        $DB->update_record('forum_posts', $updatepost);
        echo json_encode(['success' => true, 'message' => 'Post updated']);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}