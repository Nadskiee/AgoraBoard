<?php
// navbar.php
if (session_status() === PHP_SESSION_NONE) session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $isLoggedIn ? $_SESSION['name'] : '';
$userRole   = $isLoggedIn ? $_SESSION['role'] : '';
$currentPage = basename($_SERVER['PHP_SELF']); // e.g., "index.php", "login.php", "register.php"
?>

<style>
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

    .bg-emerald {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .gradient-text {
        background: linear-gradient(135deg, #059669, #047857);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .btn-emerald {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .btn-emerald:hover {
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
    }
</style>

<nav class="navbar navbar-expand-lg sticky-top backdrop-blur border-bottom">
    <div class="container">
        <!-- Brand -->
        <a href="<?= $isLoggedIn ? 'main.php' : 'index.php'; ?>" class="navbar-brand d-flex align-items-center text-decoration-none">
            <div class="position-relative me-3">
                <div class="feature-icon bg-emerald" style="width: 40px; height: 40px;">
                    <i class="fas fa-users" style="font-size: 1.5rem;"></i>
                </div>
            </div>
            <div>
                <h1 class="h4 mb-0 gradient-text fw-bold">AgoraBoard</h1>
                <small class="text-muted">Community Hub</small>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if ($isLoggedIn): ?>
                <!-- Logged-in Navbar -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link <?= $currentPage === 'main.php' ? 'active' : '' ?>" href="main.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?= $currentPage === 'announcements.php' ? 'active' : '' ?>" href="announcements.php">Announcements</a></li>
                    <li class="nav-item"><a class="nav-link <?= $currentPage === 'events.php' ? 'active' : '' ?>" href="events.php">Events</a></li>
                    <li class="nav-item"><a class="nav-link <?= $currentPage === 'categories.php' ? 'active' : '' ?>" href="categories.php">Categories</a></li>
                </ul>

                <div class="d-flex gap-2 align-items-center">
                    <span class="text-success fw-semibold me-2">Hello, <?= htmlspecialchars($userName) ?></span>

                    <?php if ($userRole === 'admin'): ?>
                        <a href="dashboard.php" class="btn btn-outline-success">Admin Dashboard</a>
                    <?php endif; ?>

                    <div class="dropdown">
                        <button class="btn btn-emerald dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Create
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="post_event.php">New Event</a></li>
                            <li><a class="dropdown-item" href="post_announcement.php">New Announcement</a></li>
                            <li><a class="dropdown-item" href="post_job.php">New Job</a></li>
                            <li><a class="dropdown-item" href="post_lostfound.php">Lost & Found</a></li>
                            <li><a class="dropdown-item" href="post_volunteer.php">Volunteering</a></li>
                        </ul>
                    </div>

                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            <?php else: ?>
                <!-- Guest Navbar -->
                <div class="d-flex gap-2 ms-auto">
                    <a href="login.php" class="btn <?= $currentPage === 'login.php' ? 'btn-emerald' : 'btn-outline-success' ?>">Login</a>
                    <a href="register.php" class="btn <?= $currentPage === 'register.php' ? 'btn-emerald' : 'btn-outline-success' ?>">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
