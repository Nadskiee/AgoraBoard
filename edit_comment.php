<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

$userId = $_SESSION['currentUser']['id'] ?? null;
$commentId = (int)($_POST['comment_id'] ?? 0);
$text = trim($_POST['comment_text'] ?? '');

if (!$userId || !$commentId || $text === '') {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $pdo->prepare("UPDATE comments SET content = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
$stmt->execute([$text, $commentId, $userId]);

echo json_encode(['success' => true]);
