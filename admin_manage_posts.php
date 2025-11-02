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
        $stmt = $pdo->prepare("UPDATE community_posts SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $success_message = "Post deleted successfully!";
    } catch (PDOException $e) {
        $error_message = "Error deleting post: " . $e->getMessage();
    }
}

// Handle pin/unpin action
if (isset($_GET['action']) && $_GET['action'] === 'toggle_pin' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE community_posts SET is_pinned = NOT is_pinned WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $success_message = "Post pin status updated!";
    } catch (PDOException $e) {
        $error_message = "Error updating post: " . $e->getMessage();
    }
}

// Handle update post action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_post') {
    try {
        $stmt = $pdo->prepare("UPDATE community_posts SET title = ?, content = ?, category_id = ? WHERE id = ?");
        $stmt->execute([
            $_POST['title'],
            $_POST['content'],
            $_POST['category_id'],
            $_POST['post_id']
        ]);
        $success_message = "Post updated successfully!";
    } catch (PDOException $e) {
        $error_message = "Error updating post: " . $e->getMessage();
    }
}

$editId = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : null;

// Pagination
$posts_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// Filter by category
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    // 🔹 Base query
    $query = "SELECT p.*, 
                     u.first_name, 
                     u.last_name, 
                     c.name AS category_name
              FROM community_posts p
              LEFT JOIN users u ON p.created_by = u.id
              LEFT JOIN categories c ON p.category_id = c.id";

    // 🔹 Build WHERE conditions
    $where_conditions = [];
    $params = [];

    $where_conditions[] = "p.deleted_at IS NULL";

    if ($category_filter > 0) {
        $where_conditions[] = "p.category_id = :category_id";
        $params[':category_id'] = $category_filter;
    }

    if (!empty($search)) {
        $where_conditions[] = "(p.title LIKE :search OR p.content LIKE :search)";
        $params[':search'] = "%$search%";
    }

    if (!empty($where_conditions)) {
        $query .= " WHERE " . implode(" AND ", $where_conditions);
    }

    // 🔹 Add sorting and pagination
    $query .= " ORDER BY p.is_pinned DESC, p.created_at DESC LIMIT :limit OFFSET :offset";

    // 🔹 Prepare & bind
    $stmt = $pdo->prepare($query);

    foreach ($params as $key => $param) {
        $stmt->bindValue($key, $param, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $posts_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔹 Count total posts (for pagination display)
    $count_query = "SELECT COUNT(*) as total FROM community_posts p";
    if (!empty($where_conditions)) {
        $count_query .= " WHERE " . implode(" AND ", $where_conditions);
    }
    $count_stmt = $pdo->prepare($count_query);
    foreach ($params as $key => $param) {
        $count_stmt->bindValue($key, $param, PDO::PARAM_STR);
    }
    $count_stmt->execute();
    $total_posts = (int) $count_stmt->fetchColumn();
    $total_pages = ceil($total_posts / $posts_per_page);

    // 🔹 Load categories for filters
    // 🔹 Load only active categories for filters
    $categories = $pdo->query("SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Posts query error: " . $e->getMessage());
    $posts = [];
    $categories = [];
    $total_posts = 0;
    $total_pages = 1;
}


// This $adminName variable will be used by the sidebar
$adminName = $_SESSION['currentUser']['name'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posts - AgoraBoard Admin</title>
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
                    <h1 class="h2">Manage Posts</h1>
                </div>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="GET" action="admin_manage_posts.php" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="0">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <button type="button" id="clearFilter" class="btn btn-secondary">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-primary">
                            All Posts (<?php echo number_format($total_posts); ?> total)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="30%">Title</th>
                                        <th width="15%">Author</th>
                                        <th width="12%">Category</th>
                                        <th width="12%">Date</th>
                                        <th width="8%">Status</th>
                                        <th width="18%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($posts)): ?>
                                        <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td><?php echo $post['id']; ?></td>
                                                <td>
                                                    <?php
                                                    $title = htmlspecialchars($post['title']);
                                                    echo strlen($title) > 50 ? substr($title, 0, 50) . '...' : $title;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($post['first_name'] && $post['last_name']) {
                                                        echo htmlspecialchars($post['first_name'] . " " . $post['last_name']);
                                                    } else {
                                                        echo '<em class="text-muted">Deleted User</em>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?>
                                                    </span>
                                                </td>
                                                <td class="small text-muted">
                                                    <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <?php if ($post['is_pinned']): ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-thumbtack"></i> Pinned
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Normal</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button"
                                                            class="btn btn-outline-info"
                                                            title="View"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#viewModal"
                                                            data-post-id="<?php echo $post['id']; ?>"
                                                            data-post-title="<?php echo htmlspecialchars($post['title']); ?>"
                                                            data-post-content="<?php echo htmlspecialchars($post['content']); ?>"
                                                            data-post-category="<?php echo htmlspecialchars($post['category_name'] ?? 'Uncategorized'); ?>"
                                                            data-post-author="<?php echo ($post['first_name'] && $post['last_name']) ? htmlspecialchars($post['first_name'] . ' ' . $post['last_name']) : 'Deleted User'; ?>"
                                                            data-post-date="<?php echo date('F j, Y \a\t g:i A', strtotime($post['created_at'])); ?>"
                                                            data-post-pinned="<?php echo $post['is_pinned'] ? '1' : '0'; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-outline-primary editBtn"
                                                            title="Edit"
                                                            data-post-id="<?php echo $post['id']; ?>"
                                                            data-post-title="<?php echo htmlspecialchars($post['title']); ?>"
                                                            data-post-content="<?php echo htmlspecialchars($post['content']); ?>"
                                                            data-post-category-id="<?php echo $post['category_id']; ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>

                                                        <a href="admin_manage_posts.php?action=toggle_pin&id=<?php echo $post['id']; ?>"
                                                            class="btn btn-outline-warning"
                                                            title="<?php echo $post['is_pinned'] ? 'Unpin' : 'Pin'; ?>">
                                                            <i class="fas fa-thumbtack"></i>
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-outline-danger"
                                                            title="Delete"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteModal"
                                                            data-post-id="<?php echo $post['id']; ?>"
                                                            data-post-title="<?php echo htmlspecialchars($post['title']); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                                No posts found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Posts pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                    </li>

                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="viewModalLabel">
                        <i class="fas fa-eye me-2"></i>View Post
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h4 id="viewPostTitle" class="mb-3"></h4>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-secondary" id="viewPostCategory"></span>
                            <span class="badge bg-light text-dark" id="viewPostPinned" style="display: none;">
                                <i class="fas fa-thumbtack"></i> Pinned
                            </span>
                        </div>
                        <p class="text-muted small mb-3">
                            <i class="fas fa-user me-1"></i><span id="viewPostAuthor"></span> •
                            <i class="fas fa-calendar-alt me-1"></i><span id="viewPostDate"></span>
                        </p>
                    </div>
                    <hr>
                    <div class="post-content-view" id="viewPostContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✏️ Edit Post Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Post
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <!-- Body -->
                <form id="editPostForm">
                    <input type="hidden" id="editPostId" name="post_id">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editPostTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editPostTitle" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label for="editPostCategory" class="form-label">Category</label>
                            <select class="form-select" id="editPostCategory" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="editPostContent" class="form-label">Content</label>
                            <textarea class="form-control" id="editPostContent" name="content" rows="4" required></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Are you sure you want to delete this post?</p>
                    <div class="alert alert-warning mb-0">
                        <strong>Post:</strong> <span id="deletePostTitle"></span>
                    </div>
                    <p class="text-danger mt-3 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        <small>This action cannot be undone.</small>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Delete Post
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Clear filter button
        document.getElementById('clearFilter').addEventListener('click', () => {
            document.querySelector('input[name="search"]').value = '';
            document.querySelector('select[name="category"]').value = '0';
            window.location = 'admin_manage_posts.php';
        });

        // Handle view modal data
        const viewModal = document.getElementById('viewModal');
        viewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;

            document.getElementById('viewPostTitle').textContent = button.getAttribute('data-post-title');
            document.getElementById('viewPostContent').textContent = button.getAttribute('data-post-content');
            document.getElementById('viewPostCategory').textContent = button.getAttribute('data-post-category');
            document.getElementById('viewPostAuthor').textContent = button.getAttribute('data-post-author');
            document.getElementById('viewPostDate').textContent = button.getAttribute('data-post-date');

            // Show/hide pinned badge
            const pinnedBadge = document.getElementById('viewPostPinned');
            if (button.getAttribute('data-post-pinned') === '1') {
                pinnedBadge.style.display = 'inline-block';
            } else {
                pinnedBadge.style.display = 'none';
            }
        });

        // Handle edit modal data
        const editButtons = document.querySelectorAll('.editBtn');
        const editForm = document.getElementById('editPostForm');

        editButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                // Read post data
                const id = btn.getAttribute('data-post-id');
                const title = btn.getAttribute('data-post-title');
                const content = btn.getAttribute('data-post-content');
                const categoryId = btn.getAttribute('data-post-category-id');

                // Fill modal fields
                document.getElementById('editPostId').value = id;
                document.getElementById('editPostTitle').value = title;
                document.getElementById('editPostContent').value = content;
                document.getElementById('editPostCategory').value = categoryId;

                // Show modal manually
                const modal = new bootstrap.Modal(document.getElementById('editModal'));
                modal.show();
            });
        });

        // Handle Save
        editForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const formData = new FormData(editForm);
            formData.append('action', 'update_post');

            fetch('admin_manage_posts.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(result => {
                    alert('Post updated successfully!');
                    location.reload();
                })
                .catch(err => {
                    alert('Error updating post.');
                    console.error(err);
                });
        });

        // Handle delete modal data
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const postId = button.getAttribute('data-post-id');
            const postTitle = button.getAttribute('data-post-title');

            document.getElementById('deletePostTitle').textContent = postTitle;
            document.getElementById('confirmDeleteBtn').href = 'admin_manage_posts.php?action=delete&id=' + postId;
        });

        // Auto-open edit modal if edit_id is present
        const urlParams = new URLSearchParams(window.location.search);
        const editId = urlParams.get('edit_id');
        const viewId = urlParams.get('view_id');
        const deleteId = urlParams.get('delete_id');

        // 🟦 Auto-open EDIT modal
        if (editId) {
            const btn = document.querySelector(`.editBtn[data-post-id="${editId}"]`);
            if (btn) {
                btn.click();
                cleanURL();
            }
        }

        // 🟩 Auto-open VIEW modal
        else if (viewId) {
            const btn = document.querySelector(`[data-post-id="${viewId}"][data-bs-target="#viewModal"]`);
            if (btn) {
                btn.click();
                cleanURL();
            }
        }

        // 🟥 Auto-open DELETE modal
        else if (deleteId) {
            const btn = document.querySelector(`[data-post-id="${deleteId}"][data-bs-target="#deleteModal"]`);
            if (btn) {
                btn.click();
                cleanURL();
            }
        }

        // Utility: clean query params
        function cleanURL() {
            const url = new URL(window.location);
            ['edit_id', 'view_id', 'delete_id'].forEach(p => url.searchParams.delete(p));
            window.history.replaceState({}, '', url);
        }
    </script>
</body>

</html>