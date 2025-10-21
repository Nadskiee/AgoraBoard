<?php
session_start();
require_once 'db_connect.php';

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

if (!isset($_SESSION['currentUser'])) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'unauthorized']);
        exit;
    } else {
        header("Location: login.php");
        exit;
    }
}

$userId = $_SESSION['currentUser']['id'] ?? null;
$eventId = $_POST['event_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$eventId || !$action || !$userId) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'missing parameters']);
        exit;
    } else {
        header("Location: event.php");
        exit;
    }
}

try {
    if ($action === 'join') {
        $stmt = $pdo->prepare("INSERT IGNORE INTO event_attendees (event_id, user_id, status) VALUES (?, ?, 'confirmed')");
        $stmt->execute([$eventId, $userId]);
    } elseif ($action === 'leave') {
        $stmt = $pdo->prepare("DELETE FROM event_attendees WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$eventId, $userId]);
    } else {
        throw new Exception('Invalid action');
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    } else {
        header("Location: event.php");
        exit;
    }
} catch (Exception $e) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    } else {
        header("Location: event.php");
        exit;
    }
}
