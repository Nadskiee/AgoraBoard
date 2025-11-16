<?php
session_start();

// Initialize default settings if not set
if (!isset($_SESSION['settings'])) {
    $_SESSION['settings'] = [
        'username' => 'User',
        'email' => 'user@example.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT), // Default password
        'notifications' => true,
        'newsletter' => false,
        'theme' => 'light',
        'language' => 'en',
        'timezone' => 'UTC'
    ];
}

$settings = $_SESSION['settings']; // Get settings *before* any POST processing
$success_message = null;
$error_message = null; // Variable for error messages

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- Combined & Fixed Save Logic ---
    
    $new_password = $_POST['new_password'] ?? '';
    $current_password_input = $_POST['current_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 1. Update general (non-password) settings first
    $_SESSION['settings']['username'] = $_POST['username'] ?? $settings['username'];
    $_SESSION['settings']['email'] = $_POST['email'] ?? $settings['email'];
    $_SESSION['settings']['notifications'] = isset($_POST['notifications']);
    $_SESSION['settings']['newsletter'] = isset($_POST['newsletter']);
    $_SESSION['settings']['theme'] = $_POST['theme'] ?? $settings['theme'];
    $_SESSION['settings']['language'] = $_POST['language'] ?? $settings['language'];
    $_SESSION['settings']['timezone'] = $_POST['timezone'] ?? $settings['timezone'];

    $password_updated_successfully = false;
    $general_settings_saved = true; // Assume success unless password fails

    // 2. Check if user is trying to update password (i.e., any password field is filled)
    if (!empty($new_password) || !empty($current_password_input) || !empty($confirm_password)) {
        
        // 2a. Check if current password matches
        if (password_verify($current_password_input, $settings['password'])) {
            // 2b. Check if new passwords are not empty and match
            if (!empty($new_password) && $new_password === $confirm_password) {
                // 2c. Hash and update password in session
                $_SESSION['settings']['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                $password_updated_successfully = true;
            } elseif (empty($new_password)) {
                $error_message = "New password cannot be empty.";
                $general_settings_saved = false; // Error occurred
            } else {
                $error_message = "New passwords do not match. Please try again.";
                $general_settings_saved = false; // Error occurred
            }
        } else {
            $error_message = "Incorrect current password. Password was not updated.";
            $general_settings_saved = false; // Error occurred
        }
    }
    
    // 3. Set final success/error message
    if ($general_settings_saved) {
        if ($password_updated_successfully) {
            $success_message = "Settings and password saved successfully!";
        } else {
            $success_message = "Settings saved successfully!";
        }
    }
    
    // 4. Re-fetch all settings from session to display the updated values
    $settings = $_SESSION['settings'];
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
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            padding: 1.5rem 2rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .settings-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.07);
        }

        .section-title {
            font-size: 1.4em;
            font-weight: 600;
            color: var(--sage-dark);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
        }
        .section-title i {
            color: var(--sage);
            font-size: 1.1em;
            margin-right: 12px;
            padding-bottom: 2px;
        }

        .form-group {
            margin-bottom: 1.25rem;
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
            font-size: 1em;
            background: var(--bg);
            color: var(--dark-text);
            transition: all 0.3s ease;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: var(--sage);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }

        /* Bootstrap Switch Styling */
        .form-check-label {
            color: var(--dark-text);
            padding-top: 2px;
        }
        .form-check-input:checked {
            background-color: var(--sage);
            border-color: var(--sage-dark);
        }
        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
            border-color: var(--sage-dark);
        }

        /* Button Styling */
        .button-group {
            display: flex;
            gap: 15px;
        }
        .btn-save, .btn-reset {
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .btn-save {
            background: linear-gradient(135deg, var(--sage), var(--sage-dark));
            color: white;
        }
        .btn-save:hover {
            background: linear-gradient(135deg, var(--sage-light), var(--sage-dark));
            transform: translateY(-2px);
        }
        .btn-reset {
            background: var(--border-color);
            color: var(--dark-text);
        }
        .btn-reset:hover {
            background: #dcdad4;
        }

        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #bbf7d0;
        }
        
        /* Added Error Message Style */
        .error-message {
            background: #fef2f2;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
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

            <?php if (isset($success_message)): ?>
                <div class="success-message" id="successMessage">
                    ✓ <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error-message" id="errorMessage">
                    ✗ <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="row d-flex justify-content-center">
                <div class="col-lg-10">
                    <form method="POST" id="settingsForm">

                        <div class="settings-card mb-4">
                            <h2 class="section-title"><i class="bi bi-person-circle"></i>Account Information</h2>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($settings['username']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($settings['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="settings-card mb-4">
                            <h2 class="section-title"><i class="bi bi-shield-lock-fill"></i>Change Password</h2>
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" 
                                       placeholder="Enter your current password">
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" 
                                       placeholder="Enter a new password">
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                       placeholder="Confirm your new password">
                            </div>
                        </div>

                        <div class="settings-card mb-4">
                            <h2 class="section-title"><i class="bi bi-sliders"></i>Preferences</h2>
                            <div class="form-group">
                                <label for="theme">Theme</label>
                                <select id="theme" name="theme">
                                    <option value="light" <?php echo $settings['theme'] === 'light' ? 'selected' : ''; ?>>Light</option>
                                    <option value="dark" <?php echo $settings['theme'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                                    <option value="auto" <?php echo $settings['theme'] === 'auto' ? 'selected' : ''; ?>>Auto</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="language">Language</label>
                                <select id="language" name="language">
                                    <option value="en" <?php echo $settings['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                                    <option value="es" <?php echo $settings['language'] === 'es' ? 'selected' : ''; ?>>Español</option>
                                    <option value="fr" <?php echo $settings['language'] === 'fr' ? 'selected' : ''; ?>>Français</option>
                                    <option value="de" <?php echo $settings['language'] === 'de' ? 'selected' : ''; ?>>Deutsch</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="timezone">Timezone</label>
                                <select id="timezone" name="timezone">
                                    <option value="UTC" <?php echo $settings['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                    <option value="America/New_York" <?php echo $settings['timezone'] === 'America/New_York' ? 'selected' : ''; ?>>Eastern Time</option>
                                    <option value="America/Chicago" <?php echo $settings['timezone'] === 'America/Chicago' ? 'selected' : ''; ?>>Central Time</option>
                                    <option value="America/Los_Angeles" <?php echo $settings['timezone'] === 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time</option>
                                    <option value="Europe/London" <?php echo $settings['timezone'] === 'Europe/London' ? 'selected' : ''; ?>>London</option>
                                    <option value="Asia/Manila" <?php echo $settings['timezone'] === 'Asia/Manila' ? 'selected' : ''; ?>>Manila</option>
                                </select>
                            </div>
                        </div>

                        <div class="settings-card mb-4">
                            <h2 class="section-title"><i class="bi bi-bell-fill"></i>Notifications</h2>
                            <div class="form-check form-switch fs-5 mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="notifications" name="notifications" 
                                       <?php echo $settings['notifications'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="notifications">Enable notifications</label>
                            </div>
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" role="switch" id="newsletter" name="newsletter"
                                       <?php echo $settings['newsletter'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="newsletter">Subscribe to newsletter</labe>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mb-4 button-group">
                            <button type="reset" class="btn-reset">Reset</button>
                            <button type="submit" class="btn-save">Save Changes</button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function confirmLogout() {
            if (confirm("Are you sure you want to logout?")) {
                document.getElementById("logoutForm").submit();
            }
        }

        // --- Auto-hide alerts ---
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.transition = 'opacity 0.5s ease';
                successMessage.style.opacity = '0';
                setTimeout(() => {
                    successMessage.remove();
                }, 500);
            }, 3000); // 3 seconds
        }
        
        const errorMessage = document.getElementById('errorMessage');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.transition = 'opacity 0.5s ease';
                errorMessage.style.opacity = '0';
                setTimeout(() => {
                    errorMessage.remove();
                }, 500);
            }, 5000); // 5 seconds for errors
        }

        // --- Combined Form Validation ---
        const form = document.getElementById('settingsForm');
        form.addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const email = document.getElementById('email').value.trim();
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const currentPassword = document.getElementById('current_password').value;

            // 1. Username validation (from old file)
            if (username.length < 3) {
                e.preventDefault();
                alert('Username must be at least 3 characters long');
                return false;
            }

            // 2. Email validation (from old file)
            if (!email.includes('@') || email.length < 5) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return false;
            }
            
            // 3. Password validation (from new file, improved)
            // Only validate if user is trying to change password
            if (newPassword.length > 0 || confirmPassword.length > 0 || currentPassword.length > 0) {
                
                if (currentPassword.length === 0) {
                     e.preventDefault();
                     alert('Please enter your current password to set a new one.');
                     return false;
                }
                
                if (newPassword.length === 0) {
                     e.preventDefault();
                     alert('New password cannot be empty.');
                     return false;
                }

                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New passwords do not match. Please try again.');
                    return false;
                }
            }
        });
    </script>
</body>
</html>