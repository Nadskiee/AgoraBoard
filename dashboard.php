<?php
session_start();

// --- Helper Function ---
function sane($s) {
    return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
}

// --- Initialize or Load Logged-in User ---
// IMPORTANT: The user data is now stored in the session to allow edits on profile.php
if (!isset($_SESSION['currentUser'])) {
    // Hardcode for initial demo load
    $_SESSION['currentUser'] = [
        "name" => "Sarah Johnson",
        "role" => "Member",
        "initial" => "S",
        "email" => "sarah.johnson@example.com" // Added email for profile page
    ];
}
$currentUser = $_SESSION['currentUser'];


// --- Seed sample posts & comments if the session is empty or missing data for the current user ---
if (!isset($_SESSION['posts'])) {
    $_SESSION['posts'] = [
        [
            "user" => "Sarah Johnson",
            "role" => "Member",
            "time" => "about 3 hours ago",
            "content" => "Welcome to the new community board! Let's keep it friendly and helpful.",
            "tag" => "General",
            "likes" => 1,
            "likedBy" => [$currentUser['name']],
            "comments" => 1,
            "shares" => 0,
            "bookmarkedBy" => [$currentUser['name']]
        ],
        [
            "user" => "Maria",
            "role" => "Community Member",
            "time" => "yesterday",
            "content" => "Nawala akong selpon sa plaza. Kung kinsa makakita, palihug ko ug uli 🙏 (I lost my cellphone at the plaza. If anyone finds it, please return it 🙏)",
            "tag" => "Lost & Found",
            "likes" => 2,
            "likedBy" => [],
            "comments" => 1,
            "shares" => 0,
            "bookmarkedBy" => []
        ],
        [
            "user" => "John Doe",
            "role" => "Community Member",
            "time" => "5 hours ago",
            "content" => "Need volunteers for the park clean-up this Saturday!",
            "tag" => "Volunteer",
            "likes" => 5,
            "likedBy" => [],
            "comments" => 2,
            "shares" => 1,
            "bookmarkedBy" => []
        ],
        [
            "user" => "Council Member",
            "role" => "Admin",
            "time" => "1 hour ago",
            "content" => "Road closure on Elm Street starting tomorrow due to emergency repairs.",
            "tag" => "Alert",
            "likes" => 10,
            "likedBy" => [],
            "comments" => 4,
            "shares" => 5,
            "bookmarkedBy" => []
        ],
        [
            "user" => "Jane Smith",
            "role" => "Community Member",
            "time" => "2 days ago",
            "content" => "Looking for a part-time babysitter near the school. Must have references.",
            "tag" => "Job",
            "likes" => 3,
            "likedBy" => [],
            "comments" => 1,
            "shares" => 0,
            "bookmarkedBy" => []
        ],
    ];
}

if (!isset($_SESSION['comments'])) {
    $_SESSION['comments'] = [
        0 => [
            ["user" => "John Doe", "role" => "Community Member", "content" => "Great initiative!", "time" => "2 hours ago"]
        ],
        1 => [
            ["user" => "John Doe", "role" => "Community Member", "content" => "I saw a phone near the tulips. I'll keep an eye.", "time" => "15 hours ago"]
        ],
        2 => [
            ["user" => "Maria", "role" => "Community Member", "content" => "I can help for a few hours!", "time" => "1 hour ago"],
            ["user" => "John Doe", "role" => "Community Member", "content" => "I'm in too!", "time" => "30 minutes ago"] // Added another comment
        ],
        3 => [
            ["user" => "Sarah Johnson", "role" => "Member", "content" => "Thanks for the alert, Admin!", "time" => "5 minutes ago"] // Current user comment
        ],
        4 => [
            ["user" => "Sarah Johnson", "role" => "Community Member", "content" => "I know someone, I'll send you a message!", "time" => "30 minutes ago"]
        ],
    ];
}

