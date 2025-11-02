<?php
session_start();
require_once 'db_connect.php';

// 🔒 Must be logged in
if (!isset($_SESSION['currentUser'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to report posts.']);
    exit;
}

$userId = $_SESSION['currentUser']['id'] ?? null;
$postId = $_POST['post_id'] ?? null;
$postType = $_POST['post_type'] ?? null;
$reason = trim($_POST['reason'] ?? '');

// ✅ Validate required fields
if (!$postId || !$postType || empty($reason)) {
    echo json_encode(['success' => false, 'message' => 'Missing post ID, post type, or reason.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO reports (reporter_id, post_type, post_id, reason, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $postType, $postId, $reason]);

    echo json_encode(['success' => true, 'message' => 'Post reported successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error reporting post: ' . $e->getMessage()]);
}
