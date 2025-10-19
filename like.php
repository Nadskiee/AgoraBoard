<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

// Validate AJAX request
if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

// Validate session
$userId = $_SESSION['currentUser']['id'] ?? null;
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

// Validate POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_post_id'])) {
    $postId = (int)$_POST['like_post_id'];

    try {
        // Check if already liked
        $check = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND post_type = 'community' AND post_id = ?");
        $check->execute([$userId, $postId]);

        if ($check->rowCount() > 0) {
            // Unlike
            $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_type = 'community' AND post_id = ?")->execute([$userId, $postId]);
            $liked = false;
        } else {
            // Like
            $pdo->prepare("INSERT INTO likes (user_id, post_type, post_id) VALUES (?, 'community', ?)")->execute([$userId, $postId]);
            $liked = true;
        }

        // Get updated like count
        $count = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_type = 'community' AND post_id = ?");
        $count->execute([$postId]);
        $totalLikes = (int)$count->fetchColumn();

        echo json_encode([
            'success' => true,
            'liked' => $liked,
            'total_likes' => $totalLikes
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}
?>
