<?php
require 'db_connect.php';
$userId = $_SESSION['currentUser']['id'] ?? null;
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$userId]);
?>
