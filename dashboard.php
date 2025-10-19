<?php
session_start();
require_once 'db_connect.php';

// 🔐 Auth check
if (!isset($_SESSION['currentUser'])) {
    header("Location: login.php");
    exit;
}
$currentUser = $_SESSION['currentUser'];
$userId = $currentUser['id'] ?? null;

// 🧼 Helper
function sane($s)
{
    return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
}

// 📝 Handle post creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_post_content'])) {
    $content = sane($_POST['new_post_content']);
    $category = sane($_POST['new_post_category'] ?? 'General');
    $title = sane($_POST['new_post_title'] ?? '');
    $stmt = $pdo->prepare("INSERT INTO community_posts (title, content, category, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $content, $category, $userId]);
    header("Location: dashboard.php");
    exit;
}

// ✏️ Edit post (AJAX only)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['edit_post_index'], $_POST['edit_post_content']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
) {
    header('Content-Type: application/json');
    $postId = (int)$_POST['edit_post_index'];
    $newContent = trim($_POST['edit_post_content']);

    try {
        $stmt = $pdo->prepare("UPDATE community_posts SET content=? WHERE id=? AND created_by=?");
        $stmt->execute([$newContent, $postId, $userId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Post updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Unauthorized or no changes']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    exit; // prevent page HTML from leaking into AJAX response
}


// 🗑️ Delete post
if (isset($_POST['delete_post_id'])) {
    $postId = $_POST['delete_post_id'];

    // only allow the user who made the post to delete it
    $stmt = $pdo->prepare("DELETE FROM community_posts WHERE id = ? AND created_by = ?");
    $stmt->execute([$postId, $userId]);
}


// ❤️ Like post (AJAX only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_post_id']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
    $postId = (int)$_POST['like_post_id'];

    $check = $pdo->prepare("SELECT id FROM likes WHERE user_id=? AND post_type='community' AND post_id=?");
    $check->execute([$userId, $postId]);

    if ($check->rowCount() > 0) {
        $pdo->prepare("DELETE FROM likes WHERE user_id=? AND post_type='community' AND post_id=?")->execute([$userId, $postId]);
        $liked = false;
    } else {
        $pdo->prepare("INSERT INTO likes (user_id, post_type, post_id) VALUES (?, 'community', ?)")->execute([$userId, $postId]);
        $liked = true;
    }

    $count = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_type='community' AND post_id=?");
    $count->execute([$postId]);
    $totalLikes = $count->fetchColumn();

    echo json_encode([
        'success' => true,
        'liked' => $liked,
        'total_likes' => $totalLikes
    ]);
    exit;
}

// 🗑️ Delete comment (AJAX only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest') {
    $commentId = (int)$_POST['delete_comment_id'];
    $pdo->prepare("DELETE FROM comments WHERE id=? AND user_id=?")->execute([$commentId, $userId]);
    echo json_encode(['success' => true]);
    exit;
}

// ✏️ Edit comment (AJAX only)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['comment_id'], $_POST['comment_text']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
) {

    $commentId = (int)$_POST['comment_id'];
    $text = sane($_POST['comment_text']);

    $stmt = $pdo->prepare("UPDATE comments SET content=? WHERE id=? AND user_id=?");
    $stmt->execute([$text, $commentId, $userId]);

    echo json_encode(['success' => true]);
    exit;
}


