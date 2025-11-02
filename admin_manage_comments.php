<?php
session_start();
require_once 'db_connect.php';

// 🔒 Check login and admin role
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $success_message = "Comment deleted successfully!";
    } catch (PDOException $e) {
        $error_message = "Error deleting comment: " . $e->getMessage();
    }
}

// Pagination
$comments_per_page = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $comments_per_page;

// Filters
$search_user = isset($_GET['user']) ? trim($_GET['user']) : '';
$search_post = isset($_GET['post']) ? trim($_GET['post']) : '';

try {
    // 🔹 Base query
    $query = "SELECT c.*, 
                     u.first_name, u.last_name, u.email, 
                     p.title AS post_title
              FROM comments c
              LEFT JOIN users u ON c.user_id = u.id
              LEFT JOIN community_posts p ON c.post_id = p.id
              WHERE 1=1";

    $params = [];

    if (!empty($search_user)) {
        $query .= " AND (u.first_name LIKE :search_user OR u.last_name LIKE :search_user OR u.email LIKE :search_user)";
        $params[':search_user'] = "%$search_user%";
    }

    if (!empty($search_post)) {
        $query .= " AND p.title LIKE :search_post";
        $params[':search_post'] = "%$search_post%";
    }

    $query .= " ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);

    foreach ($params as $key => $param) {
        $stmt->bindValue($key, $param, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $comments_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔹 Count total comments
    $count_query = "SELECT COUNT(*) FROM comments c
                    LEFT JOIN users u ON c.user_id = u.id
                    LEFT JOIN community_posts p ON c.post_id = p.id
                    WHERE 1=1";

    if (!empty($search_user)) {
        $count_query .= " AND (u.first_name LIKE :search_user OR u.last_name LIKE :search_user OR u.email LIKE :search_user)";
    }
    if (!empty($search_post)) {
        $count_query .= " AND p.title LIKE :search_post";
    }

    $count_stmt = $pdo->prepare($count_query);
    foreach ($params as $key => $param) {
        $count_stmt->bindValue($key, $param, PDO::PARAM_STR);
    }
    $count_stmt->execute();
    $total_comments = (int)$count_stmt->fetchColumn();
    $total_pages = ceil($total_comments / $comments_per_page);

} catch (PDOException $e) {
    $comments = [];
    $total_comments = 0;
    $total_pages = 1;
    $error_message = "Error fetching comments: " . $e->getMessage();
}

// Sidebar admin name
$adminName = $_SESSION['currentUser']['name'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Comments - AgoraBoard Admin</title>
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
                <h1 class="h2">Manage Comments</h1>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filter Form -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Search by User</label>
                            <input type="text" name="user" class="form-control" placeholder="Name or email..." value="<?= htmlspecialchars($search_user); ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Search by Post</label>
                            <input type="text" name="post" class="form-control" placeholder="Post title..." value="<?= htmlspecialchars($search_post); ?>">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filter</button>
                            <button type="button" id="clearFilter" class="btn btn-secondary"><i class="fas fa-redo me-1"></i> Reset</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Comments Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 fw-bold text-primary">All Comments (<?= number_format($total_comments); ?> total)</h6>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Comment</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Post</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $c): ?>
                                <tr>
                                    <td><?= $c['id']; ?></td>
                                    <td><?= htmlspecialchars(strlen($c['content']) > 80 ? substr($c['content'],0,80).'...' : $c['content']); ?></td>
                                    <td><?= $c['first_name'] && $c['last_name'] ? htmlspecialchars($c['first_name'].' '.$c['last_name']) : '<em class="text-muted">Deleted User</em>'; ?></td>
                                    <td><?= htmlspecialchars($c['email']); ?></td>
                                    <td><?= htmlspecialchars($c['post_title'] ?? 'Deleted Post'); ?></td>
                                    <td class="small text-muted"><?= date('M j, Y', strtotime($c['created_at'])); ?></td>
                                    <td>
                                        <a href="?action=delete&id=<?= $c['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this comment?');">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-comments fa-3x mb-3 d-block"></i>
                                    No comments found
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Comments pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?= $page-1; ?>&user=<?= urlencode($search_user); ?>&post=<?= urlencode($search_post); ?>">Previous</a>
                                </li>
                                <?php for ($i=1;$i<=$total_pages;$i++): ?>
                                    <li class="page-item <?= $page==$i ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?= $i; ?>&user=<?= urlencode($search_user); ?>&post=<?= urlencode($search_post); ?>"><?= $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?= $page+1; ?>&user=<?= urlencode($search_user); ?>&post=<?= urlencode($search_post); ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Reset filters
    document.getElementById('clearFilter').addEventListener('click', () => {
        window.location = 'admin_manage_comments.php';
    });
</script>
</body>
</html>