// --- Handle POST Actions (Like, Bookmark, Post, Delete/Edit Post, Add/Delete/Edit Comment) ---

// Like post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_post_index'])) {
    $idx = intval($_POST['like_post_index']);
    if (isset($_SESSION['posts'][$idx])) {
        $likedByIndex = array_search($currentUser['name'], $_SESSION['posts'][$idx]['likedBy'] ?? []);
        if ($likedByIndex !== false) {
            unset($_SESSION['posts'][$idx]['likedBy'][$likedByIndex]);
            $_SESSION['posts'][$idx]['likes']--;
        } else {
            $_SESSION['posts'][$idx]['likedBy'][] = $currentUser['name'];
            $_SESSION['posts'][$idx]['likes']++;
        }
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "#post-" . $idx);
    exit;
}

// Bookmark post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bookmark_post_index'])) {
    $idx = intval($_POST['bookmark_post_index']);
    if (isset($_SESSION['posts'][$idx])) {
        $bookmarkedByIndex = array_search($currentUser['name'], $_SESSION['posts'][$idx]['bookmarkedBy'] ?? []);
        if ($bookmarkedByIndex !== false) {
            unset($_SESSION['posts'][$idx]['bookmarkedBy'][$bookmarkedByIndex]);
        } else {
            $_SESSION['posts'][$idx]['bookmarkedBy'][] = $currentUser['name'];
        }
        $_SESSION['posts'][$idx]['bookmarkedBy'] = array_values($_SESSION['posts'][$idx]['bookmarkedBy']);
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "#post-" . $idx);
    exit;
}

// Create post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_post_content'])) {
    $content = sane($_POST['new_post_content']);
    $tag = sane($_POST['new_post_tag'] ?? 'General');
    if (!empty($content)) {
        array_unshift($_SESSION['posts'], [
            "user" => $currentUser['name'], "role" => $currentUser['role'], "time" => "Just now",
            "content" => $content, "tag" => $tag, "likes" => 0, "likedBy" => [],
            "comments" => 0, "shares" => 0, "bookmarkedBy" => []
        ]);
        // Shift comment indices to make room for the new post at index 0
        $newComments = [0 => []];
        foreach($_SESSION['comments'] as $key => $val) { $newComments[$key + 1] = $val; }
        $_SESSION['comments'] = $newComments;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Delete post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post_index'])) {
    $idx = intval($_POST['delete_post_index']);
    if (isset($_SESSION['posts'][$idx]) && $_SESSION['posts'][$idx]['user'] === $currentUser['name']) {
        unset($_SESSION['posts'][$idx]);
        unset($_SESSION['comments'][$idx]);
        // Re-index arrays to prevent gaps
        $_SESSION['posts'] = array_values($_SESSION['posts']);
        $_SESSION['comments'] = array_values($_SESSION['comments']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Edit post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_post_index'], $_POST['edit_post_content'])) {
    $idx = intval($_POST['edit_post_index']);
    $content = sane($_POST['edit_post_content']);
    if (isset($_SESSION['posts'][$idx]) && $_SESSION['posts'][$idx]['user'] === $currentUser['name']) {
        $_SESSION['posts'][$idx]['content'] = $content;
        $_SESSION['posts'][$idx]['time'] = 'Just now (edited)';
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "#post-" . $idx);
    exit;
}

// Add comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_post_index'], $_POST['comment_text'])) {
    $p_idx = intval($_POST['comment_post_index']);
    $txt = sane($_POST['comment_text']);
    if (isset($_SESSION['posts'][$p_idx]) && !empty($txt)) {
        if (!isset($_SESSION['comments'][$p_idx])) $_SESSION['comments'][$p_idx] = [];
        $_SESSION['comments'][$p_idx][] = ["user" => $currentUser['name'], "role" => $currentUser['role'], "content" => $txt, "time" => "Just now"];
        $_SESSION['posts'][$p_idx]['comments']++;
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "#post-" . $p_idx);
    exit;
}

// Delete comment (NEW)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_post'], $_POST['delete_comment_index'])) {
    $p_idx = intval($_POST['delete_comment_post']);
    $c_idx = intval($_POST['delete_comment_index']);
    if (isset($_SESSION['comments'][$p_idx][$c_idx]) && $_SESSION['comments'][$p_idx][$c_idx]['user'] === $currentUser['name']) {
        unset($_SESSION['comments'][$p_idx][$c_idx]);
        $_SESSION['comments'][$p_idx] = array_values($_SESSION['comments'][$p_idx]);
        if (isset($_SESSION['posts'][$p_idx])) {
            $_SESSION['posts'][$p_idx]['comments'] = max(0, $_SESSION['posts'][$p_idx]['comments'] - 1);
        }
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "#post-" . $p_idx);
    exit;
}

