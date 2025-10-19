<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

$userId = $_SESSION['currentUser']['id'] ?? null;
$commentId = (int)($_POST['comment_id'] ?? 0);

if (!$userId || !$commentId) {
  echo json_encode(['success' => false]);
  exit;
}

$stmt = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
$stmt->execute([$commentId, $userId]);

echo json_encode(['success' => true]);
