<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['currentUser'])) {
  header("Location: login.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $sender = $_SESSION['currentUser']['first_name'] . ' ' . $_SESSION['currentUser']['last_name'];
  $receiver = $_POST['receiver'];
  $item_id = $_POST['item_id'];
  $message = trim($_POST['message']);

  if (!empty($message)) {
    $stmt = $pdo->prepare("INSERT INTO messages (item_id, sender, receiver, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$item_id, $sender, $receiver, $message]);
    header("Location: lost_found.php?msg=sent");
    exit;
  } else {
    header("Location: lost_found.php?msg=empty");
    exit;
  }
}
?>