// Edit comment (NEW)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment_post'], $_POST['edit_comment_index'], $_POST['edit_comment_text'])) {
    $p_idx = intval($_POST['edit_comment_post']);
    $c_idx = intval($_POST['edit_comment_index']);
    $txt = sane($_POST['edit_comment_text']);
    if (isset($_SESSION['comments'][$p_idx][$c_idx]) && $_SESSION['comments'][$p_idx][$c_idx]['user'] === $currentUser['name'] && !empty($txt)) {
        $_SESSION['comments'][$p_idx][$c_idx]['content'] = $txt;
        $_SESSION['comments'][$p_idx][$c_idx]['time'] = 'Just now (edited)';
    }
    header("Location: " . $_SERVER['PHP_SELF'] . "#post-" . $p_idx);
    exit;
}

// --- Hardcoded events for right panel ---
$events = [
    ["title" => "Community BBQ & Family Fun Day", "category" => "Social", "date" => "Saturday, Dec 23", "time" => "12:00 - 6:00 PM", "location" => "Central Park Pavilion", "attendees" => 47],
    ["title" => "Holiday Light Tour", "category" => "Holiday", "date" => "Sunday, Dec 24", "time" => "7:00 - 9:00 PM", "location" => "Neighborhood Streets", "attendees" => 23],
    ["title" => "New Year's Eve Celebration", "category" => "Celebration", "date" => "Sunday, Dec 31", "time" => "8:00 PM - 12:30 AM", "location" => "Community Center", "attendees" => 120],
];

