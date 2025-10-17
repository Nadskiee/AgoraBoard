<<<<<<< HEAD
<?php
=======
 <?php
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
$servername = "localhost";
$username   = "root";      // default user sa XAMPP
$password   = "";          // default walay password
$dbname     = "agoraboard_db"; // exact name sa imong database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
<<<<<<< HEAD
?>
=======
?>
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
