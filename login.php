<?php
session_start();
require_once "db_connect.php"; // This now defines $pdo (PDO)

$message = "";
$remembered_email = $_COOKIE['remembered_email'] ?? '';
$remember_checked = $remembered_email ? 'checked' : '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['support'])) {
    $support_email = trim($_POST['user_email'] ?? '');
    $support_name  = trim($_POST['user_name'] ?? '');
    $support_message = trim($_POST['message'] ?? '');

    if ($support_message) {
        $stmt = $pdo->prepare("INSERT INTO support_requests (user_email, user_name, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$support_email, $support_name, $support_message]);

        // Redirect to avoid resubmission
        header("Location: login.php?support_sent=1");
        exit;
    } else {
        $message = "<div class='alert alert-danger text-center'>⚠️ Please fill out the support message.</div>";
    }
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 🟢 FORGOT PASSWORD FLOW
    if (isset($_POST['forgot'])) {
        $forgotEmail = trim($_POST['forgot_email'] ?? '');

        if ($forgotEmail === '') {
            $message = "<div class='alert alert-warning'>⚠️ Please enter your email.</div>";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$forgotEmail]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Generate token and expiration
                $token = bin2hex(random_bytes(16));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                $stmt->execute([$token, $expires, $user['id']]);

                $resetLink = "http://localhost/AgoraBoard/reset_password.php?token=$token";

                // Try sending email
                require 'vendor/autoload.php';
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $emailSent = false;

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'cabparlove@gmail.com'; // Your Gmail
                    $mail->Password = 'tiag jdln ukhd yrzi';  // App password
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('cabparlove@gmail.com', 'AgoraBoard');
                    $mail->addAddress($forgotEmail);

                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset - AgoraBoard';
                    $mail->Body = "
                        <p>Hello,</p>
                        <p>We received a password reset request for your AgoraBoard account.</p>
                        <p><a href='$resetLink'>Click here to reset your password</a></p>
                        <p>This link will expire in 1 hour.</p>
                    ";

                    $mail->send();
                    $emailSent = true;
                } catch (Exception $e) {
                    $emailSent = false;
                }

                if ($emailSent) {
                    // Email sent successfully
                    $message = "<div class='alert alert-success'>📧 Reset link sent to your email!</div>";
                } else {
                    // PHPMailer failed → create temporary password
                    $tempPassword = bin2hex(random_bytes(4)); // 8-character temporary password
                    $hashedTempPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

                    // Save hashed temporary password to database
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$hashedTempPassword, $user['id']]);

                    $message = "
                        <div class='alert alert-warning'>
                            ⚠️ Email could not be sent. A temporary password has been generated:
                            <br><strong>Temporary Password:</strong> $tempPassword
                            <br>Please log in using this password and change it immediately.
                        </div>
                    ";
                }
            } else {
                $message = "<div class='alert alert-danger'>❌ No account found with that email.</div>";
            }
        }
    }
}

