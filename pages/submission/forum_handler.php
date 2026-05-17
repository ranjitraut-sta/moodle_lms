<?php
define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../../../config.php');
require_once($CFG->dirroot . '/mod/forum/lib.php');

header('Content-Type: application/json');

// Session check
if (!confirm_sesskey()) {
    echo json_encode(['success' => false, 'message' => 'Invalid Session Key']);
    exit;
}

$action = optional_param('action', 'adddiscussion', PARAM_ALPHA);
$forumid = required_param('forum', PARAM_INT);

try {
    $forum = $DB->get_record('forum', array('id' => $forumid), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $forum->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);

    // Context capability loading rules
    $context = \context_module::instance($cm->id);
    require_login($course, false, $cm);

    // CRITICAL SECURITY ENFORCEMENT FOR SINGLE FORUMS
    if ($action === 'adddiscussion' && $forum->type === 'single') {
        echo json_encode([
            'success' => false,
            'message' => 'Action denied: Single Simple Discussion layouts do not support branch creations.'
        ]);
        exit;
    }

    // 1. Add Discussion Thread
    if ($action === 'adddiscussion') {
        $subject = required_param('subject', PARAM_TEXT);
        $message = required_param('message', PARAM_RAW);

        $discussion = new \stdClass();
        $discussion->course = $course->id;
        $discussion->forum = $forum->id;
        $discussion->name = $subject;
        $discussion->intro = $message;
        $discussion->message = $message;
        $discussion->messageformat = FORMAT_HTML;
        $discussion->messagetrust = 0;
        $discussion->mailnow = 0;
        $discussion->groupid = -1;
        $discussion->timestart = 0;
        $discussion->timeend = 0;
        $discussion->itemid = 0;

        $discussionid = forum_add_discussion($discussion, null, null, $USER->id);

        echo json_encode(['success' => true, 'id' => $discussionid, 'type' => 'discussion']);
        exit;
    }

    // 2. Add Reply Post
    else if ($action === 'reply') {
        $discussionid = required_param('discussion', PARAM_INT);
        $message = required_param('message', PARAM_RAW);

        $discussion_rec = $DB->get_record('forum_discussions', ['id' => $discussionid], '*', MUST_EXIST);

        // Ensure targeting post elements map back explicitly inside this course module workspace
        if ($discussion_rec->forum != $forum->id) {
            echo json_encode(['success' => false, 'message' => 'Parameters configuration matching violation error.']);
            exit;
        }

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
        $post->itemid = 0;

        $postid = forum_add_new_post($post, null);

        echo json_encode(['success' => true, 'id' => $postid, 'type' => 'reply']);
        exit;
    }

    // 3. Delete Post Handler
    else if ($action === 'delete') {
        $postid = required_param('postid', PARAM_INT);
        $post = $DB->get_record('forum_posts', ['id' => $postid], '*', MUST_EXIST);
        $discussion = $DB->get_record('forum_discussions', ['id' => $post->discussion], '*', MUST_EXIST);

        $canmanage = ($USER->id == $post->userid) || has_capability('mod/forum:manageanydiscussion', $context);

        if ($canmanage) {
            if ($discussion->firstpost == $post->id) {
                // Deleting the first post of a single forum is prohibited
                if ($forum->type === 'single') {
                    echo json_encode(['success' => false, 'message' => 'Cannot delete the master description node of single topic forums.']);
                    exit;
                }
                forum_delete_discussion($discussion, false, $course, $cm, $forum);
            } else {
                forum_delete_post($post, has_capability('mod/forum:deleteanypost', $context), $course, $cm, $forum);
            }
            echo json_encode(['success' => true, 'message' => 'Post deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
        }
        exit;
    }

    // 4. Update Post Handler
    else if ($action === 'update') {
        $postid = required_param('postid', PARAM_INT);
        $subject = optional_param('subject', '', PARAM_TEXT);
        $message = required_param('message', PARAM_RAW);

        $post = $DB->get_record('forum_posts', ['id' => $postid], '*', MUST_EXIST);

        if ($USER->id != $post->userid && !has_capability('mod/forum:editanypost', $context)) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            exit;
        }

        $updatepost = new \stdClass();
        $updatepost->id = $postid;
        $updatepost->message = $message;
        if (!empty($subject) && $forum->type !== 'single') {
            $updatepost->subject = $subject;
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