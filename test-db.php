<?php
include 'db.php';

echo "✅ Database connection successful!<br><br>";

// Query all users
$sql = "SELECT id, first_name, last_name, email FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"] . " - " 
           . $row["first_name"] . " " . $row["last_name"] 
           . " (" . $row["email"] . ")<br>";
    }
} else {
    echo "⚠️ No users found.";
}
?>
