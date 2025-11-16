<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['currentUser'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$currentUser = $_SESSION['currentUser'];
$userId = $currentUser['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = intval($_POST['event_id']);

    // Check ownership
    $stmt = $pdo->prepare("SELECT created_by FROM events WHERE id = ? AND deleted_at IS NULL");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        echo json_encode(['success' => false, 'error' => 'Event not found or already deleted.']);
        exit;
    }

    if ($event['created_by'] != $userId) {
        echo json_encode(['success' => false, 'error' => 'You are not authorized to delete this event.']);
        exit;
    }

    // Soft delete: set deleted_at
    $stmt = $pdo->prepare("UPDATE events SET deleted_at = NOW() WHERE id = ?");
    $stmt->execute([$eventId]);

    echo json_encode(['success' => true]);
}
