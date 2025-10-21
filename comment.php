<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ✅ Enforce AJAX-only access
if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'xmlhttprequest') {
  http_response_code(403);
  echo json_encode(['success' => false, 'error' => 'Forbidden']);
  exit;
}

// ✅ Validate session and input
$userId = $_SESSION['currentUser']['id'] ?? null;
$postId = (int)($_POST['comment_post_id'] ?? 0);
$text = trim($_POST['comment_text'] ?? '');
$postType = $_POST['comment_post_type'] ?? 'community';

if (!$userId || !$postId || $text === '') {
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => 'Missing data']);
  exit;
}

try {
  // ✅ Insert comment
  $stmt = $pdo->prepare("INSERT INTO comments (post_type, post_id, user_id, content) VALUES (?, ?, ?, ?)");
  $stmt->execute([$postType, $postId, $userId, $text]);


  if ($stmt->rowCount() === 0) {
    echo json_encode(['success' => false, 'error' => 'Insert failed']);
    exit;
  }

  $commentId = $pdo->lastInsertId();
  $user = $_SESSION['currentUser'];
  $first = trim($user['first_name'] ?? '');
  $last = trim($user['last_name'] ?? '');
  $userName = htmlspecialchars(($first || $last) ? "$first $last" : 'Anonymous');
  $avatar = strtoupper(substr($userName, 0, 1));

  // ✅ Safe notification block
  try {
    $postTable = $postType . '_posts';
    $stmt = $pdo->prepare("SELECT created_by AS user_id, title FROM {$postTable} WHERE id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post && $post['user_id'] != $userId) {
      $postOwnerId = $post['user_id'];
      $postTitle = $post['title'] ?? 'your post';
      $initials = strtoupper(substr($userName, 0, 2));
      $avatarColor = 'info';

      $message = 'commented on your post: "' . htmlspecialchars($postTitle, ENT_QUOTES) . '"';

      $notify = $pdo->prepare("
    INSERT INTO notifications (
        user_id,
        sender_name,
        message,
        avatar_color,
        initials,
        type,
        post_id,
        post_type
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

      $success = $notify->execute([
        $postOwnerId,   // recipient of the notification
        $userName,      // sender name
        $message,       // e.g. "$userName commented on your post"
        $avatarColor,   // sender's avatar color
        $initials,      // sender's initials
        'comment',      // type of notification
        $postId,        // ID of the post being interacted with
        $postType       // dynamic: 'community', 'event', 'job', etc.
      ]);


      if (!$success) {
        error_log("❌ Notification insert failed for post ID $postId by user $userId");
        error_log("❌ PDO error: " . implode(" | ", $notify->errorInfo()));
      }
    }
  } catch (PDOException $e) {
    error_log("Notification insert failed: " . $e->getMessage());
  }


  // ✅ Build comment HTML
  $commentHTML = '
    <div class="d-flex gap-2 mb-3 comment-card" data-comment-id="' . $commentId . '">
      <div class="avatar comment-avatar bg-secondary">' . $avatar . '</div>
      <div class="w-100">
        <div class="d-flex justify-content-between">
          <div>
            <strong>' . $userName . '</strong>
            <small class="text-muted"> • just now</small>
          </div>
          <div class="dropdown">
            <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <button class="dropdown-item btn-edit-comment"
                        data-comment-id="' . $commentId . '"
                        data-comment-text="' . htmlspecialchars($text, ENT_QUOTES) . '"
                        data-post-index="' . $postId . '">
                  <i class="bi bi-pencil me-2"></i>Edit
                </button>
              </li>
              <li>
                <button class="dropdown-item text-danger btn-delete-comment"
                        data-comment-id="' . $commentId . '">
                  <i class="bi bi-trash me-2"></i>Delete
                </button>
              </li>
            </ul>
          </div>
        </div>
        <p class="mb-0 comment-text">' . htmlspecialchars($text) . '</p>
      </div>
    </div>';

  echo json_encode(['success' => true, 'html' => $commentHTML]);
} catch (PDOException $e) {
  error_log("Database error: " . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Database error']);
}
