<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['currentUser'])) {
    header("Location: login.php");
    exit;
}
$currentUser = $_SESSION['currentUser'];
$userId = $currentUser['id'] ?? null;

$stmt = $pdo->prepare("
    SELECT id, sender_name, message, avatar_color, initials, created_at, is_read
    FROM notifications
    WHERE user_id = ? AND deleted_at IS NULL
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
    <div class="dashboard-layout d-flex">
        <?php include 'user_sidebar.php'; ?>


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