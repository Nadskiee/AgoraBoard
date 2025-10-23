<?php
require_once "db_connect.php";
require 'vendor/autoload.php'; // ✅ PHPMailer autoload
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();


function generateResetToken($pdo, $email)
{
    $token = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
    $stmt->execute([$token, $expires, $email]);

    // ✅ Send new email using PHPMailer
    $resetLink = "http://localhost/AgoraBoard/reset_password.php?token=$token";
    $subject = "New Password Reset Link - AgoraBoard";
    $body = "Hello,<br><br>Your previous password reset link expired.<br><br>
    Click below to reset your password:<br><br>
    <a href='$resetLink'>$resetLink</a><br><br>
    This link will expire in 1 hour.";

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp-relay.brevo.com';
        $mail->SMTPAuth = true;
        $mail->Username = '99c912001@smtp-brevo.com';
        $mail->Password = 'xsmtpsib-66e3870aa0f3c7eb341e78f1cfcc494ad68302a28b01e74700d86ea6187099e5-S3DcAb4zoypR8uHX';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('cabparlove@gmail.com', 'AgoraBoard'); // must be verified in Brevo
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'html';

        $mail->send();
    } catch (Exception $e) {
        file_put_contents('smtp_debug.log', date('Y-m-d H:i:s') . " Mailer Error: " . $mail->ErrorInfo . "\n", FILE_APPEND);
    }
}

$token = $_GET['token'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Token valid → reset password
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$hash, $user['id']]);
        $message = "<div class='alert alert-success'>✅ Password has been reset! You can now <a href='login.php'>login</a>.</div>";
    } else {
        // Token invalid or expired — issue a new one automatically
        $stmt = $pdo->prepare("SELECT email FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            generateResetToken($pdo, $row['email']);
            $message = "<div class='alert alert-warning'>
                ⚠️ Your previous link expired. A new reset link has been sent to your email.
            </div>";
        } else {
            $message = "<div class='alert alert-danger'>❌ Invalid reset link.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password - AgoraBoard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card p-4 shadow-sm">
                    <h4 class="mb-3 text-center">Reset Your Password</h4>
                    <?= $message ?>
                    <?php if (empty($message) || str_contains($message, 'expired')): ?>
                        <form method="POST">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-success w-100">Reset Password</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>