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

$stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
$success = $stmt->execute([$notifId, $userId]);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    error_log("Delete failed: PDO execution error");
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
exit;
