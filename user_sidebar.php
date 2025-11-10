<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current user info
$currentUser = $_SESSION['currentUser'] ?? null;
$userInitial = strtoupper(substr($currentUser['name'] ?? '?', 0, 1));
$userName = htmlspecialchars($currentUser['name'] ?? 'Anonymous', ENT_QUOTES, 'UTF-8');

// Determine active page (set in parent file)
$active_page = $active_page ?? 'dashboard';
?>

<style>
    :root {
        --sidebar-width: 245px;
        --sidebar-bg: #10b981;
        --sidebar-hover: #059669;
        --sidebar-active: #047857;
    }

    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        width: var(--sidebar-width);
        height: 100vh;
        background: var(--sidebar-bg);
        color: white;
        display: flex;
        flex-direction: column;
        z-index: 1000;
        overflow-y: auto;
    }

    /* Hide scrollbar but keep functionality */
    .sidebar::-webkit-scrollbar {
        display: none;
    }

    .sidebar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .sidebar-header {
        padding: 1.5rem 1.25rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        color: white;
        font-size: 1.35rem;
        font-weight: 700;
    }

    .sidebar-logo i {
        font-size: 1.5rem;
    }

    .sidebar-nav {
        flex: 1;
        padding: 1rem 0;
    }

    .sidebar-nav-item {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 0.875rem 1.25rem;
        color: white;
        text-decoration: none;
        transition: all 0.2s ease;
        font-size: 0.95rem;
        border-left: 3px solid transparent;
    }

    .sidebar-nav-item:hover {
        background: var(--sidebar-hover);
        color: white;
    }

    .sidebar-nav-item.active {
        background: var(--sidebar-active);
        border-left-color: white;
        font-weight: 600;
    }

    .sidebar-nav-item i {
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
    }

    .sidebar-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .sidebar-logout {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 0.875rem;
        color: white;
        text-decoration: none;
        background: rgba(0, 0, 0, 0.1);
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        font-size: 0.95rem;
        border: none;
        width: 100%;
        cursor: pointer;
    }

    .sidebar-logout:hover {
        background: rgba(0, 0, 0, 0.2);
        color: white;
    }

    .sidebar-logout i {
        font-size: 1.1rem;
    }

    /* Adjust main content for sidebar */
    .main-content {
        margin-left: var(--sidebar-width);
        min-height: 100vh;
        padding: 2rem;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
        }

        .mobile-menu-toggle {
            display: block;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 999;
            background: var(--sidebar-bg);
            color: white;
            border: none;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 1.25rem;
        }
    }

    .mobile-menu-toggle {
        display: none;
    }
</style>

<!-- Mobile Menu Toggle -->
<button class="mobile-menu-toggle" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <!-- Header/Logo -->
    <div class="sidebar-header">
        <a href="dashboard.php" class="sidebar-logo">
            <i class="bi bi-people-fill"></i>
            <span>AgoraBoard</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <a href="dashboard.php" class="sidebar-nav-item <?= $active_page === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-house-door-fill"></i>
            <span>Dashboard</span>
        </a>

        <a href="public-safety.php" class="sidebar-nav-item <?= $active_page === 'public_safety' ? 'active' : '' ?>">
            <i class="bi bi-shield-fill-check"></i>
            <span>Public Safety</span>
        </a>

        <a href="lost_and_found.php" class="sidebar-nav-item <?= $active_page === 'lost_and_found' ? 'active' : '' ?>">
            <i class="bi bi-search"></i>
            <span>Lost and Found</span>
        </a>

        <a href="event.php" class="sidebar-nav-item <?= $active_page === 'event' ? 'active' : '' ?>">
            <i class="bi bi-calendar-event-fill"></i>
            <span>Event</span>
        </a>

        <a href="jobs.php" class="sidebar-nav-item <?= $active_page === 'jobs' ? 'active' : '' ?>">
            <i class="bi bi-briefcase-fill"></i>
            <span>Jobs</span>
        </a>

        <a href="polls_view.php" class="sidebar-nav-item <?= $active_page === 'polls' ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-fill"></i>
            <span>Polls</span>
        </a>

        <a href="volunteering.php" class="sidebar-nav-item <?= $active_page === 'volunteering' ? 'active' : '' ?>">
            <i class="bi bi-heart-fill"></i>
            <span>Volunteering</span>
        </a>

        <a href="bookmarks_view.php" class="sidebar-nav-item <?= $active_page === 'bookmarks' ? 'active' : '' ?>">
            <i class="bi bi-bookmark-fill"></i>
            <span>Bookmarks</span>
        </a>

        <a href="settings.php" class="sidebar-nav-item <?= $active_page === 'settings' ? 'active' : '' ?>">
            <i class="bi bi-gear-fill"></i>
            <span>Settings</span>
        </a>
    </nav>

    <!-- Footer/Logout -->
    <div class="sidebar-footer">
        <form id="logoutForm" method="POST" action="logout.php" style="margin: 0;">
            <button type="button" class="sidebar-logout" onclick="confirmLogout()">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('show');
    }

    // Close sidebar on mobile when clicking outside
    document.addEventListener('click', (e) => {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.mobile-menu-toggle');
        
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !toggle.contains(e.target) &&
            sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
        }
    });
</script>