// 📥 Fetch posts
$stmt = $pdo->query("
    SELECT p.*, u.first_name, u.last_name, u.role,
        (SELECT COUNT(*) FROM likes WHERE post_type='community' AND post_id=p.id) AS total_likes,
        (SELECT COUNT(*) FROM comments WHERE post_type='community' AND post_id=p.id) AS total_comments,
        (SELECT COUNT(*) FROM bookmarks WHERE post_type='community' AND post_id=p.id) AS total_bookmarks
    FROM community_posts p
    LEFT JOIN users u ON p.created_by = u.id
    ORDER BY p.is_pinned DESC, p.created_at DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 💬 Fetch comments per post
function getComments($pdo, $postId)
{
    $q = $pdo->prepare("
        SELECT c.*, u.first_name, u.last_name 
        FROM comments c 
        LEFT JOIN users u ON c.user_id = u.id 
        WHERE c.post_type='community' AND c.post_id=? 
        ORDER BY c.created_at ASC
    ");
    $q->execute([$postId]);
    return $q->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgoraBoard - Home</title>
    <link rel="stylesheet" href="assets/dashboard.css?v=<?php echo time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

</head>

<body>

    <div class="modal fade" id="editPostModal" tabindex="-1" aria-labelledby="editPostModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editPostForm" method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPostModalLabel">Edit Post</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" id="editPostTitle" name="edit_post_title" class="input-underline mb-3 w-100" placeholder="Post title (optional)">
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
        <header class="main-header mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">

            <!-- 🔍 Search Bar -->
            <div class="search-bar flex-grow-1">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                    <input id="searchInput" type="text" class="form-control border-start-0" placeholder="Search announcements...">
                </div>
            </div>

            <!-- 🔔 Notifications & Profile -->
            <div class="d-flex align-items-center gap-3">

                <!-- Notification Bell -->
                <div class="dropdown position-relative">
                    <button class="btn border-0 p-0 position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                        <i class="bi bi-bell-fill fs-5 text-dark"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end notifications-dropdown shadow-sm p-0 mt-2" style="min-width: 300px;">
                        <li class="p-3 border-bottom fw-bold">Notifications</li>
                        <li><a class="dropdown-item notification-item d-flex gap-2 p-3" href="#">
                                <div class="avatar bg-primary text-white fw-bold">JD</div>
                                <div>
                                    <p class="mb-1"><strong>John Doe</strong> commented on your post: <em>"Great initiative!"</em></p>
                                    <small class="text-muted">2 hours ago</small>
                                </div>
                            </a></li>
                        <li><a class="dropdown-item notification-item d-flex gap-2 p-3" href="#">
                                <div class="avatar bg-info text-white fw-bold">CM</div>
                                <div>
                                    <p class="mb-1"><strong>Council Member</strong> created a new alert: <em>"Road closure on Elm Street..."</em></p>
                                    <small class="text-muted">1 hour ago</small>
                                </div>
                            </a></li>
                        <li><a class="dropdown-item notification-item d-flex gap-2 p-3" href="#">
                                <div class="avatar bg-success text-white fw-bold">M</div>
                                <div>
                                    <p class="mb-1"><strong>Maria</strong> liked your comment on <em>"Need volunteers..."</em></p>
                                    <small class="text-muted">5 hours ago</small>
                                </div>
                            </a></li>
                        <li><a class="dropdown-item text-center small p-2" href="#">View all notifications</a></li>
                    </ul>
                </div>

                <!-- Profile Dropdown -->
                <div class="dropdown">
                    <a href="profile.php" class="header-profile-btn d-flex align-items-center rounded-pill py-1 pe-2 text-decoration-none" title="User Profile">
                        <div class="avatar me-2 bg-secondary text-white fw-bold">
                            <?= strtoupper(substr($currentUser['initial'] ?? $currentUser['name'] ?? '?', 0, 1)); ?>
                        </div>
                        <div class="d-none d-md-block text-start me-2">
                            <span class="d-block lh-1 fw-bold text-dark" style="font-size: 0.9rem;">
                                <?= sane($currentUser['name'] ?? 'Anonymous'); ?>
                            </span>
                            <small class="badge bg-success rounded-pill px-2 py-0" style="font-size: 0.65rem;">
                                <?= strtoupper(sane($currentUser['role'] ?? 'Member')); ?>
                            </small>
                        </div>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                </div>

            </div>
        </header>

        <div class="d-flex gap-4">
            <div class="main-feed">
                <div class="d-flex gap-2 mb-3 filter-button-group">
                    <button class="btn btn-sm active" data-tag="">ALL</button>
                    <button class="btn btn-sm" data-tag="Event">EVENT</button>
                    <button class="btn btn-sm" data-tag="Alert">ALERT</button>
                    <button class="btn btn-sm" data-tag="Lost and Found">LOST & FOUND</button>
                    <button class="btn btn-sm" data-tag="Volunteer">VOLUNTEER</button>
                    <button class="btn btn-sm" data-tag="Job">JOB</button>
                </div>

                <div class="composer mb-4">
                    <form method="POST">
                        <div class="d-flex gap-3">
                            <!-- 👤 Avatar -->
                            <div class="avatar"><?= $currentUser['initial'] ?? '?'; ?></div>

                            <!-- 📝 Post Fields -->
                            <div class="w-100">
                                <!-- Title Input -->
                                <input type="text"
                                    name="new_post_title"
                                    class="input-underline mb-2"
                                    placeholder="Post title (optional)">


                                <!-- Content Textarea -->
                                <textarea name="new_post_content"
                                    class="form-control"
                                    placeholder="What's happening in your community?"
                                    rows="3"
                                    required></textarea>
                            </div>
                        </div>

                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex flex-column flex-grow-1 me-3">
                                <!-- 🏷️ Tag Selection -->
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php
                                    $tags = [
                                        'General' => 'General',
                                        'Event' => 'Event',
                                        'Alert' => 'Alert',
                                        'Lost and Found' => 'Lost and Found',
                                        'Volunteer' => 'Volunteer',
                                        'Job' => 'Job'
                                    ];
                                    foreach ($tags as $label => $value):
                                        $safeId = 'tag_' . strtolower(str_replace([' ', '&'], ['_', 'and'], $value));
                                    ?>
                                        <input type="radio"
                                            class="btn-check"
                                            name="new_post_category"
                                            id="<?= $safeId; ?>"
                                            value="<?= $value; ?>"
                                            <?= $value === 'General' ? 'checked' : ''; ?>>

                                        <label class="btn btn-outline-secondary btn-sm tag-btn"
                                            for="<?= $safeId; ?>">
                                            <?= htmlspecialchars($label, ENT_QUOTES); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- 🚀 Submit Button -->
                            <button type="submit" class="btn btn-post align-self-start">Post</button>
                        </div>

                    </form>
                </div>

                <!-- Posts -->
                <div id="postsContainer">
                    <?php foreach ($posts as $i => $post): ?>
                        <?php
                        $postId = $post['id'];
                        $first = trim($post['first_name'] ?? '');
                        $last = trim($post['last_name'] ?? '');
                        $postUser = ($first || $last) ? "$first $last" : 'Anonymous';

                        $postRole = $post['role'] ?? '';
                        $postTime = date('M d, Y H:i', strtotime($post['created_at']));
                        $postTag = $post['category'] ?? 'General';
                        $postTitle = $post['title'] ?? '';
                        $postContent = $post['content'] ?? '';
                        $postLikes = $post['total_likes'] ?? 0;
                        $postComments = $post['total_comments'] ?? 0;

                        $isCurrentUserPost = $post['created_by'] == $userId;

                        // Like status
                        $isLiked = $pdo->prepare("SELECT 1 FROM likes WHERE post_type='community' AND post_id=? AND user_id=?");
                        $isLiked->execute([$postId, $userId]);
                        $liked = $isLiked->rowCount() > 0;

                        // Bookmark status
                        $isBookmarked = $pdo->prepare("SELECT 1 FROM bookmarks WHERE post_type='community' AND post_id=? AND user_id=?");
                        $isBookmarked->execute([$postId, $userId]);
                        $bookmarked = $isBookmarked->rowCount() > 0;

                        // Fetch comments for this post
                        $commentsForPost = getComments($pdo, $postId);

                        // Tag class for filtering
                        $tagClassMap = [
                            'Lost and Found' => 'tag-lost-and-found',
                            'Event' => 'tag-event',
                            'Alert' => 'tag-alert',
                            'Volunteer' => 'tag-volunteer',
                            'Job' => 'tag-job'
                        ];
                        $tagClass = $tagClassMap[$postTag] ?? 'tag-general';


                        ?>
                        <div id="post-<?= $postId; ?>" class="post-card mb-3" data-post-tag="<?= $postTag; ?>">
                            <div class="d-flex gap-3">
                                <div class="avatar"><?= strtoupper(substr($postUser, 0, 1)); ?></div>
                                <div class="w-100">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= sane($postUser); ?></strong>
                                            <?php if ($postRole === 'Admin'): ?>
                                                <small class="text-muted"> • <?= sane($postRole); ?></small>
                                            <?php endif; ?>
                                            <small class="text-muted d-block"><?= sane($postTime); ?></small>
                                            <?php if (!empty($postTitle)): ?>
                                                <h5 class="post-title"><?= htmlspecialchars($postTitle); ?></h5>
                                            <?php else: ?>
                                                <h6 class="text-muted post-title">Untitled Post</h5>
                                                <?php endif; ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge <?= $tagClass; ?>"><?= htmlspecialchars($postTag, ENT_NOQUOTES); ?></span>
                                            <!-- 🔖 Bookmark Button -->
                                            <button class="btn-bookmark btn-as-link" data-post-id="<?= $postId; ?>" title="Bookmark">
                                                <i class="bi <?= $bookmarked ? 'bi-bookmark-fill text-warning' : 'bi-bookmark'; ?>"></i>
                                            </button>

                                            <!-- ⋮ Ellipsis Dropdown -->
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link text-muted p-0 btn-as-link" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <?php if ($isCurrentUserPost): ?>
                                                        <li>
                                                            <a href="#"
                                                                class="dropdown-item edit-post-btn"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editPostModal"
                                                                data-index="<?= $postId; ?>"
                                                                data-title="<?= htmlspecialchars($postTitle, ENT_QUOTES); ?>"
                                                                data-content="<?= htmlspecialchars($postContent, ENT_QUOTES); ?>">
                                                                <i class="bi bi-pencil me-2"></i>Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <form method="POST" class="m-0" onsubmit="return confirm('Delete this post?');">
                                                                <input type="hidden" name="delete_post_id" value="<?= $postId; ?>">
                                                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="showCustomAlert('Report functionality coming soon.'); return false;">
                                                            <i class="bi bi-flag me-2"></i>Report
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                        </div>
                                    </div>

                                    <p class="mt-2 mb-2 post-content post-content-text"><?= nl2br(sane($postContent)); ?></p>

                                    <div class="d-flex justify-content-between align-items-center mt-3 interaction-stats">
                                        <div class="d-flex align-items-center gap-3">
                                            <!-- ❤️ Like Button -->
                                            <button class="btn-like" data-post-id="<?= $postId; ?>">
                                                <i class="bi <?= $liked ? 'bi-heart-fill text-danger' : 'bi-heart'; ?>"></i>
                                                <span class="like-count"><?= $postLikes; ?></span>
                                            </button>



                                            <!-- 💬 Comment Count + Toggle -->
                                            <a class="view-comments-toggle" data-bs-toggle="collapse" href="#comments-<?= $postId; ?>">
                                                <i class="bi bi-chat-left"></i>
                                                <span id="comment-count-<?= $postId; ?>"><?= $postComments; ?></span>
                                            </a>

                                        </div>
                                    </div>

                                    <!-- 💬 Comment Section -->
                                    <div class="collapse" id="comments-<?= $postId; ?>">
                                        <!-- Existing Comments -->
                                        <div class="comment-list">
                                            <?php foreach ($commentsForPost as $comment): ?>
                                                <?php
                                                $commentUser = trim($comment['first_name'] . ' ' . $comment['last_name']) ?: 'Anonymous';
                                                $commentTime = date('M d, Y H:i', strtotime($comment['created_at']));
                                                $commentContent = $comment['content'];
                                                $isCurrentUserComment = $comment['user_id'] == $userId;
                                                ?>
                                                <div class="d-flex gap-2 mb-3 comment-card" data-comment-id="<?= $comment['id']; ?>">
                                                    <div class="avatar comment-avatar bg-secondary"><?= strtoupper(substr($commentUser, 0, 1)); ?></div>
                                                    <div class="w-100">
                                                        <div class="d-flex justify-content-between">
                                                            <div>
                                                                <strong><?= sane($commentUser); ?></strong>
                                                                <small class="text-muted"> • <?= sane($commentTime); ?></small>
                                                            </div>
                                                            <?php if ($isCurrentUserComment): ?>
                                                                <div class="dropdown">
                                                                    <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                                                        <i class="bi bi-three-dots"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                                        <li>
                                                                            <button class="dropdown-item btn-edit-comment"
                                                                                data-comment-id="<?= $comment['id']; ?>"
                                                                                data-comment-text="<?= htmlspecialchars($commentContent, ENT_QUOTES); ?>"
                                                                                data-post-index="<?= $postId; ?>">
                                                                                <i class="bi bi-pencil me-2"></i>Edit
                                                                            </button>

                                                                        </li>
                                                                        <li>
                                                                            <button class="dropdown-item text-danger btn-delete-comment"
                                                                                data-comment-id="<?= $comment['id']; ?>">
                                                                                <i class="bi bi-trash me-2"></i>Delete
                                                                            </button>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <p class="mb-0 comment-text"><?= sane($commentContent); ?></p>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- Comment Input -->
                                        <div class="d-flex gap-2 mt-3 pt-2 border-top">
                                            <div class="avatar comment-avatar bg-secondary"><?= $currentUser['initial'] ?? '?'; ?></div>
                                            <form method="POST" class="comment-form w-100 d-flex gap-2" data-post-id="<?= $postId; ?>">
                                                <input type="text" name="comment_text" class="form-control form-control-sm rounded-pill" placeholder="Write a comment..." required>
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill">
                                                    <i class="bi bi-send-fill"></i>
                                                </button>
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
                        <?php if (!empty($events)): ?>
                            <?php foreach ($events as $event): ?>
                                <?php
                                $category = $event['category'] ?? 'general';
                                $title = $event['title'] ?? 'Untitled';
                                $date = $event['date'] ?? 'TBD';
                                $time = $event['time'] ?? '';
                                $location = $event['location'] ?? 'Location not set';
                                $attendees = $event['attendees'] ?? 0;
                                ?>
                                <li class="list-group-item event-card m-2">
                                    <span class="badge <?= $tagClass; ?>"><?= htmlspecialchars($postTag, ENT_NOQUOTES); ?></span>
                                    <h6 class="fw-bold mb-1"><?= sane($title); ?></h6>
                                    <p class="mb-0 small text-muted"><i class="bi bi-clock me-1"></i> <?= sane($date); ?><?= $time ? ' | ' . sane($time) : ''; ?></p>
                                    <p class="mb-0 small text-muted"><i class="bi bi-geo-alt me-1"></i> <?= sane($location); ?></p>
                                    <p class="mb-0 small mt-2"><i class="bi bi-people me-1"></i> <?= $attendees; ?> attending</p>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted text-center">No upcoming events found.</li>
                        <?php endif; ?>
                        <li class="list-group-item text-center"><a href="#">View All Events</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            console.log('Main script loaded');

            // 🔔 Tooltip Init
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el));

            // 🔍 Post Filter Logic
            const postsContainer = document.getElementById('postsContainer');
            const filterButtons = document.querySelectorAll('.filter-button-group .btn');
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    const selectedTag = this.getAttribute('data-tag');
                    postsContainer.querySelectorAll('.post-card').forEach(post => {
                        const postTag = post.getAttribute('data-post-tag');
                        post.style.display = (!selectedTag || postTag === selectedTag) ? '' : 'none';
                    });
                });
            });


            // 🧠 POST EDIT & DELETE HANDLER
            document.addEventListener('click', async e => {
                const editBtn = e.target.closest('.edit-post-btn');
                const deleteBtn = e.target.closest('.dropdown-item.text-danger');

                // ✏️ EDIT POST — open modal
                if (editBtn) {
                    const postId = editBtn.dataset.index;
                    const content = editBtn.dataset.content;
                    const title = editBtn.dataset.title || '';

                    document.getElementById('editPostTitle').value = title;
                    document.getElementById('editPostIndex').value = postId;
                    document.getElementById('editPostContent').value = content;

                    const modalEl = document.getElementById('editPostModal');
                    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modalInstance.show();

                }

                // 🗑️ DELETE POST — AJAX
                if (deleteBtn && deleteBtn.closest('form')) {
                    e.preventDefault();
                    const form = deleteBtn.closest('form');
                    const postId = form.querySelector('input[name="delete_post_id"]').value;

                    if (!confirm('Delete this post?')) return;

                    try {
                        const response = await fetch('delete_post.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'include', // ✅ send session cookie!
                            body: `post_id=${postId}`
                        });

                        const result = await response.json();
                        if (result.success) {
                            const postCard = document.getElementById(`post-${postId}`);
                            if (postCard) postCard.remove();
                        } else {
                            alert(result.message || 'Failed to delete post');
                        }
                    } catch (err) {
                        console.error('Delete failed:', err);
                    }
                }
            });

            // 💾 HANDLE EDIT POST SUBMISSION
            document.getElementById('editPostForm').addEventListener('submit', async e => {
                e.preventDefault();

                const newTitle = document.getElementById('editPostTitle').value.trim();
                const postId = document.getElementById('editPostIndex').value;
                const newContent = document.getElementById('editPostContent').value.trim();

                if (!newContent) return;

                try {
                    const res = await fetch('edit_post.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'include',
                        body: `post_id=${encodeURIComponent(postId)}&post_title=${encodeURIComponent(newTitle)}&post_content=${encodeURIComponent(newContent)}`
                    });

                    const result = await res.json();
                    if (result.success) {
                        const postCard = document.getElementById(`post-${postId}`);
                        if (postCard) {
                            // ✅ Update content
                            const contentEl = postCard.querySelector('.post-content');
                            if (contentEl) contentEl.textContent = result.new_content;

                            // ✅ Update title
                            const titleEl = postCard.querySelector('.post-title');
                            if (titleEl) {
                                titleEl.textContent = result.new_title;
                            } else if (result.new_title) {
                                const metaBlock = postCard.querySelector('.d-flex.justify-content-between.align-items-start > div');
                                if (metaBlock) {
                                    const newTitleEl = document.createElement('h5');
                                    newTitleEl.className = 'post-title';
                                    newTitleEl.textContent = result.new_title;
                                    metaBlock.appendChild(newTitleEl);
                                }
                            }
                        }

                        // ✅ Update edit button's data attributes
                        const editBtn = document.querySelector(`.edit-post-btn[data-index="${postId}"]`);
                        if (editBtn) {
                            editBtn.dataset.content = newContent;
                            editBtn.dataset.title = newTitle;
                        }

                        // ✅ Close modal and reset form
                        const modalEl = document.getElementById('editPostModal');
                        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modalInstance.hide();
                        document.getElementById('editPostForm').reset();
                    } else {
                        alert(result.message || 'Failed to edit post.');
                    }
                } catch (err) {
                    console.error('Edit failed:', err);
                    alert('Something went wrong.');
                }
            });


            // 💬 Comment submit
            document.querySelectorAll('.comment-form').forEach(form => {
                form.addEventListener('submit', async e => {
                    e.preventDefault();
                    const postId = form.dataset.postId;
                    const input = form.querySelector('input[name="comment_text"]');
                    const text = input.value.trim();
                    if (!text) return;

                    try {
                        const response = await fetch('comment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: `comment_post_id=${postId}&comment_text=${encodeURIComponent(text)}`
                        });

                        const raw = await response.text();
                        const result = JSON.parse(raw);
                        if (result.success && result.html) {
                            const commentList = document.querySelector(`#comments-${postId} .comment-list`);
                            commentList.insertAdjacentHTML('beforeend', result.html);
                            console.log('Inserting comment:', result.html);
                            input.value = '';

                            // ✅ Update comment count
                            const countSpan = document.getElementById(`comment-count-${postId}`);
                            if (countSpan) {
                                countSpan.textContent = parseInt(countSpan.textContent || '0') + 1;
                            }

                            // 🔽 Add this block here to auto-expand the comment section
                            const collapse = form.closest('.collapse');
                            if (collapse && !collapse.classList.contains('show')) {
                                new bootstrap.Collapse(collapse, {
                                    toggle: true
                                });
                            }
                        }

                    } catch (error) {
                        console.error('Comment submission failed:', error);
                    }
                });
            });

            // ✏️ Comment Edit Modal
            const editCommentModal = document.getElementById('editCommentModal');
            if (editCommentModal) {
                document.querySelectorAll('.btn-edit-comment').forEach(button => {
                    button.addEventListener('click', () => {
                        const postId = button.getAttribute('data-post-index');
                        const commentId = button.getAttribute('data-comment-id');
                        const commentText = button.getAttribute('data-comment-text');

                        document.getElementById('editCommentPostIndex').value = postId;
                        document.getElementById('editCommentIndex').value = commentId;
                        document.getElementById('editCommentText').value = commentText;

                        // ✅ Explicitly show the modal
                        console.log('Edit clicked:', {
                            postId,
                            commentId,
                            commentText
                        });
                        const modalInstance = new bootstrap.Modal(editCommentModal);
                        modalInstance.show();
                    });
                });

                // ✏️ Attach edit post buttons
                document.querySelectorAll('.edit-post-btn').forEach(btn => {
                    btn.setAttribute('data-bs-toggle', 'modal');
                    btn.setAttribute('data-bs-target', '#editPostModal');
                });

                const editCommentModal = document.getElementById('editCommentModal');
                const editCommentIndex = document.getElementById('editCommentIndex');
                const editCommentText = document.getElementById('editCommentText');

                // ✅ Event Delegation for Edit + Delete buttons
                document.addEventListener('click', async e => {
                    const editBtn = e.target.closest('.btn-edit-comment');
                    const deleteBtn = e.target.closest('.btn-delete-comment');

                    // 📝 EDIT COMMENT — Open Modal
                    if (editBtn) {
                        const commentId = editBtn.dataset.commentId;
                        const commentText = editBtn.dataset.commentText;

                        editCommentIndex.value = commentId;
                        editCommentText.value = commentText;

                        const modal = new bootstrap.Modal(editCommentModal);
                        modal.show();
                    }

                    // 🗑️ DELETE COMMENT
                    if (deleteBtn) {
                        const commentId = deleteBtn.dataset.commentId;
                        const commentCard = document.querySelector(`[data-comment-id="${commentId}"]`);
                        const postContainer = commentCard.closest('.collapse');
                        const postId = postContainer?.id.replace('comments-', '');

                        if (confirm('Delete this comment?')) {
                            try {
                                const response = await fetch('delete_comment.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: `comment_id=${commentId}`
                                });

                                const result = await response.json();
                                if (result.success) {
                                    // Remove comment visually
                                    commentCard.remove();

                                    // 🔢 Update comment counter
                                    if (postId) {
                                        const counter = document.getElementById(`comment-count-${postId}`);
                                        if (counter) {
                                            let current = parseInt(counter.textContent.trim(), 10) || 0;
                                            if (current > 0) counter.textContent = current - 1;
                                        }
                                    }
                                }
                            } catch (error) {
                                console.error('Comment deletion failed:', error);
                            }
                        }
                    }
                });

                // ✅ Handle comment edit submission
                document.querySelector('#editCommentModal form').addEventListener('submit', async e => {
                    e.preventDefault();
                    const commentId = document.getElementById('editCommentIndex').value;
                    const text = document.getElementById('editCommentText').value.trim();

                    try {
                        const response = await fetch('edit_comment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: `comment_id=${commentId}&comment_text=${encodeURIComponent(text)}`
                        });

                        const result = await response.json();
                        if (result.success) {
                            const commentCard = document.querySelector(`[data-comment-id="${commentId}"]`);
                            if (commentCard) {
                                commentCard.querySelector('.comment-text').textContent = text;

                                // ✅ Update the edit button's data-comment-text
                                const editButton = commentCard.querySelector('.btn-edit-comment');
                                if (editButton) {
                                    editButton.setAttribute('data-comment-text', text);
                                }
                                bootstrap.Modal.getInstance(editCommentModal).hide();
                            }
                        }
                    } catch (error) {
                        console.error('Comment edit failed:', error);
                    }
                });
            }


            // ❤️ AJAX Like Handler
            document.querySelectorAll('.btn-like').forEach(button => {
                button.addEventListener('click', async () => {
                    const postId = button.dataset.postId;
                    try {
                        const response = await fetch('like.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: `like_post_id=${postId}`
                        });

                        const result = await response.json();
                        if (!result.success) return;

                        const icon = button.querySelector('i');
                        const count = button.querySelector('.like-count');

                        if (icon) {
                            icon.className = result.liked ? 'bi bi-heart-fill text-danger' : 'bi bi-heart';
                        }

                        button.classList.toggle('liked', result.liked);
                        if (count) count.textContent = result.total_likes;
                    } catch (error) {
                        console.error('Like toggle failed:', error);
                    }
                });
            });

            // 📌 Bookmark toggle
            document.querySelectorAll('.btn-bookmark').forEach(button => {
                button.addEventListener('click', async () => {
                    const postId = button.dataset.postId;
                    try {
                        const response = await fetch('bookmark.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: `bookmark_post_id=${postId}`
                        });

                        const result = await response.json();
                        if (result.success) {
                            const icon = button.querySelector('i');
                            icon.className = result.bookmarked ? 'bi bi-bookmark-fill text-warning' : 'bi bi-bookmark';
                        }
                    } catch (error) {
                        console.error('Bookmark toggle failed:', error);
                    }
                });
            });


            // 🔐 Logout Confirmation
            window.confirmLogout = function() {
                if (confirm("Are you sure you want to log out?")) {
                    document.getElementById('logoutForm').submit();
                }
            };

            // ⚠️ Placeholder Alert
            window.showCustomAlert = function(message) {
                alert(message);
            };
        });
    </script>
</body>

</html>