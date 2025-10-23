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
$pollId = $_POST['poll_id'] ?? null;
$optionId = $_POST['option_id'] ?? null;

if (!$pollId || !$optionId) {
    header("Location: polls_view.php?error=missing");
    exit;
}

// 🧠 Check if user already voted
$stmt = $pdo->prepare("SELECT id FROM poll_votes WHERE poll_id = ? AND user_id = ?");
$stmt->execute([$pollId, $userId]);
$existingVote = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existingVote) {
    // Optional: prevent double voting
    header("Location: polls_view.php?error=already_voted");
    exit;
}

try {
    // ✅ Record vote
    $stmt = $pdo->prepare("INSERT INTO poll_votes (poll_id, option_id, user_id, voted_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$pollId, $optionId, $userId]);

    header("Location: polls_view.php?success=vote");
    exit;

} catch (PDOException $e) {
    error_log("Poll vote error: " . $e->getMessage());
    header("Location: polls_view.php?error=db");
    exit;
}
?>
