<?php
session_start();
header('Content-Type: application/json');

require 'db_connect.php';
$userId = $_SESSION['currentUser']['id'] ?? null;

if (!$userId) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, sender_name, message, avatar_color, initials, created_at FROM notifications WHERE user_id = ?");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($notifications);

?>
