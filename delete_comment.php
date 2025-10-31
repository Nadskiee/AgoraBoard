<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

$userId = $_SESSION['currentUser']['id'] ?? null;
$commentId = (int)($_POST['comment_id'] ?? 0);
$postType = $_POST['comment_post_type'] ?? 'community';

if (!$userId || !$commentId) {
  echo json_encode(['success' => false, 'message' => 'Missing user or comment ID']);
  exit;
}

$now = date('Y-m-d H:i:s');

$stmt = $pdo->prepare("UPDATE comments SET deleted_at = ? WHERE id = ? AND user_id = ? AND post_type = ?");
$stmt->execute([$now, $commentId, $userId, $postType]);

if ($stmt->rowCount() > 0) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => 'Comment not found or unauthorized']);
}
