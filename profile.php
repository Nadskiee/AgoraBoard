<?php
session_start();

// Redirect to index if user data isn't set (shouldn't happen if they came from index.php)
if (!isset($_SESSION['currentUser'])) {
    header("Location: dashboard.php");
    exit;
}

// --- Helper Function ---
function sane($s) {
    return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
}

$currentUser = $_SESSION['currentUser'];
$message = '';

// Handle Profile Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newName = sane($_POST['name']);
    $newEmail = sane($_POST['email']);
    
    if (empty($newName) || empty($newEmail)) {
        $message = '<div class="alert alert-danger">Name and Email cannot be empty.</div>';
    } else {
        // Find and update the user's name in posts and comments
        $oldName = $currentUser['name'];
        $oldInitial = $currentUser['initial'];

        if ($newName !== $oldName) {
            // Update posts
            foreach ($_SESSION['posts'] as $i => $post) {
                if ($post['user'] === $oldName) {
                    $_SESSION['posts'][$i]['user'] = $newName;
                }
                // Update likedBy
                $likedByIndex = array_search($oldName, $_SESSION['posts'][$i]['likedBy'] ?? []);
                if ($likedByIndex !== false) {
                    $_SESSION['posts'][$i]['likedBy'][$likedByIndex] = $newName;
                }
            }

            // Update comments
            foreach ($_SESSION['comments'] as $p_idx => $comments) {
                foreach ($comments as $c_idx => $comment) {
                    if ($comment['user'] === $oldName) {
                        $_SESSION['comments'][$p_idx][$c_idx]['user'] = $newName;
                    }
                }
            }
        }
        
        // Update the current user's session data
        $_SESSION['currentUser']['name'] = $newName;
        $_SESSION['currentUser']['email'] = $newEmail;
        $_SESSION['currentUser']['initial'] = strtoupper(substr($newName, 0, 1));
        
        // Re-load the updated user data
        $currentUser = $_SESSION['currentUser'];

        $message = '<div class="alert alert-success">Profile updated successfully! All your posts and comments now reflect your new name.</div>';
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AgoraBoard - Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sage: #10b981; 
            --sage-light: #059669; 
            --bg: #FDFDFC; 
        }
        body { background-color: var(--bg); }
        .container { max-width: 600px; margin-top: 50px; }
        .avatar-lg { width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; background-color: var(--sage); font-weight: 700; font-size: 3rem; margin: 0 auto 20px; }
        .btn-sage { background-color: var(--sage); border-color: var(--sage); color: #fff; }
        .btn-sage:hover { background-color: var(--sage-light); border-color: var(--sage-light); color: #fff; }
    </style>
</head>
<body>

<div class="container">
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <a href="dashboard.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        </div>
        <div class="card-body p-4">
            <h2 class="card-title text-center mb-4">Edit Profile</h2>
            
            <?= $message; ?>

            <div class="avatar-lg"><?= sane($currentUser['initial']); ?></div>
            
            <form method="POST" action="">
                <input type="hidden" name="update_profile" value="1">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= sane($currentUser['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= sane($currentUser['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <input type="text" class="form-control" id="role" value="<?= sane($currentUser['role']); ?>" disabled>
                    <div class="form-text">Your role cannot be changed here.</div>
                </div>
                
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-lg btn-sage">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>