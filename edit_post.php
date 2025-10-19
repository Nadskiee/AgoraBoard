<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

if (!isset($_SESSION['currentUser']['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['currentUser']['id'];
$postId = (int)($_POST['post_id'] ?? 0);
$newTitle = trim($_POST['post_title'] ?? '');
$newContent = trim($_POST['post_content'] ?? '');

if (!$postId || ($newTitle === '' && $newContent === '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// 🔍 Check ownership and fetch current post
$stmt = $pdo->prepare("SELECT title, content FROM community_posts WHERE id=? AND created_by=?");
$stmt->execute([$postId, $userId]);
$currentPost = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentPost) {
    echo json_encode(['success' => false, 'message' => 'You don’t have permission to edit this post.']);
    exit;
}

// 🔍 Check if anything changed
if ($currentPost['title'] === $newTitle && $currentPost['content'] === $newContent) {
    echo json_encode(['success' => false, 'message' => 'No changes detected.']);
    exit;
}

// 💾 Perform update
$stmt = $pdo->prepare("UPDATE community_posts SET title=?, content=? WHERE id=? AND created_by=?");
$stmt->execute([$newTitle, $newContent, $postId, $userId]);

echo json_encode([
    'success' => true,
    'new_title' => $newTitle,
    'new_content' => $newContent
]);
