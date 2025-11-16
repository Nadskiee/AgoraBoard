<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

$userId = $_SESSION['currentUser']['id'] ?? null;
$postId = $_POST['post_id'] ?? null;
$categoryId = $_POST['category_id'] ?? null;
$reason = $_POST['reason'] ?? null;
$otherReason = trim($_POST['other_reason'] ?? '');

if (!$userId || !$postId || !$reason || !$categoryId) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$finalReason = $reason === 'Other' ? $otherReason : $reason;
$postType = $_POST['post_type'] ?? 'general';

try {
    $stmt = $pdo->prepare("
        INSERT INTO reports (reporter_id, post_type, post_id, reason, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $postType, $postId, $finalReason]);

    // 2️⃣ Flag the post/comment for admin
    if ($postType === 'community') {
        $pdo->prepare("UPDATE community_posts SET is_flagged = 1 WHERE id = ?")
            ->execute([$postId]);
    } elseif ($postType === 'comment') {
        $pdo->prepare("UPDATE comments SET is_flagged = 1 WHERE id = ?")
            ->execute([$postId]);
    }
    
    echo json_encode(['success' => true, 'message' => 'Report submitted successfully.']);
} catch (PDOException $e) {
    error_log("Report error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error submitting report.']);
}