// Handle login form
if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($email === '' || $password === '') {
        $message = "<div class='alert alert-warning'>⚠️ Please enter both email and password.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password_hash, role, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // ❌ Check if banned
                if ($user['status'] === 'banned') {
                    $message = "
                        <div class='alert alert-danger text-center'>
                        ❌ Your account has been banned. 
                        Please contact support for assistance.
                        <br><br>
                        <button type='button' class='btn btn-danger' data-bs-toggle='modal' data-bs-target='#supportModal'>
                            <i class='fas fa-envelope me-1'></i> Contact Support
                        </button>
                        </div>";
                } elseif (password_verify($password, $user['password_hash'])) {
                    $_SESSION['currentUser'] = [
                        'id' => $user['id'],
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'initial' => strtoupper(substr($user['first_name'], 0, 1)),
                        'name' => $user['first_name'] . ' ' . $user['last_name']
                    ];

                    // ✅ Store or clear remembered email
                    if ($remember) {
                        setcookie('remembered_email', $email, time() + (86400 * 30), "/"); // 30 days
                    } else {
                        setcookie('remembered_email', '', time() - 3600, "/"); // Clear cookie
                    }

                    $redirect = ($user['role'] === 'admin') ? "admin_dashboard.php" : "dashboard.php";
                    header("Location: $redirect");
                    exit;
                } else {
                    $message = "<div class='alert alert-danger'>❌ Incorrect email or password.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>❌ Incorrect email or password.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>❌ Login error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
include "navbar.php"; // Safe to include after redirect logic
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AgoraBoard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --emerald-50: #ecfdf5;
            --emerald-100: #d1fae5;
            --emerald-500: #10b981;
            --emerald-600: #059669;
            --emerald-700: #047857;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
        }

        body {
            background: linear-gradient(135deg, var(--emerald-50), var(--blue-50, #e0f2fe));
            font-family: 'Segoe UI', sans-serif;
        }

        .navbar {
            backdrop-filter: blur(12px);
            background-color: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(16, 185, 129, 0.2);
        }

        .gradient-text {
            background: linear-gradient(135deg, var(--emerald-600), var(--emerald-700));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .gradient-btn {
            background: linear-gradient(135deg, var(--emerald-600), var(--emerald-700));
            border: none;
            color: white;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.25);
        }

        .gradient-btn:hover {
            background: linear-gradient(135deg, var(--emerald-700), var(--emerald-600));
            color: white;
            transform: translateY(-2px);
        }

        .btn-outline-secondary {
            border-radius: 10px;
            border: 1.5px solid #d1d5db;
            color: #4b5563;
            background-color: white;
            font-weight: 500;
            padding: 0.6rem 1.25rem;
            height: 42px;
            /* matches gradient button height */
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease-in-out;
        }

        .btn-outline-secondary:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
            color: #374151;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .auth-card {
            backdrop-filter: blur(12px);
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1);
        }

        .bg-emerald {
            background: linear-gradient(135deg, var(--emerald-500), var(--emerald-600));
        }
    </style>
</head>

<body>
    <!-- Main Container -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold gradient-text">Welcome Back</h2>
                        <p class="text-muted">Sign in to your AgoraBoard account</p>
                    </div>

                    <?= $message ?>

                    <form method="POST">
                        <input type="hidden" name="login" value="1">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                value="<?= htmlspecialchars($remembered_email) ?>"
                                required autocomplete="username">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required autocomplete="current-password">

                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" <?= $remember_checked ?>>
                                <label class="form-check-label text-muted" for="remember">Remember me</label>
                            </div>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#forgotModal" class="gradient-text text-decoration-none">Forgot Password?</a>
                        </div>

                        <button type="submit" class="gradient-btn w-100 py-2 fw-semibold">Sign In</button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted">Don’t have an account? <a href="register.php" class="gradient-text">Register now</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content p-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title gradient-text">Forgot Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="forgot" value="1">
                    <div class="modal-body">
                        <label class="form-label">Enter your email address:</label>
                        <input type="email" name="forgot_email" class="form-control" required>
                    </div>
                    <div class="modal-footer border-0 d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-secondary px-4 py-2 fw-semibold" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="gradient-btn px-4 py-2 fw-semibold">
                            Send Reset Link
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
    <!-- Support Modal -->
    <div class="modal fade" id="supportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title gradient-text">Contact Support</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="support" value="1">

                    <div class="mb-3">
                        <label for="supportEmail" class="form-label">Your Email (optional)</label>
                        <input type="email" name="user_email" id="supportEmail" class="form-control"
                            value="<?= htmlspecialchars($email ?? $remembered_email ?? '') ?>"
                            placeholder="Enter your email for reply">
                    </div>

                    <div class="mb-3">
                        <label for="supportName" class="form-label">Your Name (optional)</label>
                        <input type="text" name="user_name" id="supportName" class="form-control"
                            value="<?= htmlspecialchars($_SESSION['currentUser']['name'] ?? '') ?>"
                            placeholder="Enter your name to help us identify you">
                    </div>

                    <div class="mb-3">
                        <label for="supportMessage" class="form-label">Message</label>
                        <textarea name="message" id="supportMessage" class="form-control" rows="6" required><?= htmlspecialchars(
    !empty($banned_reason)
        ? "Hello Support,\n\nMy account has been banned.\nReason (if known): $banned_reason\n\nPlease review and unban my account.\nThank you."
        : "Hello Support,\n\nMy account has been banned. Please review and unban my account.\nThank you."
) ?></textarea>

                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i> Send Message
                        </button>
                    </div>

                    <?php if (isset($_GET['support_sent']) && $_GET['support_sent'] == 1): ?>
                        <div class="alert alert-success text-center mt-3">
                            ✅ Your message has been sent to support. They will contact you soon.
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>


    <!-- Footer -->
    <footer class="border-top py-5" style="background: linear-gradient(to bottom, rgba(249,250,251,0.3), rgba(249,250,251,0.5));">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="fw-bold mb-2">AgoraBoard</h5>
                    <p class="text-muted small">Connecting communities through digital communication.</p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-semibold mb-2">Community</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="text-muted text-decoration-none">Guidelines</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-semibold mb-2">Support</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Contact Us</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Feedback</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-3">
            <div class="text-center">
                <p class="text-muted small mb-1">&copy; 2025 AgoraBoard. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</html>