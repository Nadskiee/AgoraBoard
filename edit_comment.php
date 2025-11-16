<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

$userId = $_SESSION['currentUser']['id'] ?? null;
$commentId = (int)($_POST['comment_id'] ?? 0);
$text = trim($_POST['comment_text'] ?? '');

if (!$userId || !$commentId || $text === '') {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

// ----------------------
// Bad & extreme words
// ----------------------
$extremeWords = ['kill', 'murder', 'rape', 'terrorist', 'bomb', 'threat', 'abuse', 'hate'];
$badWords = ['fuck','shit','puta','pota','ulol','gago','nigga','bitch','asshole','puki','tite','motherfucker','slut','whore','damn', 'hell', 'crap', 'stupid', 'idiot', 'sucks', 'dumb'];

$textLower = strtolower($text);

// Block extreme words entirely
foreach ($extremeWords as $word) {
    if (strpos($textLower, $word) !== false) {
        echo json_encode([
            'success' => false,
            'message' => 'Your comment contains prohibited content and cannot be posted.'
        ]);
        exit;
    }
}

// Flag bad words
$shouldFlag = 0;
foreach ($badWords as $word) {
    if (strpos($textLower, $word) !== false) {
        $shouldFlag = 1;
        break;
    }
}

// ----------------------
// Update comment
// ----------------------
$stmt = $pdo->prepare("
    UPDATE comments 
    SET content = ?, is_flagged = ?, updated_at = NOW() 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$text, $shouldFlag, $commentId, $userId]);

if ($stmt->rowCount() > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'Comment updated',
        'new_content' => $text,
        'flagged' => $shouldFlag
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized or no changes'
    ]);
}
exit;
