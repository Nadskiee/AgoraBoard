<?php
require_once "db_connect.php";
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", time() + 3600); // 1 hour validity

        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->execute([$token, $expires, $email]);

        // Replace with your domain
        $resetLink = "http://yourdomain.com/reset_password.php?token=$token";

        // Send via PHP mail (for dev you can just echo)
        $subject = "Password Reset - AgoraBoard";
        $body = "Click the link below to reset your password:\n\n$resetLink\n\nThis link expires in 1 hour.";
        $headers = "From: no-reply@agoraboard.com";

        // mail($email, $subject, $body, $headers);
        $message = "<div class='alert alert-success'>📩 Reset link sent to your email.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Email not found.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="col-md-6 mx-auto bg-white p-5 rounded shadow">
        <h3 class="text-center mb-4">Forgot Password</h3>
        <?= $message ?>
        <form method="POST">
            <div class="mb-3">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button class="btn btn-success w-100">Send Reset Link</button>
        </form>
    </div>
</div>
</body>
</html>
