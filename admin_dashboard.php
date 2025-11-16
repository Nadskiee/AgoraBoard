<?php
session_start();
require_once 'db_connect.php';

// 🔒 Check login and admin role
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Stats array
$stats = [];

try {
    // Total posts
    $query = "SELECT COUNT(*) as total FROM community_posts";
    $result = $pdo->query($query);
    $stats['total_posts'] = $result->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Total users
    $query = "SELECT COUNT(*) as total FROM users";
    $result = $pdo->query($query);
    $stats['total_users'] = $result->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Total categories
    $query = "SELECT COUNT(*) as total FROM categories";
    $result = $pdo->query($query);
    $stats['total_categories'] = $result->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    // Recent posts (limit 5) - JOIN with categories table
    $query = "SELECT p.id, p.title, p.created_at, p.is_flagged, p.is_pinned,
                 u.first_name, u.last_name,
                 c.name as category_name
          FROM community_posts p
          LEFT JOIN users u ON p.created_by = u.id 
          LEFT JOIN categories c ON p.category_id = c.id
          ORDER BY p.created_at DESC LIMIT 5";

    $stmt = $pdo->query($query);
    $recent_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Dashboard query error: " . $e->getMessage());
    $stats = ['total_posts' => 0, 'total_users' => 0, 'total_categories' => 0];
    $recent_posts = [];
}

// Handle flag action
if (isset($_GET['action']) && $_GET['action'] === 'flag' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE community_posts SET is_flagged = 1 WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $success_message = "Post has been flagged!";
    } catch (PDOException $e) {
        $error_message = "Error flagging post: " . $e->getMessage();
    }
}

// Handle unflag action
if (isset($_GET['action']) && $_GET['action'] === 'unflag' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE community_posts SET is_flagged = 0 WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $success_message = "Post marked as safe!";
    } catch (PDOException $e) {
        $error_message = "Error unflagging post: " . $e->getMessage();
    }
}


// This $adminName variable will be used by the sidebar
$adminName = $_SESSION['currentUser']['name'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgoraBoard Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin_dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Posts</div>
                                <div class="h5 mb-0 fw-bold"><?php echo number_format($stats['total_posts']); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">Total Users</div>
                                <div class="h5 mb-0 fw-bold"><?php echo number_format($stats['total_users']); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card shadow h-100 py-2">
                            <div class="card-body">
                                <div class="text-xs fw-bold text-success text-uppercase mb-1">Categories</div>
                                <div class="h5 mb-0 fw-bold"><?php echo number_format($stats['total_categories']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-primary">Recent Posts</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
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
                                                        <td><?php echo htmlspecialchars(substr($post['title'], 0, 30)) . '...'; ?></td>
                                                        <td>
                                                            <?php
                                                            if ($post['first_name'] && $post['last_name']) {
                                                                echo htmlspecialchars($post['first_name'] . " " . $post['last_name']);
                                                            } else {
                                                                echo '<em class="text-muted">Deleted User</em>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?></td>
                                                        <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="admin_manage_posts.php?view_id=<?= $post['id'] ?>"
                                                                    class="btn btn-outline-info btn-sm" title="View">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>

                                                                <!-- Edit -->
                                                                <a href="admin_manage_posts.php?edit_id=<?= $post['id'] ?>"
                                                                    class="btn btn-outline-primary btn-sm" title="Edit">
                                                                    <i class="fas fa-edit"></i>
                                                                </a>

                                                                <!-- Pin/Unpin -->
                                                                <a href="admin_manage_posts.php?action=toggle_pin&id=<?= $post['id'] ?>"
                                                                    class="btn btn-outline-warning btn-sm" title="Pin/Unpin">
                                                                    <i class="fas fa-thumbtack"></i>
                                                                </a>

                                                                <!-- Flag/Unflag -->
                                                                <?php if (!empty($post['is_flagged']) && $post['is_flagged']): ?>
                                                                    <a href="admin_manage_posts.php?action=unflag&id=<?= $post['id'] ?>"
                                                                        class="btn btn-success btn-sm" title="Mark Safe"
                                                                        onclick="return confirm('Are you sure you want to mark this post as safe?');">
                                                                        <i class="fas fa-check"></i>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <a href="admin_manage_posts.php?action=flag&id=<?= $post['id'] ?>"
                                                                        class="btn btn-danger btn-sm" title="Flag Post"
                                                                        onclick="return confirm('Are you sure you want to flag this post?');">
                                                                        <i class="fas fa-flag"></i>
                                                                    </a>
                                                                <?php endif; ?>

                                                                <!-- Delete -->
                                                                <a href="admin_manage_posts.php?delete_id=<?= $post['id'] ?>"
                                                                    class="btn btn-outline-danger btn-sm" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            </div>
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
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-primary">Quick Actions</h6>
                            </div>
                            <div class="card-body d-grid gap-2">
                                <a href="admin_manage_posts.php" class="btn btn-primary"><i class="fas fa-clipboard-list me-2"></i> Manage Posts</a>
                                <a href="admin_manage_categories.php" class="btn btn-info"><i class="fas fa-plus me-2"></i> Add New Category</a>
                                <a href="admin_manage_users.php" class="btn btn-success"><i class="fas fa-user-plus me-2"></i> Manage Users</a>
                                <a href="admin_manage_reports.php" class="btn btn-secondary"><i class="fas fa-chart-bar me-2"></i> View Reports</a>
                            </div>
                        </div>

                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 fw-bold text-primary">System Status</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 d-flex justify-content-between"><span>Server Status</span><span class="badge bg-success">Online</span></div>
                                <div class="mb-3 d-flex justify-content-between"><span>Database</span><span class="badge bg-success">Connected</span></div>
                                <div class="mb-3 d-flex justify-content-between"><span>Last Backup</span><span class="text-muted">2 hours ago</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this post?")) {
                window.location.href = "delete_post.php?id=" + id;
            }
        }
    </script>
</body>

</html>