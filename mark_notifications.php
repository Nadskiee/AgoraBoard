<?php
require 'db_connect.php';
$userId = $_SESSION['currentUser']['id'] ?? null;
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0")->execute([$userId]);

?>
