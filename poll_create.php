<?php
session_start();
require_once 'db_connect.php';

// 🔐 Auth check
if (!isset($_SESSION['currentUser'])) {
    header("Location: login.php");
    exit;
}

$currentUser = $_SESSION['currentUser'];
$userId = $currentUser['id'] ?? null;

// 🛡️ Validate input
$question = trim($_POST['question'] ?? '');
$options = $_POST['options'] ?? [];

if (!$question || count($options) < 2) {
    // Redirect back with error (optional)
    header("Location: polls_view.php?error=invalid");
    exit;
}

try {
    // 🧠 Insert poll
    $stmt = $pdo->prepare("INSERT INTO polls (question, created_by, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$question, $userId]);
    $pollId = $pdo->lastInsertId();

    // 🧠 Insert options
    $optStmt = $pdo->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)");
    foreach ($options as $opt) {
        $optText = trim($opt);
        if ($optText !== '') {
            $optStmt->execute([$pollId, $optText]);
        }
    }

    // ✅ Redirect back to poll view
    header("Location: polls_view.php?success=1");
    exit;

} catch (PDOException $e) {
    error_log("Poll creation error: " . $e->getMessage());
    header("Location: polls_view.php?error=db");
    exit;
}
?>
