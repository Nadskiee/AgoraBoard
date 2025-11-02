<?php
session_start();
require_once 'db_connect.php';

// 🔒 Check login and admin role
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get the current admin's ID for safety checks
$current_admin_id = $_SESSION['currentUser']['id'];
$success_message = null;
$error_message = null;

// --- Handle GET Actions (Toggle Status, Toggle Role, Delete) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];

    // Prevent admin from modifying their own account via GET actions
    if ($user_id === $current_admin_id) {
        $error_message = "Error: You cannot modify your own account status, role, or delete it.";
    } else {

        // --- Toggle Status (Ban/Unban) ---
        if ($_GET['action'] === 'toggle_status') {
            try {
                $stmt = $pdo->prepare("UPDATE users SET status = CASE WHEN status = 'active' THEN 'banned' ELSE 'active' END WHERE id = ?");
                $stmt->execute([$user_id]);
                $success_message = "User status updated successfully!";
            } catch (PDOException $e) {
                $error_message = "Error updating user status: " . $e->getMessage();
            }
        }

        // --- Toggle Role (Admin/User) ---
        if ($_GET['action'] === 'toggle_role') {
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = CASE WHEN role = 'user' THEN 'admin' ELSE 'user' END WHERE id = ?");
                $stmt->execute([$user_id]);
                $success_message = "User role updated successfully!";
            } catch (PDOException $e) {
                $error_message = "Error updating user role: " . $e->getMessage();
            }
        }

        // --- Soft Delete User ---
        if ($_GET['action'] === 'delete') {
            try {
                $stmt = $pdo->prepare("UPDATE users SET deleted_at = NOW(), status = 'banned' WHERE id = ?");
                $stmt->execute([$user_id]);
                $success_message = "User deleted successfully (soft delete). Their content remains anonymized or intact.";
            } catch (PDOException $e) {
                $error_message = "Error deleting user: " . $e->getMessage();
            }
        }
    }
}

// --- Handle POST Actions (Add User, Update User) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Add New User ---
    if (isset($_POST['action']) && $_POST['action'] === 'add_user') {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        $role = $_POST['role'];
        $username = !empty($_POST['username']) ? trim($_POST['username']) : null;

        if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($role)) {
            $error_message = "Please fill in all required fields (First Name, Last Name, Email, Password, Role).";
        } elseif ($password !== $password_confirm) {
            $error_message = "Passwords do not match.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format.";
        } else {
            try {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, username, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$first_name, $last_name, $username, $email, $password_hash, $role]);
                $success_message = "User added successfully!";
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { // Duplicate entry
                    $error_message = "Error: An account with this email or username already exists.";
                } else {
                    $error_message = "Error adding user: " . $e->getMessage();
                }
            }
        }
    }

    // --- Update User Info ---
    if (isset($_POST['action']) && $_POST['action'] === 'update_user') {
        $user_id = $_POST['user_id'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $username = !empty($_POST['username']) ? trim($_POST['username']) : null;
        $email = trim($_POST['email']);

        if (empty($first_name) || empty($last_name) || empty($email) || empty($user_id)) {
            $error_message = "First name, last name, and email are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format.";
        } else {
            try {
                $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, email = ? WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $username, $email, $user_id]);
                $success_message = "User updated successfully!";
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { // Duplicate entry
                    $error_message = "Error: This email or username is already in use by another account.";
                } else {
                    $error_message = "Error updating user: " . $e->getMessage();
                }
            }
        }
    }
}

// --- Fetch All Users (with filters) ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT 
            u.id, u.first_name, u.last_name, u.username, u.email, u.role, u.status, u.created_at,
            (SELECT COUNT(*) FROM community_posts p WHERE p.created_by = u.id) as post_count
          FROM users u
          WHERE u.deleted_at IS NULL"; // <-- exclude deleted users

$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
    $search_term = "%$search%";
    array_push($params, $search_term, $search_term, $search_term, $search_term);
}
if (!empty($filter_role)) {
    $where_conditions[] = "u.role = ?";
    $params[] = $filter_role;
}
if (!empty($filter_status)) {
    $where_conditions[] = "u.status = ?";
    $params[] = $filter_status;
}

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " ORDER BY u.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    $error_message = "Error fetching users: " . $e->getMessage();
}

