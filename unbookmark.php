<?php
session_start();
require_once 'db_connect.php';

$userId = $_SESSION['currentUser']['id'] ?? null;
$postId = $_POST['post_id'] ?? null;

if (!$userId || !$postId) {
    http_response_code(400);
    exit('Invalid request');
}

$stmt = $pdo->prepare("DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?");
$stmt->execute([$userId, $postId]);

header("Location: bookmarks_view.php");
exit;
?>
