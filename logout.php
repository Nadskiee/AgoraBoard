<?php
// logout.php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();
header("Location: login.php");
exit;
    