<?php
session_start();
require_once 'db_connect.php';

// 🛡️ Ensure user is logged in
if (!isset($_SESSION['currentUser'])) {
    header("Location: login.php");
    exit;
}

$currentUser = $_SESSION['currentUser'];
$currentUserId = $currentUser['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $title = trim($_POST['title'] ?? '');
    $employer = trim($_POST['employer'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $contact_info = trim($_POST['contact_info'] ?? '');

    // Simple validation
    if (empty($title) || empty($employer) || empty($description) || empty($contact_info)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: dashboard.php");
        exit;
    }

    try {
        // Prepare insert statement
        $stmt = $pdo->prepare("INSERT INTO jobs (title, employer, description, contact_info, posted_by, created_at) 
                               VALUES (:title, :employer, :description, :contact_info, :posted_by, NOW())");

        $stmt->execute([
            ':title' => $title,
            ':employer' => $employer,
            ':description' => $description,
            ':contact_info' => $contact_info,
            ':posted_by' => $currentUserId
        ]);

        $_SESSION['success'] = "Job successfully created!";
        header("Location: jobs.php");
        exit;

    } catch (PDOException $e) {
        // Optional: log error $e->getMessage()
        $_SESSION['error'] = "Failed to create job. Please try again.";
        header("Location: jobs.php");
        exit;
    }
} else {
    // Invalid request method
    header("Location: jobs.php");
    exit;
}
?>
