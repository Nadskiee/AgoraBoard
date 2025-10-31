<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

if (!isset($_SESSION['currentUser']['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['currentUser']['id'];
$postId = (int)($_POST['post_id'] ?? 0);

if (!$postId) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

try {
    $pdo->beginTransaction();

    $now = date('Y-m-d H:i:s');

    // 🗑️ Soft delete related data
    $tables = [
        ['comments', 'post_type', 'post_id'],
        ['likes', 'post_type', 'post_id'],
        ['bookmarks', 'post_type', 'post_id'],
        ['notifications', 'post_type', 'post_id']
    ];

    foreach ($tables as [$table, $typeCol, $idCol]) {
        $stmt = $pdo->prepare("UPDATE {$table} SET deleted_at = ? WHERE {$typeCol} = 'community' AND {$idCol} = ?");
        $stmt->execute([$now, $postId]);
    }

    // 🗑️ Soft delete the post itself
    $stmtPost = $pdo->prepare("UPDATE community_posts SET deleted_at = ? WHERE id = ? AND created_by = ?");
    $stmtPost->execute([$now, $postId, $userId]);

    if ($stmtPost->rowCount() > 0) {
        $pdo->commit();
        echo json_encode(['success' => true]);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Unauthorized or not found']);
    }
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
