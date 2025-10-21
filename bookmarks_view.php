<?php
session_start();
require_once 'db_connect.php';

// 🛡️ Auth check
if (!isset($_SESSION['currentUser'])) {
    header("Location: login.php");
    exit;
}

$currentUser = $_SESSION['currentUser'];
$userId = $currentUser['id'] ?? null;

// 🧼 Sanitizer
function sane($s)
{
    return htmlspecialchars(trim($s ?? ''), ENT_QUOTES, 'UTF-8');
}

// 📚 Fetch bookmarked posts
try {
    $stmt = $pdo->prepare("
        SELECT 
            p.id AS post_id,
            p.title AS post_title,
            p.content AS post_content,
            p.category,
            p.created_at,
            u.first_name,
            u.last_name,
            (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS total_likes,
            (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id) AS total_comments
        FROM bookmarks b
        JOIN community_posts p ON b.post_id = p.id
        LEFT JOIN users u ON p.created_by = u.id
        WHERE b.user_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$userId]);
    $bookmarkedPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgoraBoard - Bookmarks</title>
    <link rel="stylesheet" href="assets/dashboard.css?v=<?= time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <!-- 🧭 Sidebar (fixed position like dashboard/polls) -->
    <div class="sidebar">
        <div class="sidebar-content">
            <h4 class="mb-4"><i class="bi bi-people-fill me-2"></i> AgoraBoard</h4>
            <nav class="nav flex-column">
                <a href="dashboard.php" class="nav-link"><i class="bi bi-house-door"></i> Dashboard</a>
                <a href="public-safety.php" class="nav-link"><i class="bi bi-shield-exclamation"></i> Public Safety</a>
                <a href="lost-and-found.php" class="nav-link"><i class="bi bi-search"></i> Lost & Found</a>
                <a href="event.php" class="nav-link"><i class="bi bi-calendar-event"></i> Events</a>
                <a href="jobs.php" class="nav-link"><i class="bi bi-briefcase"></i> Jobs</a>
                <a href="polls_view.php" class="nav-link"><i class="bi bi-bar-chart-line"></i> Polls</a>
                <a href="volunteering.php" class="nav-link"><i class="bi bi-heart"></i> Volunteering</a>

                <hr class="my-3 border-white opacity-25">

                <a href="bookmarks_view.php" class="nav-link active"><i class="bi bi-bookmark"></i> Bookmarks</a>
                <a href="#" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
            </nav>
        </div>

        <div class="sidebar-footer">
            <form action="logout.php" method="POST" id="logoutForm">
                <input type="hidden" name="logout" value="1">
                <button type="button" class="nav-link logout-btn w-100 text-start" onclick="confirmLogout()">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <!-- 📰 Main Content -->
    <div class="main-content">
        <div class="main-header mb-4">
            <h3 class="fw-bold">📑 My Bookmarks</h3>
        </div>

        <div class="main-feed">
            <?php if (empty($bookmarkedPosts)): ?>
                <div class="text-center p-5 bg-white border rounded-3">
                    <i class="bi bi-bookmark-x fs-3 d-block mb-2 text-muted"></i>
                    <strong>You haven’t bookmarked any posts yet.</strong>
                    <p class="mt-2 mb-0 text-muted">Save interesting posts to revisit them later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($bookmarkedPosts as $post): ?>
                    <?php
                    $postUser = trim(($post['first_name'] ?? '') . ' ' . ($post['last_name'] ?? '')) ?: 'Anonymous';
                    $postTime = date('M d, Y • h:i A', strtotime($post['created_at']));
                    $category = $post['category'] ?? 'General';

                    $tagClass = match ($category) {
                        'Lost and Found' => 'tag-lost-and-found',
                        'Event' => 'tag-event',
                        'Alert' => 'tag-alert',
                        'Volunteer' => 'tag-volunteer',
                        'Job' => 'tag-job',
                        default => 'tag-general',
                    };
                    ?>

                    <div class="post-card mb-3 p-3 bg-white border rounded-3 shadow-sm">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="avatar"><?= strtoupper(substr($postUser, 0, 1)); ?></div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= sane($postUser); ?></strong>
                                        <small class="d-block text-muted"><?= sane($postTime); ?></small>
                                    </div>
                                    <span class="badge <?= $tagClass; ?>"><?= sane($category); ?></span>
                                </div>

                                <h5 class="post-title mt-2"><?= sane($post['post_title'] ?? 'Untitled'); ?></h5>
                                <p class="mb-2 text-muted"><?= nl2br(sane($post['post_content'])); ?></p>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="interaction-stats d-flex gap-3">
                                        <span><i class="bi bi-heart-fill text-danger me-1"></i><?= $post['total_likes']; ?> Likes</span>
                                        <span><i class="bi bi-chat-left-text me-1"></i><?= $post['total_comments']; ?> Comments</span>
                                    </div>
                                    <form method="POST" action="unbookmark.php" class="m-0">
                                        <input type="hidden" name="post_id" value="<?= $post['post_id']; ?>">
                                        <button type="submit" class="btn-as-link text-danger fw-semibold">
                                            <i class="bi bi-bookmark-x me-1"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ⚙️ Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                document.getElementById('logoutForm').submit();
            }
        }
    </script>
</body>

</html>