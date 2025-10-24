<?php
session_start();
require_once 'db_connect.php'; // ✅ PDO connection

// 🔐 Check if admin is logged in
if (!isset($_SESSION['currentUser']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Logged-in admin info
$adminName = $_SESSION['name'] ?? 'Administrator';

// Initialize stats array
$stats = [
    'total_posts' => 0,
    'total_users' => 0,
    'total_categories' => 0
];

try {
    // 🧮 Total counts
    $stats['total_posts'] = $pdo->query("SELECT COUNT(*) FROM community_posts")->fetchColumn();
    $stats['total_users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Count unique categories
    $stats['total_categories'] = $pdo->query("SELECT COUNT(DISTINCT category) FROM community_posts")->fetchColumn();

    // 📰 Recent posts with user info
    $stmt = $pdo->query("
        SELECT cp.id, cp.title, cp.category, cp.created_at,
               u.first_name, u.last_name
        FROM community_posts cp
        LEFT JOIN users u ON cp.created_by = u.id
        ORDER BY cp.created_at DESC
        LIMIT 5
    ");
    $recent_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgoraBoard Admin Dashboard</title>
    <link rel="stylesheet" href="assets/admin-dashboard.css?v=<?php echo time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

</head>

<body>
    <div class="container-fluid">
        <div class="row">
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
                            <a href="" class="nav-link py-1 <?= basename($_SERVER['PHP_SELF']) === 'admin-dashboard.php' ? 'active' : ''; ?>" href="admin-dashboard.php">
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

            <!-- Main Content -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="dashboard-header border-bottom mb-4 pb-2">
                    <h1 class="h3">Dashboard Overview</h1>
                </div>

                <!-- Stats -->
                <div class="stats-grid mb-4">
                    <div class="stat-card">
                        <div class="stat-title">Total Posts</div>
                        <div class="stat-value"><?= number_format($stats['total_posts']); ?></div>
                    </div>
                    <div class="stat-card" style="border-left-color: var(--cyan);">
                        <div class="stat-title">Total Users</div>
                        <div class="stat-value"><?= number_format($stats['total_users']); ?></div>
                    </div>
                    <div class="stat-card" style="border-left-color: var(--teal-accent);">
                        <div class="stat-title">Categories</div>
                        <div class="stat-value"><?= number_format($stats['total_categories']); ?></div>
                    </div>
                </div>

                <!-- Recent Posts Table -->
                <div class="table-card table-green mb-4">
                    <div class="card-header">
                        <h6 class="m-0">Recent Community Posts</h6>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-green table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_posts)): ?>
                                    <?php foreach ($recent_posts as $post): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($post['title']); ?></td>
                                            <td><?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></td>
                                            <td><?= htmlspecialchars($post['category']); ?></td>
                                            <td><?= date('M j, Y', strtotime($post['created_at'])); ?></td>
                                            <td class="text-center">
                                                <!-- View button -->
                                                <a href="view_post.php?id=<?= $post['id']; ?>"
                                                    class="btn btn-sm btn-outline-primary me-1"
                                                    title="View Post">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <!-- Edit button (placeholder or real link) -->
                                                <a href="edit_post.php?id=<?= $post['id']; ?>"
                                                    class="btn btn-sm btn-outline-success me-1"
                                                    title="Edit Post">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Delete button (semi-functional with confirm) -->
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger delete-btn"
                                                    data-id="<?= $post['id']; ?>"
                                                    title="Delete Post">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No recent posts found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <footer class="footer">
                    &copy; <?= date('Y'); ?> AgoraBoard Admin Dashboard
                </footer>
            </main>
        </div>
    </div>
</body>


</html>