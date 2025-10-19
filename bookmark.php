<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

// ✅ Enforce AJAX-only access
if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

// ✅ Validate session and input
$userId = $_SESSION['currentUser']['id'] ?? null;
$postId = (int)($_POST['bookmark_post_id'] ?? 0);

if (!$userId || !$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing data']);
    exit;
}

try {
    // ✅ Check if already bookmarked
    $check = $pdo->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND post_type = 'community' AND post_id = ?");
    $check->execute([$userId, $postId]);

    if ($check->rowCount() > 0) {
        // 🔄 Remove bookmark
        $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND post_type = 'community' AND post_id = ?")->execute([$userId, $postId]);
        $bookmarked = false;
    } else {
        // ✅ Add bookmark
        $pdo->prepare("INSERT INTO bookmarks (user_id, post_type, post_id) VALUES (?, 'community', ?)")->execute([$userId, $postId]);
        $bookmarked = true;
    }

    echo json_encode([
        'success' => true,
        'bookmarked' => $bookmarked
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
