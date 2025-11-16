<?php
session_start();

// --- Helper Function ---
function sane($s)
{
    return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
}

// Redirect to dashboard if user data isn't set
if (!isset($_SESSION['currentUser'])) {
    header("Location: dashboard.php");
    exit;
}

$currentUser = $_SESSION['currentUser'];
$message = '';
$message_type = 'success';

// --- DEMO DATA ---
// Add placeholders for new fields if they don't exist
if (!isset($currentUser['phone'])) $currentUser['phone'] = '+1 (555) 123-4567';
if (!isset($currentUser['location'])) $currentUser['location'] = 'San Francisco, CA';
if (!isset($currentUser['bio'])) $currentUser['bio'] = 'Product designer passionate about creating intuitive user experiences.';
if (!isset($currentUser['memberSince'])) $currentUser['memberSince'] = 'January 2024';
// Ensure 'initial' is set, e.g., from the first letter of the name
$nameParts = explode(' ', $currentUser['name'], 2);
$currentUser['initial'] = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

$_SESSION['currentUser'] = $currentUser; // Save back to session

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_changes'])) {
    // --- THIS IS A DEMO ---
    // In a real app, you would validate data and update a database here.
    // For this example, we'll just update the session data.

    $name = sane($_POST['fullName']);
    $email = sane($_POST['email']);
    $phone = sane($_POST['phone']);
    $location = sane($_POST['location']);
    $bio = sane($_POST['bio']);

    if (!empty($name) && !empty($email)) {
        // Update session data
        $_SESSION['currentUser']['name'] = $name;
        $_SESSION['currentUser']['email'] = $email;
        $_SESSION['currentUser']['phone'] = $phone;
        $_SESSION['currentUser']['location'] = $location;
        $_SESSION['currentUser']['bio'] = $bio;

        // Update the $currentUser var for the current page load
        $currentUser = $_SESSION['currentUser'];

        $message = "Profile updated successfully!";
        $message_type = 'success';
    } else {
        $message = "Full Name and Email are required.";
        $message_type = 'danger';
    }
}

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgoraBoard - Profile Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sage-dark: #059669;
            --sage-light-hover: #d1fae5;
            --bg: #f9fafb;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border-light: #e5e7eb;
            --card-bg: #ffffff;
        }

        body {
            background-color: var(--bg);
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
        }

        /* Back button */
        .btn-back {
            background-color: var(--card-bg);
            border: 1px solid var(--border-light);
            color: var(--text-dark);
            font-weight: 500;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-back:hover {
            background-color: #f3f4f6;
        }

        /* Card styles */
        .profile-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-light);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03), 0 1px 2px rgba(0, 0, 0, 0.03);
            margin-top: 1.5rem;
        }

        /* Top Card: Profile Settings */
        .profile-header-card {
            padding: 2rem;
        }

        .profile-header-card h1 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .profile-header-card>p {
            color: var(--text-light);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .profile-user-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .avatar-container {
            position: relative;
            flex-shrink: 0;
        }

        .avatar-lg {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sage-dark);
            background-color: var(--sage-light-hover);
            font-weight: 600;
            font-size: 2.25rem;
        }

        .avatar-edit-icon {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 28px;
            height: 28px;
            background-color: var(--card-bg);
            color: var(--text-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            border: 1px solid var(--border-light);
            cursor: pointer;
            transition: all 0.2s;
        }

        .avatar-edit-icon:hover {
            color: var(--text-dark);
            border-color: #d1d5db;
        }

        .user-details h2 {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
        }

        .user-details p {
            margin: 0;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Bottom Card: Personal Info */
        .personal-info-card {
            /* No padding, header/footer will have it */
        }

        .personal-info-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-light);
        }

        .personal-info-header h2 {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0;
        }

        .personal-info-header p {
            color: var(--text-light);
            margin: 0;
            font-size: 0.9rem;
        }

        .personal-info-body {
            padding: 2rem;
        }

        .personal-info-footer {
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
            background-color: #f9fafb;
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--border-light);
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }

        /* Form styles */
        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .input-group .form-control {
            border-left: 0;
            padding-left: 0.5rem;
        }

        .input-group-text {
            background-color: var(--card-bg);
            border-right: 0;
            color: var(--text-light);
        }

        .form-control,
        .input-group-text {
            border-radius: 8px;
            border: 1px solid var(--border-light);
            padding: 0.6rem 0.9rem;
        }

        /* Fix for BS5 radius */
        .input-group> :not(:first-child) {
            border-top-left-radius: 8px;
            border-bottom-left-radius: 8px;
        }

        .input-group> :not(:last-child) {
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }

        .input-group:focus-within .form-control,
        .input-group:focus-within .input-group-text {
            border-color: var(--sage-dark);
            box-shadow: 0 0 0 2px var(--sage-light-hover);
        }

        .form-control:focus {
            border-color: var(--sage-dark);
            box-shadow: 0 0 0 2px var(--sage-light-hover);
        }

        .form-control:disabled {
            background-color: #f3f4f6;
            color: var(--text-light);
        }

        textarea.form-control {
            min-height: 120px;
        }

        .char-count {
            font-size: 0.8rem;
            color: var(--text-light);
            text-align: right;
            margin-top: 0.25rem;
        }

        .form-text {
            font-size: 0.875rem;
        }

        /* Buttons */
        .btn-save {
            background-color: var(--sage-dark);
            border-color: var(--sage-dark);
            color: #fff;
            font-weight: 500;
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-save:hover {
            background-color: #047857;
            border-color: #047857;
            color: #fff;
        }

        .btn-cancel {
            background-color: var(--card-bg);
            border: 1px solid var(--border-light);
            color: var(--text-dark);
            font-weight: 500;
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .btn-cancel:hover {
            background-color: #f3f4f6;
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- Back to Dashboard Link -->
        <a href="dashboard.php" class="btn-back"><i class="bi bi-arrow-left me-1"></i> Back</a>

        <!-- Success/Error Message -->
        <?php if ($message): ?>
            <div class="alert alert-<?= sane($message_type); ?> alert-dismissible fade show mt-3" role="alert">
                <?= sane($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Top Card: Profile Settings -->
        <div class="profile-card profile-header-card">
            <h1>Profile Settings</h1>
            <p>Manage your personal information and preferences</p>

            <div class="profile-user-info">
                <div class="avatar-container">
                    <div class="avatar-lg"><?= sane($currentUser['initial']); ?></div>
                    <a href="#" class="avatar-edit-icon" title="Change Photo">
                        <i class="bi bi-camera-fill"></i>
                    </a>
                </div>
                <div class="user-details">
                    <h2><?= sane($currentUser['name']); ?></h2>
                    <p><?= sane($currentUser['email']); ?></p>
                    <p>Member since <?= sane($currentUser['memberSince']); ?></p>
                </div>
            </div>
        </div>

        <!-- Bottom Card: Personal Information Form -->
        <form action="profile.php" method="POST">
            <div class="profile-card personal-info-card">
                <div class="personal-info-header">
                    <h2>Personal Information</h2>
                    <p>Update your personal details</p>
                </div>

                <div class="personal-info-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fullName" class="form-label">Full Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="fullName" name="fullName" value="<?= sane($currentUser['name']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" value="<?= sane($currentUser['email']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-phone"></i></span>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?= sane($currentUser['phone']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" class="form-control" id="location" name="location" value="<?= sane($currentUser['location']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4" maxlength="200"><?= sane($currentUser['bio']); ?></textarea>
                        <div id="charCount" class="char-count">0 / 200 characters</div>
                    </div>

                    <div class="mb-3">
                        <label for="accountRole" class="form-label">Account Role</label>
                        <input type="text" class="form-control" id="accountRole" name="accountRole" value="<?= sane($currentUser['role']); ?>" disabled>
                        <div class="form-text">Contact support to change your account role</div>
                    </div>
                </div>

                <div class="personal-info-footer">
                    <!-- Cancel button could redirect back or clear changes via JS -->
                    <a href="profile.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" name="save_changes" class="btn btn-save">Save Changes</button>
                </div>
            </div>
        </form>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character counter for Bio
        const bioTextarea = document.getElementById('bio');
        const charCountEl = document.getElementById('charCount');
        const maxChars = 200;

        function updateCharCount() {
            const currentLength = bioTextarea.value.length;
            charCountEl.textContent = `${currentLength} / ${maxChars} characters`;
        }

        // Initial count on page load
        updateCharCount();

        // Update count on input
        bioTextarea.addEventListener('input', updateCharCount);
    </script>
</body>

</html>