$adminName = $_SESSION['currentUser']['name'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - AgoraBoard Admin</title>
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
                    <h1 class="h2">Manage Users</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-user-plus me-1"></i> Add New User
                    </button>
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

                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="GET" action="admin_manage_users.php" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Name, email, username..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <option value="">All Roles</option>
                                    <option value="admin" <?php echo $filter_role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="user" <?php echo $filter_role == 'user' ? 'selected' : ''; ?>>User</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Statuses</option>
                                    <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="banned" <?php echo $filter_status == 'banned' ? 'selected' : ''; ?>>Banned</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i></button>
                                <a href="admin_manage_users.php" class="btn btn-secondary"><i class="fas fa-redo me-1"></i></a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 fw-bold text-primary">All Users (<?php echo count($users); ?>)</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Posts</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($users)): ?>
                                        <?php foreach ($users as $user): ?>
                                            <?php $is_current_admin = ($user['id'] === $current_admin_id); ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted">@<?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php if ($user['role'] == 'admin'): ?>
                                                        <span class="badge bg-primary">Admin</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">User</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($user['status'] == 'active'): ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Banned</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $user['post_count']; ?></td>
                                                <td class="small text-muted"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-primary" title="Edit"
                                                            data-bs-toggle="modal" data-bs-target="#editModal"
                                                            data-id="<?php echo $user['id']; ?>"
                                                            data-first_name="<?php echo htmlspecialchars($user['first_name']); ?>"
                                                            data-last_name="<?php echo htmlspecialchars($user['last_name']); ?>"
                                                            data-username="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                                                            data-email="<?php echo htmlspecialchars($user['email']); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="admin_manage_users.php?action=toggle_role&id=<?php echo $user['id']; ?>"
                                                            class="btn btn-outline-info <?php if ($is_current_admin) echo 'disabled'; ?>"
                                                            title="<?php echo $user['role'] == 'user' ? 'Promote to Admin' : 'Demote to User'; ?>">
                                                            <i class="fas fa-user-shield"></i>
                                                        </a>
                                                        <a href="admin_manage_users.php?action=toggle_status&id=<?php echo $user['id']; ?>"
                                                            class="btn btn-outline-warning <?php if ($is_current_admin) echo 'disabled'; ?>"
                                                            title="<?php echo $user['status'] == 'active' ? 'Ban User' : 'Unban User'; ?>">
                                                            <i class="fas <?php echo $user['status'] == 'active' ? 'fa-ban' : 'fa-check'; ?>"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-danger" title="Delete"
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                            data-id="<?php echo $user['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"
                                                            <?php if ($is_current_admin) echo 'disabled'; ?>>
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No users found matching your criteria.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="POST" action="admin_manage_users.php">
                    <input type="hidden" name="action" value="add_user">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="addUserModalLabel"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="addFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="addFirstName" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="addLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="addLastName" name="last_name" required>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="addEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="addEmail" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="addUsername" class="form-label">Username <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" id="addUsername" name="username">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="addPassword" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="addPassword" name="password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="addPasswordConfirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="addPasswordConfirm" name="password_confirm" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="addRole" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="addRole" name="role" required>
                                <option value="user" selected>User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <form method="POST" action="admin_manage_users.php">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="user_id" id="editUserId">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="editModalLabel"><i class="fas fa-edit me-2"></i>Edit User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="editFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editFirstName" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editLastName" name="last_name" required>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="editEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="editEmail" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editUsername" class="form-label">Username <span class="text-muted">(Optional)</span></label>
                                <input type="text" class="form-control" id="editUsername" name="username">
                            </div>
                        </div>
                        <small class="text-muted">Password and role are managed separately.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this user?</p>
                    <div class="alert alert-warning mb-0">
                        <strong>User:</strong> <span id="deleteUserName"></span>
                    </div>
                    <p class="text-danger mt-3 mb-0">
                        <small>This action cannot be undone. All their posts and comments will be anonymized.</small>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger"><i class="fas fa-trash me-1"></i>Delete User</a>
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
            document.getElementById('editUserId').value = button.getAttribute('data-id');
            document.getElementById('editFirstName').value = button.getAttribute('data-first_name');
            document.getElementById('editLastName').value = button.getAttribute('data-last_name');
            document.getElementById('editUsername').value = button.getAttribute('data-username');
            document.getElementById('editEmail').value = button.getAttribute('data-email');
        });

        // Handle delete modal data
        const deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const userId = button.getAttribute('data-id');
            const userName = button.getAttribute('data-name');

            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('confirmDeleteBtn').href = 'admin_manage_users.php?action=delete&id=' + userId;
        });
    </script>
</body>

</html>