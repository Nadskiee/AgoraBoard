<?php
session_start(); // ✅ Must be first
require_once 'db_connect.php';
header('Content-Type: application/json');
error_log("User ID: " . ($_SESSION['currentUser']['id'] ?? 'null'));

$userId = $_SESSION['currentUser']['id'] ?? null;
$notifId = (int)($_POST['id'] ?? 0);

if (!$userId || !$notifId) {
    error_log("Delete failed: Missing userId or notifId");
    echo json_encode(['success' => false, 'error' => 'Missing user or notification ID']);
    exit;
}

$now = date('Y-m-d H:i:s');

$stmt = $pdo->prepare("UPDATE notifications SET deleted_at = NOW() WHERE id = ? AND user_id = ?");
$stmt->execute([$notifId, $userId]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Notification not found or unauthorized']);
}

exit;
