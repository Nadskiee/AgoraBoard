<?php
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
        /* ensure text is white */
    }

    .btn-emerald:hover {
        background: linear-gradient(135deg, #059669, #10b981);
        color: white;
    }

    .btn-outline-success {
        border-color: #10b981;
        color: #10b981;
    }

    .btn-outline-success:hover {
        background-color: #10b981;
        color: white;
    }

    /* Active button always white text */
    .btn-emerald,
    .btn-outline-success.active {
        color: white !important;
    }

    /* Navbar solid white background with subtle shadow */
    .navbar {
        background-color: rgba(255, 255, 255, 0.95);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        backdrop-filter: blur(12px);
        transition: background-color 0.3s ease;
    }
</style>

<nav class="navbar navbar-expand-lg sticky-top border-bottom">
    <div class="container">
        <!-- Brand -->
        <a href="index.php" class="navbar-brand d-flex align-items-center text-decoration-none">
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

        <!-- Guest Buttons -->
        <div class="d-flex gap-2 ms-auto">
            <?php if ($currentPage === 'index.php'): ?>
                <!-- On landing page, make Register primary (green) -->
                <a href="login.php" class="btn btn-outline-success">Login</a>
                <a href="register.php" class="btn btn-emerald">Register</a>
            <?php else: ?>
                <!-- Highlight active page -->
                <a href="login.php" class="btn <?= $currentPage === 'login.php' ? 'btn-emerald' : 'btn-outline-success' ?>">Login</a>
                <a href="register.php" class="btn <?= $currentPage === 'register.php' ? 'btn-emerald' : 'btn-outline-success' ?>">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
