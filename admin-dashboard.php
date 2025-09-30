<?php
session_start();
require_once 'db.php'; 

// 🔐 Check login ug admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Stats array
$stats = [];

// Total posts
$query = "SELECT COUNT(*) as total FROM posts";
$result = $conn->query($query);
$stats['total_posts'] = $result->fetch_assoc()['total'] ?? 0;

// Pending posts
$query = "SELECT COUNT(*) as total FROM posts WHERE status = 'pending'";
$result = $conn->query($query);
$stats['pending_posts'] = $result->fetch_assoc()['total'] ?? 0;

// Total users
$query = "SELECT COUNT(*) as total FROM users";
$result = $conn->query($query);
$stats['total_users'] = $result->fetch_assoc()['total'] ?? 0;

// Total categories
$query = "SELECT COUNT(*) as total FROM categories";
$result = $conn->query($query);
$stats['total_categories'] = $result->fetch_assoc()['total'] ?? 0;

// Recent posts (limit 5)
$query = "SELECT p.*, u.first_name, u.last_name, c.name as category_name 
          FROM posts p
          LEFT JOIN users u ON p.user_id = u.id 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY p.created_at DESC LIMIT 5";
$recent_posts = $conn->query($query);

$adminName = $_SESSION['name'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgoraBoard Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">AgoraBoard</h5>
                        <small class="text-muted">Admin Panel</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="admin-dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="posts.php">
                                <i class="fas fa-clipboard-list me-2"></i> Manage Posts
                                <?php if($stats['pending_posts'] > 0): ?>
                                    <span class="badge bg-warning ms-2"><?php echo $stats['pending_posts']; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="users.php">
                                <i class="fas fa-users me-2"></i> Manage Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="categories.php">
                                <i class="fas fa-tags me-2"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="reports.php">
                                <i class="fas fa-chart-bar me-2"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50" href="settings.php">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </li>
                    </ul>
                    
                    <hr class="text-white-50">
                    
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i>
                            <strong><?php echo htmlspecialchars($adminName); ?></strong>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- Statistics Cards -->
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
                                <div class="text-xs fw-bold text-warning text-uppercase mb-1">Pending Posts</div>
                                <div class="h5 mb-0 fw-bold"><?php echo number_format($stats['pending_posts']); ?></div>
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

                <!-- Recent Activity -->
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
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if ($recent_posts && $recent_posts->num_rows > 0): ?>
                                                <?php while($post = $recent_posts->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars(substr($post['title'], 0, 30)) . '...'; ?></td>
                                                        <td><?php echo htmlspecialchars($post['first_name'] . " " . $post['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($post['category_name']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                echo $post['status'] == 'approved' ? 'success' : 
                                                                     ($post['status'] == 'pending' ? 'warning' : 'danger'); ?>">
                                                                <?php echo ucfirst($post['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-eye"></i></a>
                                                                <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-edit"></i></a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr><td colspan="6" class="text-center">No recent posts found</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Quick Actions</h6></div>
                            <div class="card-body d-grid gap-2">
                                <a href="posts.php?status=pending" class="btn btn-warning"><i class="fas fa-clock me-2"></i> Review Pending Posts</a>
                                <a href="categories.php" class="btn btn-info"><i class="fas fa-plus me-2"></i> Add New Category</a>
                                <a href="users.php" class="btn btn-success"><i class="fas fa-user-plus me-2"></i> Manage Users</a>
                                <a href="reports.php" class="btn btn-secondary"><i class="fas fa-chart-bar me-2"></i> View Reports</a>
                            </div>
                        </div>

                        <!-- System Status -->
                        <div class="card shadow mb-4">
                            <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">System Status</h6></div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
