<?php
// Default to dashboard if $active_page is not set
if (!isset($active_page)) {
    $active_page = 'dashboard';
}

// Ensure $adminName is set, providing a default if not
$adminName = $_SESSION['currentUser']['name'] ?? "Admin";
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --teal: #0d9488;
    }

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
        z-index: 100;
    }

    .sidebar .nav-link {
        color: #dcdcdc;
        padding: 8px 15px;
        border-radius: 4px;
        margin: 2px 10px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        background-color: #0a615a;
        color: #fff;
    }

    .sidebar .nav-link i {
        width: 20px;
        text-align: center;
    }

    .sidebar .dropdown-toggle {
        color: #dcdcdc;
        padding: 8px 15px;
        border-radius: 4px;
        margin: 2px 10px;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        width: calc(100% - 20px);
        /* account for margin */
        text-decoration: none;
        background: none;
        border: none;
        cursor: pointer;
    }

    .sidebar .nav-link i,
    .sidebar .dropdown-toggle i {
        width: 20px;
        text-align: center;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active,
    .sidebar .dropdown-toggle:hover,
    .sidebar .dropdown-toggle.active {
        background-color: #0a615a;
        color: #fff;
    }

    /* Dropdown menu */
    .sidebar .dropdown-menu {
        background-color: #0b5345;
        /* slightly darker than sidebar */
        border: none;
        padding: 0;
        margin: 0;
        width: 100%;
    }

    /* Dropdown items */
    .sidebar .dropdown-item {
        padding: 8px 15px;
        color: #dcdcdc;
        font-size: 0.9rem;
        border-radius: 0;
        /* match sidebar links */
        width: 100%;
        display: flex;
        align-items: center;
        text-decoration: none;
    }

    /* Dropdown hover matches sidebar hover */
    .sidebar .dropdown-item:hover,
    .sidebar .dropdown-item:active {
        background-color: #0a615a;
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

    /* Responsive fix */
    @media (max-width: 768px) {
        .sidebar {
            position: relative;
            width: 100%;
            min-height: auto;
            flex-direction: row;
            flex-wrap: wrap;
            padding: 10px;
        }

        .sidebar ul {
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: center;
        }

        .sidebar .nav-link {
            margin: 5px;
            font-size: 0.85rem;
        }

        .main-content {
            margin-left: 0;
            padding: 15px;
        }
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
            <li class="nav-item dropdown">
                <!-- Dropdown toggle -->
                <a href="#"
                    class="nav-link dropdown-toggle <?= in_array(basename($_SERVER['PHP_SELF']), ['admin_manage_posts.php', 'admin_manage_safety.php', 'admin_manage_jobs.php', 'admin_manage_volunteering.php']) ? 'active' : ''; ?>"
                    id="postsDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fas fa-bullhorn me-2"></i>
                    <span id="postsDropdownLabel">
                        <?php
                        $file = basename($_SERVER['PHP_SELF']);
                        if ($file == 'admin_manage_safety.php') echo 'Safety Reports';
                        elseif ($file == 'admin_manage_jobs.php') echo 'Jobs';
                        elseif ($file == 'admin_manage_volunteering.php') echo 'Volunteering';
                        else echo 'Posts';
                        ?>
                    </span>
                </a>

                <!-- Dropdown menu -->
                <ul class="dropdown-menu bg-teal border-0" aria-labelledby="postsDropdown">
                    <li><a class="dropdown-item text-white" href="admin_manage_posts.php">Posts</a></li>
                    <li><a class="dropdown-item text-white" href="admin_manage_safety.php">Safety Reports</a></li>
                    <li><a class="dropdown-item text-white" href="admin_manage_jobs.php">Jobs</a></li>
                    <!-- <li><a class="dropdown-item text-white" href="admin_manage_volunteering.php">Volunteering</a></li> -->
                </ul>
            </li>
            <li>
                <a href="admin_manage_categories.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'admin_manage_categories.php' ? 'active' : ''; ?>">
                    <i class="fas fa-folder-open me-2"></i> Categories
                </a>
            </li>
            <li>
                <a href="admin_manage_users.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'admin_manage_users.php' ? 'active' : ''; ?>">

                    <i class="fas fa-users me-2"></i> Users
                </a>
            </li>
            <li>
                <a href="admin_manage_reports.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>">
                    <i class="fas fa-flag me-2"></i> Reports
                </a>
            </li>
            <li>
                <a href="admin_manage_comments.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'comments.php' ? 'active' : ''; ?>">
                    <i class="fas fa-comments me-2"></i> Comments
                </a>
            </li>
            <li>
                <a href="admin_support.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'admin_support.php' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope me-2"></i> Support
                </a>
            </li>
            <li>
                <a href="admin_profile.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'admin_profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle me-2"></i> Profile
                </a>
            </li>

            <!-- <li>
                <a href="events.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt me-2"></i> Events
                </a>
            </li>
            <li>
                <a href="settings.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog me-2"></i> Settings
                </a>
            </li> -->
        </ul>
    </div>

    <div class="sidebar-footer">
        <div class="mb-2 small"><?= htmlspecialchars($adminName); ?></div>
        <a href="logout.php" class="btn btn-logout">
            <i class="fas fa-sign-out-alt me-1"></i> Logout
        </a>
    </div>
</nav>