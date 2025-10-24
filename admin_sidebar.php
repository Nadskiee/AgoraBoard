<link rel="stylesheet" href="assets/admin-dashboard.css?v=<?php echo time(); ?>">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

<!-- Sidebar -->
<nav class="sidebar col-md-3 col-lg-2 d-md-block collapse" id="adminSidebar">
    <div class="d-flex flex-column h-100 py-3 px-2">
        <!-- Logo / Title -->
        <div class="text-center mb-3">
            <h6 class="fw-bold text-white mb-0">AgoraBoard</h6>
            <small class="text-light opacity-75">Admin</small>
        </div>

        <!-- Navigation -->
        <ul class="nav flex-column small mb-auto">
            <li>
                <a href="admin-dashboard.php" class="nav-link py-1 <?= basename($_SERVER['PHP_SELF']) === 'admin-dashboard.php' ? 'active' : ''; ?>" href="admin-dashboard.php">
                    <i class="fas fa-chart-line me-1"></i> Dashboard
                </a>
            </li>

            <li>
                <hr class="text-secondary my-2">
            </li>

            <li>
                <a href="admin_manage_posts.php" class="nav-link py-1 <?= basename($_SERVER['PHP_SELF']) === 'posts.php' ? 'active' : ''; ?>" href="posts.php">
                    <i class="fas fa-bullhorn me-1"></i> Posts
                </a>
            </li>
            <li>
                <a class="nav-link py-1 <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                    <i class="fas fa-folder-open me-1"></i> Categories
                </a>
            </li>
            <li>
                <a class="nav-link py-1 <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-users me-1"></i> Users
                </a>
            </li>

            <li>
                <hr class="text-secondary my-2">
            </li>

            <li>
                <a class="nav-link py-1 <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-flag me-1"></i> Reports
                </a>
            </li>
            <li>
                <a class="nav-link py-1 <?= basename($_SERVER['PHP_SELF']) === 'comments.php' ? 'active' : ''; ?>" href="comments.php">
                    <i class="fas fa-comments me-1"></i> Comments
                </a>
            </li>
            <li>
                <a class="nav-link py-1 <?= basename($_SERVER['PHP_SELF']) === 'polls.php' ? 'active' : ''; ?>" href="polls.php">
                    <i class="fas fa-poll me-1"></i> Polls
                </a>
            </li>
            <li>
                <a class="nav-link py-1 <?= basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>" href="events.php">
                    <i class="fas fa-calendar-alt me-1"></i> Events
                </a>
            </li>

            <li>
                <hr class="text-secondary my-2">
            </li>

            <li>
                <a class="nav-link py-1 <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-cog me-1"></i> Settings
                </a>
            </li>
        </ul>

        <!-- Footer -->
        <div class="sidebar-footer text-center mt-auto">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center justify-content-center text-white text-decoration-none dropdown-toggle small" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-1 fs-6"></i>
                    <?= htmlspecialchars($adminName); ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-id-badge me-1"></i> Profile</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>