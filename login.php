<?php
<<<<<<< HEAD
session_start();
require_once "db.php";
=======
session_start();         
require_once "db.php"; 
include "navbar.php"; 
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role 
                            FROM users WHERE email = ?");
=======
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password_hash, role FROM users WHERE email = ?");
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

<<<<<<< HEAD
        // Check password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['first_name'] . " " . $user['last_name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin-dashboard.php");
=======
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['first_name'] . " " . $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: dashboard.php");
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
            } else {
                header("Location: dashboard.php");
            }
            exit;
<<<<<<< HEAD

=======
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
        } else {
            $message = "<div class='alert alert-danger'>❌ Incorrect password!</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>❌ No account found with that email!</div>";
    }
    $stmt->close();
}
?>

<<<<<<< HEAD


=======
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AgoraBoard</title>
<<<<<<< HEAD
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .auth-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <i class="fas fa-bullhorn me-2"></i>AgoraBoard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#announcements">Announcements</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#events">Events</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#categories">Categories</a></li>
                </ul>
                <div class="d-flex">
                    <a href="register.php" class="btn btn-outline-primary me-2"><i class="fas fa-user-plus me-1"></i>Register</a>
                    <a href="welcome-page.php" class="btn btn-outline-primary">Home</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="auth-container d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="auth-card p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-dark">Welcome Back</h2>
                            <p class="text-muted">Sign in to your AgoraBoard account</p>
                        </div>

                        <!-- Show messages -->
                        <?php if (!empty($message)) echo $message; ?>

                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember">
                                    <label class="form-check-label text-muted" for="remember">Remember me</label>
                                </div>
                                <a href="forgot.php" class="text-primary text-decoration-none">Forgot Password?</a>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="fas fa-sign-in-alt me-2"></i>Sign In
                            </button>
                        </form>

                        <div class="text-center mt-4">
                            <p class="text-muted">Don't have an account? 
                                <a href="register.php" class="text-primary text-decoration-none fw-semibold">Create one here</a>
                            </p>
                        </div>
                    </div>
=======
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
            background-color: rgba(255,255,255,0.8);
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

        .auth-card {
            backdrop-filter: blur(12px);
            background-color: rgba(255,255,255,0.8);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
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
        .feature-card:hover .feature-icon { transform: scale(1.1); }
        .bg-emerald { background: linear-gradient(135deg, var(--emerald-500), var(--emerald-600)); }
    </style>
</head>
<body>


<!-- Login Form -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="auth-card p-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold gradient-text">Welcome Back</h2>
                    <p class="text-muted">Sign in to your AgoraBoard account</p>
                </div>

                <!-- Display Messages -->
                <?php if ($message) echo $message; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remember">
                            <label class="form-check-label text-muted" for="remember">Remember me</label>
                        </div>
                        <a href="#" class="gradient-text text-decoration-none">Forgot Password?</a>
                    </div>

                    <button type="submit" class="gradient-btn w-100 py-2 fw-semibold">Sign In</button>
                </form>

                <div class="text-center mt-4">
                    <p class="text-muted">Don’t have an account? <a href="register.php" class="gradient-text">Register now</a></p>
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
                </div>
            </div>
        </div>
    </div>
<<<<<<< HEAD

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-3">AgoraBoard</h5>
                    <p class="text-muted">Connecting communities through modern digital communication.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">&copy; 2025 AgoraBoard. All rights reserved.</p>
                    <small class="text-muted">Developed by Grace Mae, Hendria & Nadine</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
=======
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
</html>
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
