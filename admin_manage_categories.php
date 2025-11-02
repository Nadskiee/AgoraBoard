<?php
session_start();
require_once 'db_connect.php';

// 🔒 Check login and admin role
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$success_message = null;
$error_message = null;

// Handle POST actions (Add / Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- ADD NEW CATEGORY ---
    if (isset($_POST['action']) && $_POST['action'] === 'add_category') {
        $name = trim($_POST['category_name']);

        if (empty($name)) {
            $error_message = "Category name cannot be empty.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                $stmt->execute([$name]);
                $success_message = "Category added successfully!";
            } catch (PDOException $e) {
                // Check for duplicate entry error (SQLSTATE 23000, error code 1062)
                if ($e->errorInfo[1] == 1062) {
                    $error_message = "Error: A category with this name already exists.";
                } else {
                    $error_message = "Error adding category: " . $e->getMessage();
                }
            }
        }
    }

    // --- UPDATE CATEGORY ---
    if (isset($_POST['action']) && $_POST['action'] === 'update_category') {
        $name = trim($_POST['category_name']);
        $id = $_POST['category_id'];

        if (empty($name) || empty($id)) {
            $error_message = "Invalid data provided for update.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
                $stmt->execute([$name, $id]);
                $success_message = "Category updated successfully!";
            } catch (PDOException $e) {
                // Check for duplicate entry error
                if ($e->errorInfo[1] == 1062) {
                    $error_message = "Error: A category with this name already exists.";
                } else {
                    $error_message = "Error updating category: " . $e->getMessage();
                }
            }
        }
    }
}

// Handle GET actions (Delete)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id']; // ✅ Add this line
    try {
        $stmt = $pdo->prepare("UPDATE categories SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
        $success_message = "Category deleted successfully!";
    } catch (PDOException $e) {
        // Check for foreign key constraint violation (SQLSTATE 23000, error code 1451)
        if ($e->errorInfo[1] == 1451) {
            $error_message = "Error: Cannot delete category. It is being used by one or more posts.";
        } else {
            $error_message = "Error deleting category: " . $e->getMessage();
        }
    }
}

// --- READ ALL CATEGORIES ---
// Fetch all categories and count posts in each
try {
    $stmt = $pdo->query("
    SELECT 
        c.id, 
        c.name, 
        c.created_at,
        (SELECT COUNT(*) FROM community_posts p WHERE p.category_id = c.id) as post_count
    FROM categories c
    WHERE c.deleted_at IS NULL
    ORDER BY c.name ASC
    ");

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    $error_message = "Error fetching categories: " . $e->getMessage();
}

$adminName = $_SESSION['currentUser']['name'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - AgoraBoard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin_dashboard.css?v=<?php echo time(); ?>">
    <!-- <style>
        html, body { height: 100%; margin: 0; overflow-x: hidden; }
        .container-fluid { height: 100%; }
        #sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 0; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); overflow-y: auto; }
        main { margin-left: 16.66667%; min-height: 100vh; padding-bottom: 50px; }
        .sidebar .nav-link { padding-left: 1.5rem; }
        @media (max-width: 768px) { main { margin-left: 0; } }
    </style> -->
</head>
<div class="container-fluid">
    <div class="row">
        <?php include 'admin_sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Categories</h1>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 fw-bold text-primary">
                                <i class="fas fa-plus me-2"></i>Add New Category
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="admin_manage_categories.php">
                                <input type="hidden" name="action" value="add_category">
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Category Name</label>
                                    <input type="text" class="form-control" id="categoryName" name="category_name" required maxlength="50" placeholder="e.g., 'Events'">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> Add Category
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 fw-bold text-primary">
                                <i class="fas fa-tags me-2"></i>Existing Categories
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Post Count</th>
                                            <th>Created On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($categories)): ?>
                                            <?php foreach ($categories as $cat): ?>
                                                <tr>
                                                    <td class="fw-bold">
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <?php echo $cat['post_count']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="small text-muted">
                                                        <?php echo date('M j, Y', strtotime($cat['created_at'])); ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <button type="button"
                                                                class="btn btn-outline-primary"
                                                                title="Edit"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editModal"
                                                                data-id="<?php echo $cat['id']; ?>"
                                                                data-name="<?php echo htmlspecialchars($cat['name']); ?>">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button type="button"
                                                                class="btn btn-outline-danger"
                                                                title="Delete"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#deleteModal"
                                                                data-id="<?php echo $cat['id']; ?>"
                                                                data-name="<?php echo htmlspecialchars($cat['name']); ?>"
                                                                <?php echo ($cat['post_count'] > 0) ? 'disabled' : ''; ?>>
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    No categories found.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="admin_manage_categories.php">
                <input type="hidden" name="action" value="update_category">
                <input type="hidden" name="category_id" id="editCategoryId">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="fas fa-edit me-2"></i>Edit Category
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editCategoryName" class="form-label">Category Name</label>
                        <input type="text" class="form-control" id="editCategoryName" name="category_name" required maxlength="50">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this category?</p>
                <div class="alert alert-warning mb-0">
                    <strong>Category:</strong> <span id="deleteCategoryName"></span>
                </div>
                <p class="text-danger mt-3 mb-0">
                    <small>This action cannot be undone. You can only delete categories that have 0 posts.</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i>Delete
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Handle edit modal data
    const editModal = document.getElementById('editModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');

        document.getElementById('editCategoryId').value = id;
        document.getElementById('editCategoryName').value = name;
    });

    // Handle delete modal data
    const deleteModal = document.getElementById('deleteModal');
    deleteModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');

        document.getElementById('deleteCategoryName').textContent = name;
        // ✅ Correct file name
        document.getElementById('confirmDeleteBtn').href = 'admin_manage_categories.php?action=delete&id=' + id;
    });

    document.getElementById('confirmDeleteBtn').addEventListener('click', function(event) {
    if (!this.href.includes('id=')) {
        event.preventDefault();
        alert('Invalid delete request.');
    }
});

</script>
</body>

</html>