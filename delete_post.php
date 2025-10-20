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

try {
    $pdo->beginTransaction();

    // 🗑️ Delete all related data tied to this community post
    $tables = [
        ['comments', 'post_type', 'post_id'],
        ['likes', 'post_type', 'post_id'],
        ['bookmarks', 'post_type', 'post_id'],
        ['notifications', 'post_type', 'post_id']
    ];

    foreach ($tables as [$table, $typeCol, $idCol]) {
        $stmt = $pdo->prepare("DELETE FROM {$table} WHERE {$typeCol} = 'community' AND {$idCol} = ?");
        $stmt->execute([$postId]);
    }

    // 🗑️ Delete the post itself
    $stmtPost = $pdo->prepare("DELETE FROM community_posts WHERE id = ? AND created_by = ?");
    $stmtPost->execute([$postId, $userId]);

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


// if (!$postId) {
//     echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
//     exit;
// }

// try {
//     $stmt = $pdo->prepare("DELETE FROM community_posts WHERE id=? AND created_by=?");
//     $stmt->execute([$postId, $userId]);

//     if ($stmt->rowCount() > 0) {
//         echo json_encode(['success' => true]);
//     } else {
//         echo json_encode(['success' => false, 'message' => 'Unauthorized or not found']);
//     }
// } catch (Exception $e) {
//     echo json_encode(['success' => false, 'message' => $e->getMessage()]);
// }
