<?php
session_start();
<<<<<<< HEAD
require_once "db.php";

$message = "";
$adminSecret = "SECRET123"; // 🔑 Secret admin password
=======
require_once("db.php");
include "navbar.php";

$message = "";
$adminSecret = "SECRET123";
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
<<<<<<< HEAD
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

=======
    $username = trim($_POST['username'] ?? null); // Optional
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $secretKey = trim($_POST['secretKey'] ?? '');

    // Validation
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "<div class='alert alert-danger'>❌ Invalid email format!</div>";
    } elseif ($password !== $confirmPassword) {
        $message = "<div class='alert alert-danger'>❌ Passwords do not match!</div>";
    } else {
<<<<<<< HEAD
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "<div class='alert alert-warning'>⚠️ Email already registered!</div>";
        } else {
            // Default role = community
            $role = "community";

            // If password is same as admin secret, set as admin
            if ($password === $adminSecret) {
                $role = "admin";
            }

            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>
                                ✅ Registration successful! 
                                <a href='login.php'>Login now</a>.
                            </div>";
            } else {
                $message = "<div class='alert alert-danger'>❌ Error: " . htmlspecialchars($conn->error) . "</div>";
            }
            $stmt->close();
        }
        $check->close();
=======
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Determine role
        $role = ($secretKey === $adminSecret) ? "admin" : "user";

        // Insert into database using NULL if username is empty
        $stmt = $conn->prepare(
            "INSERT INTO users (first_name, last_name, username, email, password_hash, role) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        // If username is empty, set it to NULL
        if (empty($username)) $username = null;

        $stmt->bind_param("ssssss", $firstName, $lastName, $username, $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            // Set session
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $username;
            $_SESSION['name'] = $firstName . " " . $lastName;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role;

            // Redirect based on role
            if ($role === 'admin') {
                header("Location: dashboard.php");
            } else {
                header("Location: main.php");
            }
            exit;
        } else {
            $message = "<div class='alert alert-danger'>❌ Error: " . htmlspecialchars($conn->error) . "</div>";
        }
        $stmt->close();
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
    }
}
?>

<<<<<<< HEAD

<!DOCTYPE html>
<html lang="en">
=======
<!DOCTYPE html>
<html lang="en">

>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AgoraBoard</title>
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
                    <a href="login.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Register Form -->
    <div class="auth-container d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="auth-card p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-dark">Join AgoraBoard</h2>
                            <p class="text-muted">Create your community account</p>
                        </div>

                        <!-- ✅ Display messages -->
                        <?php if (!empty($message)) echo $message; ?>

                        <form method="POST" action="register.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label fw-semibold">First Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" name="firstName" id="firstName" placeholder="First name" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label fw-semibold">Last Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" name="lastName" id="lastName" placeholder="Last name" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" name="password" id="password" placeholder="Create a password" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label fw-semibold">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" placeholder="Confirm your password" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label text-muted" for="terms">
                                        I agree to the <a href="#" class="text-primary text-decoration-none">Terms of Service</a> and <a href="#" class="text-primary text-decoration-none">Privacy Policy</a>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted">Already have an account? 
                                <a href="login.php" class="text-primary text-decoration-none fw-semibold">Sign in here</a>
                            </p>
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

        .auth-card {
            backdrop-filter: blur(12px);
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>

    <!-- Registration Form -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold gradient-text">Join AgoraBoard</h2>
                        <p class="text-muted">Create your community account</p>
                    </div>

                    <!-- Display Messages -->
                    <?php if ($message) echo $message; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="firstName" class="form-control" placeholder="First name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="lastName" class="form-control" placeholder="Last name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirmPassword" class="form-control" placeholder="Confirm password" required>
                        </div>

                        <!-- Admin Secret Key -->
                        <div class="mb-3">
                            <label class="form-label">Admin Secret Key (Optional)</label>
                            <input type="text" name="secretKey" class="form-control" placeholder="Enter secret key if admin">
                        </div>

                        <button type="submit" class="gradient-btn w-100 py-2 fw-semibold">Create Account</button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted">Already have an account? <a href="login.php" class="gradient-text">Sign in</a></p>
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
<<<<<<< HEAD
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="fw-bold mb-3">AgoraBoard</h5>
                    <p class="text-muted">Connecting communities through modern digital communication.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted mb-0">&copy; 2025 AgoraBoard. All rights reserved.</p>
                    <small class="text-muted">Developed by Grace Mae,  Hendria & Nadine</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
=======
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
