<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

// 🛡️ Auth check
if (!isset($_SESSION['currentUser'])) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$userId = $_SESSION['currentUser']['id'] ?? null;
$eventId = $_GET['event_id'] ?? null;

if (!$eventId || !$userId) {
    echo json_encode(['error' => 'missing parameters']);
    exit;
}

// 🧾 Fetch attendees
$stmt = $pdo->prepare("
    SELECT 
        ea.user_id, 
        ea.status, 
        u.first_name, 
        u.last_name,
        CASE WHEN u.id = ? THEN 1 ELSE 0 END AS is_current_user
    FROM event_attendees ea
    INNER JOIN users u ON ea.user_id = u.id
    WHERE ea.event_id = ?
");
$stmt->execute([$userId, $eventId]);
$attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Wrap in object for frontend flexibility
echo json_encode([
    'attendees' => $attendees,
    'attending' => !empty(array_filter($attendees, fn($a) => $a['is_current_user']))
]);
