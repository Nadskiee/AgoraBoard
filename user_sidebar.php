<?php
// Default active page to 'dashboard' if not set
if (!isset($active_page)) {
    $active_page = 'dashboard';
}

// Get user name from session
$userName = $_SESSION['currentUser']['name'] ?? "User";
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">

<style>
    :root {
        --sage: #10b981;
        /* Primary Green */
        --sage-light: #059669;
        --sage-dark: #047857;
        --cream: #f5f5f0;
        --bg: #fdfdfc;
        --muted-text: #6c757d;
        --dark-text: #3b3a36;
        --border-color: #eae8e3;
        --sidebar-width: 240px;
    }

    body {
        background-color: var(--bg);
        color: var(--dark-text);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
            "Helvetica Neue", Arial, sans-serif;
    }

    /* Sidebar Styling */
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: var(--sidebar-width);
        background: var(--sage);
        color: #fff;
        padding: 20px;
        display: flex;
        flex-direction: column;
    }

    .sidebar h4 {
        font-weight: 700;
        letter-spacing: 0.5px;
        padding-left: 10px;
    }

    .sidebar .nav-link {
        color: #e6e3d3;
        font-weight: 500;
        padding: 8px 12px;
        /* reduced padding */
        border-radius: 8px;
        margin-bottom: 2px;
        /* reduced margin */
        display: flex;
        align-items: center;
        gap: 8px;
        /* smaller gap between icon and text */
        transition: background 0.2s ease;
    }


    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        background: rgba(0, 0, 0, 0.15);
        color: #fff;
    }

    .sidebar .nav-link i {
        font-size: 1.1rem;
    }

    .sidebar hr {
        border-color: rgba(255, 255, 255, 0.3);
        margin: 10px 0;
    }

    /* Footer */
    .sidebar-footer {
        margin-top: auto;
    }

    .sidebar-footer .logout-btn {
        background-color: rgba(0, 0, 0, 0.15);
        border: none;
        color: #fff;
        width: 100%;
        text-align: left;
        border-radius: 6px;
        padding: 10px 12px;
        transition: background-color 0.3s ease;
    }

    .sidebar-footer .logout-btn:hover {
        background-color: rgba(0, 0, 0, 0.3);
    }

    .sidebar-footer small {
        display: block;
        text-align: center;
        margin-bottom: 10px;
        color: #f0f0f0;
    }

    /* Main content offset */
    .main-content {
        margin-left: var(--sidebar-width);
        padding: 25px 30px;
    }

    @media (max-width: 768px) {
        .sidebar {
            position: relative;
            width: 100%;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: space-around;
            padding: 10px;
        }

        .main-content {
            margin-left: 0;
            padding: 15px;
        }
    }
</style>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-content">
        <h4 class="mb-4"><i class="bi bi-people-fill me-2"></i> AgoraBoard</h4>
        <nav class="nav flex-column">
            <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-house-door"></i> Dashboard
            </a>
            <a href="public-safety.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'public-safety.php' ? 'active' : ''; ?>">
                <i class="bi bi-shield-exclamation"></i> Public Safety
            </a>
            <a href="lost_and_found.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'lost-and-found.php' ? 'active' : ''; ?>">
                <i class="bi bi-search"></i> Lost & Found
            </a>
            <a href="event.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'event.php' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-event"></i> Events
            </a>
            <a href="jobs.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'jobs.php' ? 'active' : ''; ?>">
                <i class="bi bi-briefcase"></i> Jobs
            </a>
            <a href="polls_view.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'polls_view.php' ? 'active' : ''; ?>">
                <i class="bi bi-bar-chart-line"></i> Polls
            </a>
            <a href="volunteering.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'volunteering.php' ? 'active' : ''; ?>">
                <i class="bi bi-heart"></i> Volunteering
            </a>
            <hr class="my-3 border-white opacity-25">
            <a href="bookmarks_view.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'bookmarks_view.php' ? 'active' : ''; ?>">
                <i class="bi bi-bookmark"></i> Bookmarks
            </a>
            <a href="settings.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i> Settings
            </a>
        </nav>
    </div>

    <div class="sidebar-footer">
        <small><?= htmlspecialchars($userName); ?></small>
        <form action="logout.php" method="POST" id="logoutForm">
            <input type="hidden" name="logout" value="1">
            <button type="button" class="logout-btn" onclick="confirmLogout()">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>
</div>

<script>
    function confirmLogout() {
        if (confirm("Are you sure you want to logout?")) {
            document.getElementById("logoutForm").submit();
        }
    }
</script>