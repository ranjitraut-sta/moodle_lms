<?php
namespace theme_mytheme\lesson;

use theme_mytheme\lesson\contracts\LessonModuleInterface;

class Forum implements LessonModuleInterface
{

    protected $cm;
    protected $DB;
    protected $cmid;
    protected $context;

    public function __construct($cm, $DB, $cmid)
    {
        $this->cm = $cm;
        $this->DB = $DB;
        $this->cmid = $cmid;
        $this->context = \context_module::instance($cmid);
    }

    public function getData(): array
    {
        global $USER, $PAGE, $OUTPUT;

        $forum = $this->DB->get_record('forum', ['id' => $this->cm->instance]);
        if (!$forum) {
            return [];
        }

        // Get discussions with pagination
        $discussions = $this->DB->get_records(
            'forum_discussions',
            ['forum' => $forum->id],
            'timemodified DESC',
            '*',
            0,
            15
        );

        $discussiondata = [];
        foreach ($discussions as $d) {
            $user = $this->DB->get_record('user', ['id' => $d->userid]);
            if (!$user) {
                continue;
            }

            // Get user picture
            $userpic = $OUTPUT->user_picture($user, ['size' => 50, 'link' => false]);

            // Check permissions
            $isowner = ($USER->id == $d->userid);
            // $canmanage = $isowner || has_capability('mod/forum:manageown', $this->context) ||
            //     has_capability('mod/forum:manageanydiscussion', $this->context);

            // Get first post content
            $firstpost = $this->DB->get_record('forum_posts', ['id' => $d->firstpost]);
            $message = '';
            if ($firstpost) {
                $message = format_text(
                    $firstpost->message,
                    $firstpost->messageformat,
                    ['context' => $this->context]
                );
            }

            // Count all replies (total posts - 1)
            $totalreplies = $this->DB->count_records('forum_posts', ['discussion' => $d->id]) - 1;

            // Get all replies for preview
            $replies = $this->getDiscussionReplies($d->id, $firstpost->id);

            $discussiondata[] = [
                'id' => $d->id,
                'name' => format_string($d->name),
                'user' => fullname($user),
                'userid' => $user->id,
                'userpicture' => $userpic,
                'post_message' => $message,
                'replies_count' => $totalreplies,
                'replies' => $replies,
                'date' => userdate($d->timemodified, "%A, %d %B %Y, %I:%M %p"),
                // 'canmanage' => $canmanage,
                'isowner' => $isowner,
            ];
        }

        $canpost = has_capability('mod/forum:startdiscussion', $this->context);

        return [
            'isforum' => true,
            'cmid' => $this->cmid,
            'forumname' => format_string($forum->name),
            'forumintro' => format_text(
                $forum->intro,
                $forum->introformat,
                ['context' => $this->context]
            ),
            'discussions' => $discussiondata,
            'canpost' => $canpost,
            'forum_id' => $forum->id,
            'sesskey' => sesskey(),
        ];
    }

    /**
     * Get all replies for a discussion
     */
    /**
     * गेट नेस्टेड रिप्लाईहरू
     */
    protected function getDiscussionReplies($discussionid, $excludefirstpost = null): array
    {
        global $OUTPUT;

        $posts = $this->DB->get_records('forum_posts', ['discussion' => $discussionid], 'created ASC');

        $postMap = [];
        $nested = [];

        foreach ($posts as $post) {
            if ($excludefirstpost && $post->id == $excludefirstpost) {
                continue;
            }

            $postuser = $this->DB->get_record('user', ['id' => $post->userid]);
            $userpic = $OUTPUT->user_picture($postuser, ['size' => 40, 'link' => false]);

            $item = [
                'id' => $post->id,
                'parent' => $post->parent,
                'user' => fullname($postuser),
                'userpicture' => $userpic,
                'message' => format_text($post->message, $post->messageformat, ['context' => $this->context]),
                'timeago' => $this->getTimeAgo($post->created),
                'replies' => [] // यहाँ यसको भित्रका रिप्लाई बस्छन्
            ];

            $postMap[$post->id] = $item;
        }

        // नेस्टिङ मिलाउने (Logic)
        foreach ($postMap as $id => &$post) {
            if ($post['parent'] && isset($postMap[$post['parent']])) {
                $postMap[$post['parent']]['replies'][] = &$post;
            } else {
                $nested[] = &$post;
            }
        }

        return $nested;
    }

    /**
     * Get human-readable time ago
     */
    protected function getTimeAgo($timestamp): string
    {
        $now = time();
        $diff = $now - $timestamp;

        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins == 1 ? '1 minute ago' : "$mins minutes ago";
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours == 1 ? '1 hour ago' : "$hours hours ago";
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days == 1 ? '1 day ago' : "$days days ago";
        } else {
            return userdate($timestamp, "%d %b %Y");
        }
    }
}
