<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['currentUser'])) {
    header("Location: login.php");
    exit;
}
$currentUser = $_SESSION['currentUser'];
$userId = $currentUser['id'] ?? null;

// Fetch notifications
$stmt = $pdo->prepare("
    SELECT id, sender_name, message, avatar_color, initials, created_at, is_read
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgoraBoard - Polls</title>
    <link rel="stylesheet" href="assets/dashboard.css?v=<?= time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <div class="sidebar">
        <div>
            <h4 class="mb-4"><i class="bi bi-people-fill me-2"></i> AgoraBoard</h4>
            <nav class="nav flex-column">
                <a href="dashboard.php" class="nav-link active"><i class="bi bi-house-door"></i> Dashboard</a>
                <a href="public-safety.php" class="nav-link"><i class="bi bi-shield-exclamation"></i> Public Safety</a>
                <a href="lost-and-found.php" class="nav-link"><i class="bi bi-search"></i> Lost and Found</a>
                <a href="event.php" class="nav-link"><i class="bi bi-calendar-event"></i> Event</a>
                <a href="jobs.php" class="nav-link"><i class="bi bi-briefcase"></i> Jobs</a>
                <a href="polls_view.php" class="nav-link"><i class="bi bi-bar-chart-line"></i> Polls</a>
                <a href="volunteering.php" class="nav-link"><i class="bi bi-heart"></i> Volunteering</a>
                <hr class="my-3 border-white opacity-25">

                <a href="bookmarks_view.php" class="nav-link"><i class="bi bi-bookmark"></i> Bookmarks</a>
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

    <!-- 🧩 Main Content -->
    <div class="main-content flex-grow-1 p-4">
        <div class="main-header mb-4 d-flex justify-content-between align-items-center">
            <h3 style="font-weight:700;"><i class="bi bi-bell-fill me-2"></i>All Notifications</h3>
        </div>

        <div class="post-card mb-4 p-4 shadow-sm">
            <?php if (count($notifications) > 0): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($notifications as $n): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-start <?= !$n['is_read'] ? 'unread' : '' ?>">
                            <div class="d-flex gap-3 align-items-start">
                                <div class="avatar rounded-circle d-flex align-items-center justify-content-center text-white fw-bold bg-<?= htmlspecialchars($n['avatar_color']) ?>">
                                    <?= htmlspecialchars($n['initials']) ?>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($n['sender_name']) ?></strong>
                                    <?= htmlspecialchars($n['message']) ?><br>
                                    <small class="text-muted"><?= date('M d, Y • h:i A', strtotime($n['created_at'])) ?></small>
                                </div>
                            </div>
                            <form method="POST" action="delete_notifications.php" onsubmit="return confirm('Delete this notification?');">
                                <input type="hidden" name="id" value="<?= $n['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                    You have no notifications at the moment.
            <?php endif; ?>
        </div>
    </div>
</body>

</html>