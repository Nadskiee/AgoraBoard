<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .sidebar {
        background-color: var(--teal);
        color: white;
        min-height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        width: 220px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .sidebar .nav-link {
        color: #dcdcdc;
        padding: 8px 15px;
        border-radius: 4px;
        margin: 2px 10px;
        font-size: 0.9rem;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        background-color: #0a615aff;
        color: #fff;
    }

    .sidebar hr {
        border-color: rgba(255, 255, 255, 0.2);
    }

    .sidebar-footer {
        padding: 15px;
        text-align: center;
    }

    .sidebar-footer .btn-logout {
        width: 100%;
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 6px 0;
        transition: background-color 0.2s;
    }

    .sidebar-footer .btn-logout:hover {
        background-color: #bb2d3b;
    }

    /* Adjust main content padding */
    .main-content {
        margin-left: 220px;
        padding: 20px;
    }
</style>

<!-- Sidebar -->
<nav class="sidebar">
    <div>
        <div class="text-center py-3 border-bottom border-light-subtle">
            <h5 class="fw-bold mb-0">AgoraBoard</h5>
            <small class="opacity-75">Admin Panel</small>
        </div>

        <ul class="nav flex-column mt-3">
            <li>
                <a href="admin_dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="admin_manage_posts.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'admin_manage_posts.php' ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn me-2"></i> Posts
                </a>
            </li>
            <li>
                <a href="categories.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : ''; ?>">
                    <i class="fas fa-folder-open me-2"></i> Categories
                </a>
            </li>
            <li>
                <a href="users.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users me-2"></i> Users
                </a>
            </li>
            <li>
                <a href="reports.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-flag me-2"></i> Reports
                </a>
            </li>
            <li>
                <a href="comments.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'comments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-comments me-2"></i> Comments
                </a>
            </li>
            <li>
                <a href="events.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt me-2"></i> Events
                </a>
            </li>
            <li>
                <a href="settings.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog me-2"></i> Settings
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="mb-2 small"><?= htmlspecialchars($adminName); ?></div>
        <a href="logout.php" class="btn btn-logout">
            <i class="fas fa-sign-out-alt me-1"></i> Logout
        </a>
    </div>
</nav>
