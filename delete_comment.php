<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

$userId = $_SESSION['currentUser']['id'] ?? null;
$commentId = (int)($_POST['comment_id'] ?? 0);
$postType = $_POST['comment_post_type'] ?? 'community';

if (!$userId || !$commentId) {
  echo json_encode(['success' => false]);
  exit;
}

$stmt = $pdo->prepare("DELETE FROM comments WHERE id=? AND user_id=? AND post_type=?");
$stmt->execute([$commentId, $userId, $postType]);

echo json_encode(['success' => true]);
