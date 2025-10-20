<?php
require 'db_connect.php';
session_start();
$userId = $_SESSION['currentUser']['id'] ?? null;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$userId]);
$count = $stmt->fetchColumn();

echo json_encode(['unread' => $count]);
?>
