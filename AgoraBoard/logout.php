<?php
// logout.php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to index.php
header("Location: index.php");
exit;
