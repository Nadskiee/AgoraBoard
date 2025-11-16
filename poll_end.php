<?php
session_start();
require_once 'db_connect.php';

if (!isset($_POST['poll_id'])) exit;

$pollId = $_POST['poll_id'];
$userId = $_SESSION['currentUser']['id'];

$stmt = $pdo->prepare("
    UPDATE polls 
    SET is_closed = 1
    WHERE id = ? AND created_by = ?
");
$stmt->execute([$pollId, $userId]);

echo "OK";