// Helper function to get tag CSS class
function getTagClass($tag) {
    $tag = strtolower(str_replace([' ', '&'], ['-', ''], $tag));
    return "tag-" . $tag;
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgoraBoard - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sage: #10b981; /* Primary Green */
            --sage-light: #059669; /* Slightly darker/Emerald-600 for contrast/accents */
            --sage-dark: #047857;
            --cream: #F5F5F0; --bg: #FDFDFC; --muted-text: #6c757d;
            --dark-text: #3B3A36; --border-color: #EAE8E3; --sidebar-width: 240px;
        }
        body { background-color: var(--bg); color: var(--dark-text); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: var(--sidebar-width); background: var(--sage); color: #fff; padding: 20px; display: flex; flex-direction: column; }
        .sidebar h4 { font-weight: 700; letter-spacing: 0.5px; padding-left: 10px; }
        .sidebar .nav-link { color: #E6E3D3; font-weight: 500; padding: 12px 15px; border-radius: 8px; margin-bottom: 4px; display: flex; align-items: center; gap: 12px; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { background: rgba(0, 0, 0, 0.1); color: #fff; }
        .sidebar .nav-link i { font-size: 1.1rem; }
        .main-content { margin-left: var(--sidebar-width); padding: 25px 30px; }
        .main-feed { flex: 1; } .right-panel { width: 320px; flex-shrink: 0; }
        .filter-button-group .btn { border-radius: 6px; font-weight: 500; background-color: #E9ECEF; border: 1px solid #DEE2E6; color: var(--muted-text); }
        .composer { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 15px; }
        .composer textarea { border: none; resize: none; width: 100%; background: transparent; outline: none; box-shadow: none; font-size: 1.1rem; }
        .composer .btn-post { background-color: var(--sage); color: #fff; font-weight: 600; border-radius: 50px; padding: 8px 25px; }
        .composer .btn-post:hover { background-color: var(--sage-dark); }
        .composer .tag-btn { font-size: 0.8rem; border-radius: 50px; }
        .post-card { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; }
        .avatar { width: 45px; height: 45px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #fff; background-color: var(--sage); font-weight: 700; font-size: 1.2rem; flex-shrink: 0; }
        
        /* New Tag Color Styles */
        .post-card .badge { font-size: 0.7rem; font-weight: 600; padding: 5px 10px; border-radius: 6px; color: #fff !important; }

        .tag-general { background-color: #3B82F6 !important; } /* Blue */
        .tag-lost-found { background-color: #F97316 !important; } /* Orange */
        .tag-event { background-color: #10B981 !important; } /* Emerald Green */
        .tag-alert { background-color: #DC2626 !important; } /* Red */
        .tag-volunteer { background-color: #A855F7 !important; } /* Purple */
        .tag-job { background-color: #06B6D4 !important; } /* Cyan */

        .btn-as-link { background: none; border: none; padding: 4px 6px; margin: 0; cursor: pointer; color: var(--muted-text); border-radius: 5px; }
        .btn-as-link:hover { background-color: #f0f0f0; }
        .interaction-stats { color: var(--muted-text); font-size: 0.9rem; font-weight: 500; }
        .interaction-stats button { background: none; border: none; padding: 0; color: var(--muted-text); }
        .interaction-stats button.liked { color: #dc3545; }
        .view-comments-toggle { color: var(--muted-text); font-weight: 500; text-decoration: none; font-size: 0.9rem; cursor: pointer; }  
        .view-comments-toggle:hover { text-decoration: underline; }
        .comment-card { background-color: #F8F9FA; padding: 12px; border-radius: 8px; }
        .comment-avatar { width: 32px; height: 32px; font-size: 0.9rem; background-color: var(--muted-text); }
        .events-panel .card-header { background-color: #fff; font-weight: 600; font-size: 1.1rem; }
        .event-card { border: 1px solid var(--border-color); border-radius: 10px; }
        .event-card .badge { font-size: 0.7rem; font-weight: 600; padding: 4px 8px; }
        .badge-social { background-color: #28a745 !important; } .badge-holiday { background-color: #17a2b8 !important; } .badge-celebration { background-color: #fd7e14 !important; }
        .dropdown-item { cursor: pointer; }

        
        .sidebar-footer { margin-top: auto; }
        .sidebar-footer .logout-btn { background-color: rgba(0,0,0,0.15); border: none; text-align: left; }
        .sidebar-footer .logout-btn:hover { background-color: rgba(0,0,0,0.3); color: #fff; }

        /* NEW: Main Header & Notifications */
        .main-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; }
        .main-header .search-bar { flex-grow: 1; }
        .notification-bell { font-size: 1.5rem; color: #6c757d; position: relative; cursor: pointer; }
        .notification-bell .badge { position: absolute; top: -5px; right: -8px; font-size: 0.6rem; }
        .notifications-dropdown { min-width: 320px; max-height: 400px; overflow-y: auto; }
        .notification-item { display: flex; gap: 10px; padding: 8px 12px; border-bottom: 1px solid var(--border-color); }
        .notification-item:last-child { border-bottom: 0; }
        .notification-item .avatar { width: 35px; height: 35px; font-size: 1rem; }
        .notification-item p { margin-bottom: 0; font-size: 0.9rem; }
        .notification-item small { font-size: 0.8rem; color: var(--muted-text); }
        
        /* NEW: Header User Profile Styling */
        .header-profile-btn { 
            background: none; 
            border: none; 
            transition: background-color 0.15s ease-in-out;
            text-decoration: none; /* Make it look like a button but act like a link */
            color: inherit;
        }
        .header-profile-btn:hover {
             background-color: #f0f0f0; 
        }
        .header-profile-btn .avatar {
            width: 35px;
            height: 35px;
            font-size: 1rem;
            flex-shrink: 0;
            background-color: var(--sage-light);
        }
    </style>
</head>
<body>

    <div class="modal fade" id="editPostModal" tabindex="-1" aria-labelledby="editPostModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPostModalLabel">Edit Post</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_post_index" id="editPostIndex">
                        <textarea name="edit_post_content" id="editPostContent" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary bg-success border-success">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="editCommentModal" tabindex="-1" aria-labelledby="editCommentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCommentModalLabel">Edit Comment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_comment_post" id="editCommentPostIndex">
                        <input type="hidden" name="edit_comment_index" id="editCommentIndex">
                        <textarea name="edit_comment_text" id="editCommentText" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm bg-success border-success">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="sidebar">
        <div>
            <h4 class="mb-4"><i class="bi bi-people-fill me-2"></i> AgoraBoard</h4>
            <nav class="nav flex-column">
                <a href="index.php" class="nav-link active"><i class="bi bi-house-door"></i> Dashboard</a>
                <a href="public-safety.php" class="nav-link"><i class="bi bi-shield-exclamation"></i> Public Safety</a>
                <a href="lost-and-found.php" class="nav-link"><i class="bi bi-search"></i> Lost and Found</a>
                <a href="event.php" class="nav-link"><i class="bi bi-calendar-event"></i> Event</a>
                <a href="jobs.php" class="nav-link"><i class="bi bi-briefcase"></i> Jobs</a>
                <a href="polls.php" class="nav-link"><i class="bi bi-bar-chart-line"></i> Polls</a>
                <a href="volunteering.php" class="nav-link"><i class="bi bi-heart"></i> Volunteering</a>
                <hr class="my-3 border-white opacity-25">

                <a href="#" class="nav-link"><i class="bi bi-bookmark"></i> Bookmarks</a>
                <a href="#" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
            </nav>
        </div>

        <div class="sidebar-footer">
            <form action="logout.php" method="POST" class="m-0" id="logoutForm">
                <input type="hidden" name="logout" value="1">
                <button type="button" class="nav-link logout-btn w-100" onclick="confirmLogout()">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <header class="main-header mb-4">
            <div class="search-bar">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input id="searchInput" type="text" class="form-control border-start-0" placeholder="Search announcements...">
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                
                <div class="dropdown">
                    <button class="btn border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill notification-bell">
                            <span class="badge rounded-pill bg-danger">3</span>
                        </i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end notifications-dropdown p-0">
                        <li class="p-2 border-bottom"><strong>Notifications</strong></li>
                        <li><a class="dropdown-item notification-item" href="#"><div class="avatar bg-primary">JD</div><div><p><strong>John Doe</strong> commented on your post: "Great initiative!"</p><small>2 hours ago</small></div></a></li>
                         <li><a class="dropdown-item notification-item" href="#"><div class="avatar bg-info">CM</div><div><p><strong>Council Member</strong> created a new alert: "Road closure on Elm Street..."</p><small>1 hour ago</small></div></a></li>
                         <li><a class="dropdown-item notification-item" href="#"><div class="avatar bg-success">M</div><div><p><strong>Maria</strong> liked your comment on "Need volunteers..."</p><small>5 hours ago</small></div></a></li>
                        <li><a class="dropdown-item text-center small p-2" href="#">View all notifications</a></li>
                    </ul>
                </div>
                
                <div class="dropdown">
                    <a href="profile.php" class="header-profile-btn d-flex align-items-center rounded-pill py-1 pe-2" title="User Profile">
                        <div class="avatar me-2"><?= $currentUser['initial']; ?></div>
                        
                        <div class="d-none d-md-block text-start me-2">
                            <span class="d-block lh-1 fw-bold text-dark" style="font-size: 0.9rem;"><?= sane($currentUser['name']); ?></span>
                            <small class="badge bg-success rounded-pill px-2 py-0" style="font-size: 0.65rem; background-color: var(--sage-light) !important;"><?= strtoupper(sane($currentUser['role'])); ?></small>
                        </div>
                        
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                </div>
            </div>
            </header>

        <div class="d-flex gap-4">
            <div class="main-feed">
                <div class="d-flex gap-2 mb-3 filter-button-group">
                     <button class="btn btn-sm active" data-tag="">ALL</button><button class="btn btn-sm" data-tag="Event">EVENT</button><button class="btn btn-sm" data-tag="Alert">ALERT</button><button class="btn btn-sm" data-tag="Lost & Found">LOST & FOUND</button><button class="btn btn-sm" data-tag="Volunteer">VOLUNTEER</button><button class="btn btn-sm" data-tag="Job">JOB</button>
                </div>

                <div class="composer mb-4">
                    <form method="POST" action="">
                        <div class="d-flex gap-3"><div class="avatar"><?= $currentUser['initial']; ?></div><textarea name="new_post_content" placeholder="What's happening in your community?" rows="2" required></textarea></div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-2 flex-wrap">
                                <input type="radio" class="btn-check" name="new_post_tag" id="tag_general" value="General" checked><label class="btn btn-outline-secondary btn-sm tag-btn" for="tag_general">General</label>
                                <input type="radio" class="btn-check" name="new_post_tag" id="tag_event" value="Event"><label class="btn btn-outline-secondary btn-sm tag-btn" for="tag_event">Event</label>
                                <input type="radio" class="btn-check" name="new_post_tag" id="tag_alert" value="Alert"><label class="btn btn-outline-secondary btn-sm tag-btn" for="tag_alert">Alert</label>
                                <input type="radio" class="btn-check" name="new_post_tag" id="tag_lost" value="Lost & Found"><label class="btn btn-outline-secondary btn-sm tag-btn" for="tag_lost">Lost & Found</label>
                                <input type="radio" class="btn-check" name="new_post_tag" id="tag_volunteer" value="Volunteer"><label class="btn btn-outline-secondary btn-sm tag-btn" for="tag_volunteer">Volunteer</label>
                                <input type="radio" class="btn-check" name="new_post_tag" id="tag_job" value="Job"><label class="btn btn-outline-secondary btn-sm tag-btn" for="tag_job">Job</label>
                            </div>
                            <button type="submit" class="btn btn-post">Post</button>
                        </div>
                    </form>
                </div>

                <div id="postsContainer">
                    <?php foreach ($_SESSION['posts'] as $i => $post):
                        $isLiked = in_array($currentUser['name'], $post['likedBy'] ?? []);
                        $isBookmarked = in_array($currentUser['name'], $post['bookmarkedBy'] ?? []);
                        $isCurrentUserPost = ($post['user'] === $currentUser['name']);
                        $tagClass = getTagClass($post['tag']);
                    ?>
                        <div id="post-<?= $i; ?>" class="post-card mb-3" data-post-index="<?= $i; ?>" data-post-tag="<?= sane($post['tag']); ?>">
                            <div class="d-flex gap-3">
                                <div class="avatar"><?= strtoupper(substr($post['user'], 0, 1)); ?></div>
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= sane($post['user']); ?></strong><?php if ($post['role'] === 'Admin'): ?><small class="text-muted"> • <?= sane($post['role']); ?></small><?php endif; ?>
                                            <small class="text-muted d-block" style="margin-top: -3px;"><?= sane($post['time']); ?></small>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge <?= $tagClass; ?>"><?= sane($post['tag']); ?></span>
                                            <form method="POST" class="m-0"><input type="hidden" name="bookmark_post_index" value="<?= $i; ?>"><button type="submit" class="btn-as-link" data-bs-toggle="tooltip" title="Bookmark"><i class="bi <?= $isBookmarked ? 'bi-bookmark-fill text-warning' : 'bi-bookmark'; ?>"></i></button></form>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link text-muted p-0 btn-as-link" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <?php if ($isCurrentUserPost): ?>
                                                        <li><a class="dropdown-item edit-post-btn" data-index="<?= $i; ?>" data-content="<?= htmlspecialchars($post['content'], ENT_QUOTES); ?>"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                        <li><form method="POST" class="m-0" onsubmit="return confirm('Delete this post?');"><input type="hidden" name="delete_post_index" value="<?= $i; ?>"><button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button></form></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                    <?php endif; ?>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="showCustomAlert('Report functionality is a work in progress.'); return false;"><i class="bi bi-flag me-2"></i>Report</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="mt-2 mb-2 post-content-text"><?= nl2br(sane($post['content'])); ?></p>

                                    <div class="d-flex justify-content-between align-items-center mt-3 interaction-stats">
                                        <div class="d-flex align-items-center gap-3">
                                            <form method="POST" class="m-0">
                                                <input type="hidden" name="like_post_index" value="<?= $i; ?>">
                                                <button type="submit" class="p-0 <?= $isLiked ? 'liked' : ''; ?>">
                                                    <i class="bi <?= $isLiked ? 'bi-heart-fill' : 'bi-heart'; ?>"></i> <?= $post['likes']; ?>
                                                </button>
                                            </form>
                                            <span><i class="bi bi-chat-left"></i> <?= $post['comments']; ?></span>
                                            <span><i class="bi bi-arrow-repeat"></i> <?= $post['shares']; ?></span>
                                        </div>
                                        <?php if ($post['comments'] > 0): ?>
                                            <a class="view-comments-toggle" data-bs-toggle="collapse" href="#comments-<?= $i; ?>" role="button" aria-expanded="false" aria-controls="comments-<?= $i; ?>">
                                                View comments
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="collapse mt-3" id="comments-<?= $i; ?>">
                                        <hr>
                                        <?php if (isset($_SESSION['comments'][$i]) && !empty($_SESSION['comments'][$i])): ?>
                                            <?php foreach ($_SESSION['comments'][$i] as $c_idx => $comment):
                                                $isCurrentUserComment = ($comment['user'] === $currentUser['name']);
                                            ?>
                                            <div class="d-flex gap-2 mb-3 comment-card">
                                                <div class="avatar comment-avatar"><?= strtoupper(substr($comment['user'], 0, 1)); ?></div>
                                                <div class="w-100">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <strong><?= sane($comment['user']); ?></strong>
                                                            <small class="text-muted"> • <?= sane($comment['time']); ?></small>
                                                        </div>
                                                        <?php if ($isCurrentUserComment): ?>
                                                        <div class="dropdown">
                                                             <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
                                                             <ul class="dropdown-menu dropdown-menu-end">
                                                                 <li><a class="dropdown-item edit-comment-btn" data-post-index="<?= $i; ?>" data-comment-index="<?= $c_idx; ?>" data-comment-text="<?= htmlspecialchars($comment['content'], ENT_QUOTES); ?>"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                                 <li><form method="POST" class="m-0" onsubmit="return confirm('Delete this comment?');"><input type="hidden" name="delete_comment_post" value="<?= $i; ?>"><input type="hidden" name="delete_comment_index" value="<?= $c_idx; ?>"><button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button></form></li>
                                                             </ul>
                                                         </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="mb-0 comment-content-text-<?= $i; ?>-<?= $c_idx; ?>"><?= sane($comment['content']); ?></p>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex gap-2 mt-3 pt-2 border-top">
                                            <div class="avatar comment-avatar bg-secondary"><?= $currentUser['initial']; ?></div>
                                            <form method="POST" class="w-100 d-flex gap-2" action="#comments-<?= $i; ?>">
                                                <input type="hidden" name="comment_post_index" value="<?= $i; ?>">
                                                <input type="text" name="comment_text" class="form-control form-control-sm rounded-pill" placeholder="Write a comment..." required>
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill"><i class="bi bi-send-fill"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="right-panel">
                <div class="card events-panel">
                    <div class="card-header"><i class="bi bi-calendar-event me-2"></i> Upcoming Events</div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($events as $event): ?>
                            <li class="list-group-item event-card m-2">
                                <span class="badge badge-pill badge-<?= strtolower($event['category']); ?> mb-1"><?= sane($event['category']); ?></span>
                                <h6 class="fw-bold mb-1"><?= sane($event['title']); ?></h6>
                                <p class="mb-0 small text-muted"><i class="bi bi-clock me-1"></i> <?= sane($event['date']); ?> | <?= sane($event['time']); ?></p>
                                <p class="mb-0 small text-muted"><i class="bi bi-geo-alt me-1"></i> <?= sane($event['location']); ?></p>
                                <p class="mb-0 small mt-2"><i class="bi bi-people me-1"></i> <?= $event['attendees']; ?> attending</p>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item text-center"><a href="#">View All Events</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Custom Alert for placeholder functionality
        function showCustomAlert(message) {
            alert(message);
        }

        // Logout Confirmation
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                document.getElementById('logoutForm').submit();
            }
        }

        // Post Filter Logic
        document.addEventListener('DOMContentLoaded', () => {
            const postsContainer = document.getElementById('postsContainer');
            const filterButtons = document.querySelectorAll('.filter-button-group .btn');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    const selectedTag = this.getAttribute('data-tag');
                    
                    postsContainer.querySelectorAll('.post-card').forEach(post => {
                        const postTag = post.getAttribute('data-post-tag');
                        if (!selectedTag || postTag === selectedTag) {
                            post.style.display = '';
                        } else {
                            post.style.display = 'none';
                        }
                    });
                });
            });

            // Post Edit Modal Logic
            const editPostModal = document.getElementById('editPostModal');
            if (editPostModal) {
                editPostModal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    const postIndex = button.getAttribute('data-index');
                    const postContent = button.getAttribute('data-content');
                    
                    document.getElementById('editPostIndex').value = postIndex;
                    document.getElementById('editPostContent').value = postContent;
                });
            }
            
            // Comment Edit Modal Logic (NEW)
            const editCommentModal = document.getElementById('editCommentModal');
            if (editCommentModal) {
                editCommentModal.addEventListener('show.bs.modal', event => {
                    const button = event.relatedTarget;
                    const postIndex = button.getAttribute('data-post-index');
                    const commentIndex = button.getAttribute('data-comment-index');
                    const commentText = button.getAttribute('data-comment-text');
                    
                    document.getElementById('editCommentPostIndex').value = postIndex;
                    document.getElementById('editCommentIndex').value = commentIndex;
                    document.getElementById('editCommentText').value = commentText;
                });

                // Attach click listeners to all edit comment buttons
                document.querySelectorAll('.edit-comment-btn').forEach(btn => {
                    btn.setAttribute('data-bs-toggle', 'modal');
                    btn.setAttribute('data-bs-target', '#editCommentModal');
                });
            }
            
            // Attach click listeners to all edit post buttons
            document.querySelectorAll('.edit-post-btn').forEach(btn => {
                btn.setAttribute('data-bs-toggle', 'modal');
                btn.setAttribute('data-bs-target', '#editPostModal');
            });
            
            // Initializing tooltips (required for Bootstrap 5)
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        });
    </script>
</body>
</html>