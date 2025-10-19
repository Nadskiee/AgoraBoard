<?php
$servername = "localhost";
$username   = "root"; // default user sa XAMPP
$password   = "";     // default walay password
$dbname     = "agoraboard_db"; // exact name sa imong database

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}
?>
