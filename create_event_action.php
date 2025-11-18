<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['currentUser'])) {
    header("Location: login.php");
    exit;
}

$currentUser = $_SESSION['currentUser'];
$userId = $currentUser['id'] ?? null;

// 🧼 Sanitize input
function sane($s) { return htmlspecialchars(trim($s ?? ''), ENT_QUOTES, 'UTF-8'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sane($_POST['title'] ?? '');
    $description = sane($_POST['description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $location = sane($_POST['location'] ?? '');

    if ($title && $description && $event_date) {
        $stmt = $pdo->prepare("
            INSERT INTO events (title, description, event_date, location, created_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $description, $event_date, $location, $userId]);
    }

    header("Location: event.php");
    exit;
}
?>
