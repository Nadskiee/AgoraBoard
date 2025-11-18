<?php
session_start();
require_once 'db_connect.php';

// 🔒 Ensure admin
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle create report
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_report') {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare("INSERT INTO safety_reports (report_type, description, location, created_by) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['report_type'], $_POST['description'], $_POST['location'], $_SESSION['currentUser']['id']]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}


// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE safety_reports SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $success_message = "Report deleted successfully!";
    } catch (PDOException $e) {
        $error_message = "Error deleting report: " . $e->getMessage();
    }
}

// Handle AJAX update report
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_report') {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare("UPDATE safety_reports SET report_type = ?, description = ?, location = ? WHERE id = ?");
        $stmt->execute([$_POST['report_type'], $_POST['description'], $_POST['location'], $_POST['report_id']]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Pagination
$reports_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $reports_per_page;

// Search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $query = "SELECT r.*, u.first_name, u.last_name 
              FROM safety_reports r
              LEFT JOIN users u ON r.created_by = u.id
              WHERE r.deleted_at IS NULL";

    $params = [];
    if (!empty($search)) {
        $query .= " AND (r.report_type LIKE :search OR r.description LIKE :search)";
        $params[':search'] = "%$search%";
    }

    $query .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val, PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $reports_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count total
    $count_query = "SELECT COUNT(*) FROM safety_reports WHERE deleted_at IS NULL";
    if (!empty($search)) $count_query .= " AND (report_type LIKE :search OR description LIKE :search)";
    $count_stmt = $pdo->prepare($count_query);
    if (!empty($search)) $count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $count_stmt->execute();
    $total_reports = (int)$count_stmt->fetchColumn();
    $total_pages = ceil($total_reports / $reports_per_page);
} catch (PDOException $e) {
    $reports = [];
    $total_pages = 1;
    $total_reports = 0;
    error_log("Safety reports query error: " . $e->getMessage());
}

$adminName = $_SESSION['currentUser']['name'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Safety Reports - Admin</title>
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
                    <h1 class="h2">Manage Safety Reports</h1>
                </div>

                <!-- Success / Error -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Filter/Search -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search report type or description..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-8 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <button type="button" id="clearFilter" class="btn btn-secondary me-2">
                                    Reset
                                </button>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal">
                                    <i class="fas fa-plus me-1"></i> Create New
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Reports Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary">
                            Reports (<?php echo number_format($total_reports); ?> total)
                        </h6>
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                            <i class="fas fa-plus me-1"></i> Add Report
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Location</th>
                                        <th>Reported By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="reportsTableBody">
                                    <?php if (!empty($reports)): ?>
                                        <?php foreach ($reports as $r): ?>
                                            <tr id="reportRow_<?php echo $r['id']; ?>">
                                                <td><?php echo $r['id']; ?></td>
                                                <td><?php echo htmlspecialchars($r['report_type']); ?></td>
                                                <td><?php echo strlen($r['description']) > 50 ? substr(htmlspecialchars($r['description']), 0, 50) . '...' : htmlspecialchars($r['description']); ?></td>
                                                <td><?php echo htmlspecialchars($r['location']); ?></td>
                                                <td><?php echo $r['first_name'] && $r['last_name'] ? htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) : '<em class="text-muted">Unknown</em>'; ?></td>
                                                <td class="small text-muted"><?php echo date('M j, Y', strtotime($r['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-info" title="View" data-bs-toggle="modal" data-bs-target="#viewModal"
                                                            data-id="<?php echo $r['id']; ?>"
                                                            data-type="<?php echo htmlspecialchars($r['report_type'], ENT_QUOTES); ?>"
                                                            data-desc="<?php echo htmlspecialchars($r['description'], ENT_QUOTES); ?>"
                                                            data-loc="<?php echo htmlspecialchars($r['location'], ENT_QUOTES); ?>"
                                                            data-author="<?php echo $r['first_name'] && $r['last_name'] ? htmlspecialchars($r['first_name'] . ' ' . $r['last_name'], ENT_QUOTES) : 'Unknown'; ?>"
                                                            data-date="<?php echo date('F j, Y \a\t g:i A', strtotime($r['created_at'])); ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary editBtn" title="Edit"
                                                            data-id="<?php echo $r['id']; ?>"
                                                            data-type="<?php echo htmlspecialchars($r['report_type'], ENT_QUOTES); ?>"
                                                            data-desc="<?php echo htmlspecialchars($r['description'], ENT_QUOTES); ?>"
                                                            data-loc="<?php echo htmlspecialchars($r['location'], ENT_QUOTES); ?>">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                            data-id="<?php echo $r['id']; ?>"
                                                            data-type="<?php echo htmlspecialchars($r['report_type'], ENT_QUOTES); ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>No reports found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Reports pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?= $page - 1; ?>&search=<?= urlencode($search); ?>">Previous</a>
                                    </li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>"><?= $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?= $page + 1; ?>&search=<?= urlencode($search); ?>">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="createForm">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="createModalLabel">
                            <i class="fas fa-plus me-2"></i>Add Safety Report
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="report_type" class="form-label">Report Type</label>
                            <input type="text" class="form-control" name="report_type" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" name="location">
                        </div>
                        <input type="hidden" name="action" value="create_report">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-plus me-1"></i>Create Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>View Safety Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h4 id="viewType"></h4>
                    <p id="viewDesc"></p>
                    <p><strong>Location:</strong> <span id="viewLoc"></span></p>
                    <p class="text-muted"><strong>Reported by:</strong> <span id="viewAuthor"></span> | <strong>Date:</strong> <span id="viewDate"></span></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-1"></i>Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm">
                    <input type="hidden" name="report_id" id="editId">
                    <input type="hidden" name="action" value="update_report">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Report Type</label>
                            <input type="text" class="form-control" name="report_type" id="editType" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editDesc" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" id="editLoc">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this report?</p>
                    <div class="alert alert-warning mb-0"><strong>Report:</strong> <span id="deleteType"></span></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Clear filter
        document.getElementById('clearFilter').addEventListener('click', () => {
            window.location = 'admin_manage_safety.php';
        });

        document.getElementById('createForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('admin_manage_safety.php', {
                    method: 'POST',
                    body: formData
                }).then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert('Error: ' + data.error);
                });
        });


        // View modal
        var viewModal = document.getElementById('viewModal');
        viewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('viewType').textContent = button.getAttribute('data-type');
            document.getElementById('viewDesc').textContent = button.getAttribute('data-desc');
            document.getElementById('viewLoc').textContent = button.getAttribute('data-loc');
            document.getElementById('viewAuthor').textContent = button.getAttribute('data-author');
            document.getElementById('viewDate').textContent = button.getAttribute('data-date');
        });

        // Edit modal
        var editButtons = document.querySelectorAll('.editBtn');
        editButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('editId').value = btn.getAttribute('data-id');
                document.getElementById('editType').value = btn.getAttribute('data-type');
                document.getElementById('editDesc').value = btn.getAttribute('data-desc');
                document.getElementById('editLoc').value = btn.getAttribute('data-loc');
                new bootstrap.Modal(document.getElementById('editModal')).show();
            });
        });

        // AJAX save edit
        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('admin_manage_safety.php', {
                    method: 'POST',
                    body: formData
                }).then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                    else alert('Error: ' + data.error);
                });
        });

        // Delete modal
        var deleteModal = document.getElementById('deleteModal');
        deleteModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            document.getElementById('deleteType').textContent = button.getAttribute('data-type');
            document.getElementById('confirmDelete').href = 'admin_manage_safety.php?action=delete&id=' + button.getAttribute('data-id');
        });
    </script>
</body>

</html>