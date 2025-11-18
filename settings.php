<?php
session_start();
require_once 'db_connect.php'; // Make sure this initializes $pdo

// Ensure user is logged in
if (!isset($_SESSION['currentUser'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['currentUser']['id'];
$message = '';
$alertClass = 'info';

// Fetch current user info
$stmt = $pdo->prepare("SELECT username, email, password_hash FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle Account Info Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_account'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (strlen($username) < 3 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid username and email.";
        $alertClass = "danger";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$username, $email, $userId]);
        $message = "Account information updated successfully!";
        $alertClass = "success";
        $user['username'] = $username;
        $user['email'] = $email;
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$current || !$new || !$confirm) {
        $message = 'All password fields are required.';
        $alertClass = 'danger';
    } elseif ($new !== $confirm) {
        $message = 'New passwords do not match.';
        $alertClass = 'danger';
    } elseif (!password_verify($current, $user['password_hash'])) {
        $message = 'Current password is incorrect.';
        $alertClass = 'danger';
    } else {
        $newHash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$newHash, $userId]);
        $message = 'Password updated successfully.';
        $alertClass = 'success';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - AgoraBoard</title>
    <link rel="stylesheet" href="assets/dashboard.css?v=<?php echo time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sage: #10b981;
            --sage-light: #059669;
            --sage-dark: #047857;
            --cream: #f5f5f0;
            --bg: #fdfdfc;
            --muted-text: #6c757d;
            --dark-text: #3b3a36;
            --border-color: #eae8e3;
        }

        body {
            background-color: var(--bg);
        }

        .page-header h1 {
            font-weight: 700;
            color: var(--sage-dark);
        }

        .page-header p {
            color: var(--muted-text);
            font-size: 1.1rem;
        }

        .settings-card {
            background: var(--cream);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem 2rem;
            margin-bottom: 20px;
        }

        /* Add this to your <style> section */
        .settings-card .d-flex.gap-2 {
            margin-top: 1.5rem;
            /* adds space between the last input and the button */
        }


        .section-title {
            font-size: 1.4em;
            font-weight: 600;
            color: var(--sage-dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .section-title i {
            color: var(--sage);
            margin-right: 10px;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark-text);
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg);
            color: var(--dark-text);
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--sage);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }

        .form-check-label {
            color: var(--dark-text);
        }

        .form-check-input:checked {
            background-color: var(--sage);
            border-color: var(--sage-dark);
        }

        .btn-save {
            background: linear-gradient(135deg, var(--sage), var(--sage-dark));
            color: white;
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 25px;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, var(--sage-light), var(--sage-dark));
        }

        .btn-reset {
            background: var(--border-color);
            color: var(--dark-text);
            border-radius: 8px;
            padding: 10px 25px;
        }

        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #bbf7d0;
        }

        .error-message {
            background: #fef2f2;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }
    </style>
</head>

<body>
    <div class="dashboard-layout d-flex">
        <?php include 'user_sidebar.php'; ?>
        <div class="main-content flex-grow-1 p-4">
            <div class="page-header mb-4">
                <h1>⚙️ Settings</h1>
                <p>Manage your account preferences</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?= $alertClass ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <!-- ACCOUNT INFO FORM -->
                    <form method="POST">
                        <input type="hidden" name="update_account" value="1">
                        <div class="settings-card">
                            <h3 class="section-title"><i class="bi bi-person-circle"></i>Account Information</h3>
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            <div class="d-flex gap-2 justify-content-end">
                                <button class="btn-save" type="submit">Save Account</button>
                            </div>
                        </div>
                    </form>

                    <!-- CHANGE PASSWORD FORM -->
                    <form method="POST">
                        <input type="hidden" name="change_password" value="1">
                        <div class="settings-card">
                            <h3 class="section-title"><i class="bi bi-shield-lock-fill"></i>Change Password</h3>
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                            <div class="d-flex gap-2 justify-content-end">
                                <button class="btn-save" type="submit">Change Password</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // --- Auto-hide alerts ---
        const successMessage = document.querySelector('.success-message');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.transition = 'opacity 0.5s ease';
                successMessage.style.opacity = '0';
                setTimeout(() => successMessage.remove(), 500);
            }, 3000);
        }

        const errorMessage = document.querySelector('.error-message');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.transition = 'opacity 0.5s ease';
                errorMessage.style.opacity = '0';
                setTimeout(() => errorMessage.remove(), 500);
            }, 5000);
        }

        // --- ACCOUNT INFO FORM VALIDATION ---
        const accountForm = document.querySelector('form input[name="action"][value="update_account"]').closest('form');
        accountForm.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();

            if (username.length < 3) {
                e.preventDefault();
                alert('Username must be at least 3 characters long.');
                return false;
            }

            if (!email.includes('@') || email.length < 5) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return false;
            }
        });

        // --- PREFERENCES FORM VALIDATION ---
        const preferencesForm = document.querySelector('form input[name="action"][value="update_preferences"]').closest('form');
        preferencesForm.addEventListener('submit', function(e) {
            const timezone = document.getElementById('timezone').value;
            if (!timezone) {
                e.preventDefault();
                alert('Please select a valid timezone.');
                return false;
            }
        });

        // --- CHANGE PASSWORD FORM VALIDATION ---
        const passwordForm = document.querySelector('form input[name="action"][value="change_password"]').closest('form');
        passwordForm.addEventListener('submit', function(e) {
            const currentPassword = document.getElementById('current_password').value.trim();
            const newPassword = document.getElementById('new_password').value.trim();
            const confirmPassword = document.getElementById('confirm_password').value.trim();
            const tempToken = document.querySelector('input[name="temp_token"]').value.trim();

            if (newPassword.length === 0) {
                e.preventDefault();
                alert('New password cannot be empty.');
                return false;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match.');
                return false;
            }

            if (currentPassword.length === 0 && tempToken.length === 0) {
                e.preventDefault();
                alert('Enter your current password or use a temporary token.');
                return false;
            }

            if (newPassword.length < 6) {
                e.preventDefault();
                alert('New password must be at least 6 characters long.');
                return false;
            }
        });
    </script>

</body>

</html>