<?php
session_start();
require_once 'db_connect.php';

if (!isset($_POST['poll_id'])) exit;

$pollId = $_POST['poll_id'];
$userId = $_SESSION['currentUser']['id'];

// Soft delete only if poll belongs to the user
$stmt = $pdo->prepare("
    UPDATE polls 
    SET deleted_at = NOW() 
    WHERE id = ? AND created_by = ?
");
$stmt->execute([$pollId, $userId]);

echo "OK";
