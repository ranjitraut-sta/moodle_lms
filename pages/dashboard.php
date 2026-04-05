<?php
// pages/dashboard.php - Moodle dashboard page
require_once('../../../config.php');
require_login();

$PAGE->set_url('/theme/mytheme/pages/dashboard.php');
$PAGE->set_pagelayout('mydashboard'); // or 'standard'
$PAGE->set_title('Dashboard');
$PAGE->set_heading(fullname($USER));

// Load CSS
$PAGE->requires->css('/theme/mytheme/styles/user-dash.css');

// Dynamic data (mock/hardcoded now; replace with real Moodle queries later)
$data = [
    'username' => fullname($USER),
    'useravatar' => 'https://i.pravatar.cc/40?u=' . $USER->id, // Moodle user avatar
    'studentid' => 'ALT/SOP/023/0195', // from user profile
    'greeting' => 'Hello, ' . fullname($USER) . '! 👋',
    'subgreeting' => 'Let\'s learn something new today!',
    'notificationsbadge' => '30',
    
    // Stats
    'stats' => [
        [
            'title' => 'Courses',
            'value' => '12/30',
            'label' => 'Completed',
            'iconhtml' => '<img src="https://via.placeholder.com/50x50?text=📚" alt="books" class="amd-lms-user-dash-stat-icon">',
            'class' => 'amd-lms-user-dash-stat-card-courses'
        ],
        [
            'title' => 'Class Attendance',
            'value' => '64 points',
            'iconhtml' => '<img src="https://via.placeholder.com/50x50?text=📅" alt="calendar" class="amd-lms-user-dash-stat-icon">',
            'class' => 'amd-lms-user-dash-stat-card-attendance'
        ],
        [
            'title' => 'Grade',
            'score' => '85',
            'scoretext' => 'Your score is<br>Keep it up! 😉',
            'class' => 'amd-lms-user-dash-stat-card-grade'
        ],
        [
            'title' => 'Leaderboard',
            'value' => '1st',
            'iconhtml' => '<img src="https://via.placeholder.com/50x50?text=🏆" alt="trophy" class="amd-lms-user-dash-stat-icon">',
            'class' => 'amd-lms-user-dash-stat-card-leaderboard'
        ]
    ],
    
    // Events
    'events' => [
        [
            'image' => 'https://images.unsplash.com/photo-1587620962725-abab7fe55159?w=500&q=80',
            'title' => 'Design System II',
            'instructor' => 'by Ekemini Mark',
            'date' => '25 July, 2024',
            'time' => '7:00 pm WAT',
            'buttontext' => 'Join Class'
        ]
    ],
    
    // Leaderboard
    'leaderboard' => [
        ['rank' => '01', 'name' => fullname($USER) . ' (you) 🏆', 'score' => '100/100'],
        ['rank' => '02', 'name' => 'Chinedu Okeke', 'score' => '85/100'],
        ['rank' => '03', 'name' => 'Amina Yusuf', 'score' => '84/100'],
        ['rank' => '04', 'name' => 'Olufemi Adewale', 'score' => '75/100']
    ],
    
    // Assignments
    'assignments_todo' => [
        ['title' => 'Capstone Project', 'deadline' => '16 Aug, 2024 • 3:00 pm WAT'],
        ['title' => 'Design System Article', 'deadline' => '16 Aug, 2024 • 3:00 pm WAT']
    ],
    'assignments_completed' => [
        ['title' => 'Designing for Use - The Heuristic Principles', 'grade' => '96/100'],
        ['title' => 'Problem Definition + Ideation', 'grade' => '100/100']
    ],
    
    // Nav items
    'nav_main' => [
        ['url' => '#', 'icon' => 'bi-grid-fill', 'text' => 'Dashboard', 'active' => true],
        ['url' => 'courselist.php', 'icon' => 'bi-calendar-week', 'text' => 'Course Lists'],
        ['url' => 'courselist.php', 'icon' => 'bi-calendar-week', 'text' => 'Course Lists2'],
        ['url' => '#', 'icon' => 'bi-play-btn', 'text' => 'Classroom'],
        ['url' => '#', 'icon' => 'bi-chat-left-text', 'text' => 'Messages', 'badge' => '30'],
        ['url' => '#', 'icon' => 'bi-people', 'text' => 'Communities', 'badge' => '30'],
        ['url' => '#', 'icon' => 'bi-gear', 'text' => 'Settings']
    ],
    'nav_footer' => [
        ['url' => '#', 'icon' => 'bi-question-circle', 'text' => 'Help Center'],
        ['url' => $CFG->wwwroot . '/login/logout.php', 'icon' => 'bi-box-arrow-right', 'text' => 'Logout']
    ],
    
    'communities' => [
        'LMS Africa Althub',
        'School of Product',
        'Direct Messages'
    ]
];

// Load dashboard renderer
echo $OUTPUT->header();

// Render dashboard using mustache templates
echo $OUTPUT->render_from_template('theme_mytheme/dashboard/main', $data);

echo $OUTPUT->footer();
?>

