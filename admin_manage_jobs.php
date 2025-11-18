<?php
session_start();
require_once 'db_connect.php';

// 🔒 Ensure admin
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle AJAX create job
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_job') {
    header('Content-Type: application/json');
    try {
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $contact_info = $email;
        if (!empty($phone)) $contact_info .= ',' . $phone;

        $stmt = $pdo->prepare("INSERT INTO jobs (title, description, employer, contact_info, posted_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['employer'] ?? null,
            $contact_info,
            $_SESSION['currentUser']['id']
        ]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("UPDATE jobs SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $success_message = "Job deleted successfully!";
    } catch (PDOException $e) {
        $error_message = "Error deleting job: " . $e->getMessage();
    }
}

// Handle AJAX update job
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_job') {
    header('Content-Type: application/json');

    try {
        // Get email and phone separately
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $contact_info = $email;
        if (!empty($phone)) $contact_info .= ',' . $phone;

        // Prepare and execute update
        $stmt = $pdo->prepare("UPDATE jobs 
                               SET title = ?, description = ?, employer = ?, contact_info = ? 
                               WHERE id = ?");
        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['employer'] ?? null,
            $contact_info,
            $_POST['job_id']
        ]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Pagination & search
$jobs_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $jobs_per_page;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    $query = "SELECT j.*, u.first_name, u.last_name FROM jobs j LEFT JOIN users u ON j.posted_by = u.id WHERE j.deleted_at IS NULL";
    $params = [];
    if (!empty($search)) {
        $query .= " AND (j.title LIKE :search OR j.description LIKE :search OR j.employer LIKE :search)";
        $params[':search'] = "%$search%";
    }
    $query .= " ORDER BY j.created_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);
    foreach ($params as $key => $val) $stmt->bindValue($key, $val, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $jobs_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count total
    $count_query = "SELECT COUNT(*) FROM jobs WHERE deleted_at IS NULL";
    if (!empty($search)) $count_query .= " AND (title LIKE :search OR description LIKE :search OR employer LIKE :search)";
    $count_stmt = $pdo->prepare($count_query);
    if (!empty($search)) $count_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $count_stmt->execute();
    $total_jobs = (int)$count_stmt->fetchColumn();
    $total_pages = ceil($total_jobs / $jobs_per_page);
} catch (PDOException $e) {
    $jobs = [];
    $total_jobs = 0;
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Jobs - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin_dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Jobs Opportunities</h1>
                </div>

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
                                <input type="text" name="search" class="form-control" placeholder="Search title, description or employer..." value="<?= htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-8 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filter</button>
                                <button type="button" id="clearFilter" class="btn btn-secondary me-2">Reset</button>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fas fa-plus me-1"></i> Add Job</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Jobs Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 fw-bold text-primary">Jobs (<?= number_format($total_jobs); ?> total)</h6>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Employer</th>
                                    <th>Posted By</th>
                                    <th>Date</th>
                                    <!-- <th>Flag</th> -->
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($jobs)): ?>
                                    <?php foreach ($jobs as $job): ?>
                                        <tr>
                                            <td><?= $job['id']; ?></td>
                                            <td><?= htmlspecialchars($job['title']); ?></td>
                                            <td><?= strlen($job['description']) > 50 ? substr(htmlspecialchars($job['description']), 0, 50) . '...' : htmlspecialchars($job['description']); ?></td>
                                            <td><?= htmlspecialchars($job['employer']); ?></td>
                                            <td><?= $job['first_name'] && $job['last_name'] ? htmlspecialchars($job['first_name'] . ' ' . $job['last_name']) : '<em class="text-muted">Unknown</em>'; ?></td>
                                            <td class="small text-muted"><?= date('M j, Y', strtotime($job['created_at'])); ?></td>
                                            <!-- <td>
                                                <?php if ($job['is_flagged']): ?>
                                                    <a href="?action=unflag&id=<?= $job['id']; ?>" class="btn btn-success btn-sm" onclick="return confirm('Mark as safe?')"><i class="fas fa-check"></i></a>
                                                <?php else: ?>
                                                    <a href="?action=flag&id=<?= $job['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Flag this job?')"><i class="fas fa-flag"></i></a>
                                                <?php endif; ?>
                                            </td> -->
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-info viewBtn" data-bs-toggle="modal" data-bs-target="#viewModal"
                                                        data-id="<?= $job['id']; ?>" data-title="<?= htmlspecialchars($job['title'], ENT_QUOTES); ?>"
                                                        data-desc="<?= htmlspecialchars($job['description'], ENT_QUOTES); ?>"
                                                        data-employer="<?= htmlspecialchars($job['employer'], ENT_QUOTES); ?>"
                                                        data-contact="<?= htmlspecialchars($job['contact_info'], ENT_QUOTES); ?>"
                                                        data-author="<?= $job['first_name'] && $job['last_name'] ? htmlspecialchars($job['first_name'] . ' ' . $job['last_name'], ENT_QUOTES) : 'Unknown'; ?>"
                                                        data-date="<?= date('F j, Y g:i A', strtotime($job['created_at'])); ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-primary editBtn"
                                                        data-bs-toggle="modal" data-bs-target="#editModal"
                                                        data-id="<?= $job['id']; ?>" data-title="<?= htmlspecialchars($job['title'], ENT_QUOTES); ?>"
                                                        data-desc="<?= htmlspecialchars($job['description'], ENT_QUOTES); ?>"
                                                        data-employer="<?= htmlspecialchars($job['employer'], ENT_QUOTES); ?>"
                                                        data-contact="<?= htmlspecialchars($job['contact_info'], ENT_QUOTES); ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger deleteBtn" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                        data-id="<?= $job['id']; ?>" data-title="<?= htmlspecialchars($job['title'], ENT_QUOTES); ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-3x mb-3 d-block"></i>No jobs found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Previous</a></li>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a></li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>"><a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Next</a></li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="createForm">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add Job</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Job Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Employer</label>
                            <input type="text" class="form-control" name="employer">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="Email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone (optional)</label>
                            <input type="text" class="form-control" name="phone" id="Phone">
                        </div>
                        <input type="hidden" name="action" value="create_job">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Create Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View/Edit/Delete modals -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Job Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h4 id="jobTitle"></h4>
                    <p id="jobDesc"></p>
                    <p><strong>Employer:</strong> <span id="jobEmployer"></span></p>
                    <p><strong>Contact:</strong> <span id="jobContact"></span></p>
                    <p><strong>Posted By:</strong> <span id="jobAuthor"></span></p>
                    <p><strong>Date:</strong> <span id="jobDate"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="editForm">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Job</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="job_id" id="editJobId">
                        <input type="hidden" name="action" value="update_job">
                        <div class="mb-3">
                            <label class="form-label">Job Title</label>
                            <input type="text" class="form-control" name="title" id="editTitle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Employer</label>
                            <input type="text" class="form-control" name="employer" id="editEmployer">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone (optional)</label>
                            <input type="text" class="form-control" name="phone" id="editPhone">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Job</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash me-2"></i>Delete Job</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete "<span id="deleteJobTitle"></span>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>


    <!-- similar to safety reports but using job fields -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // CREATE JOB AJAX
            const createForm = document.getElementById('createForm');
            createForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(createForm);

                fetch('admin_manage_jobs.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('Job created successfully!');
                            window.location.reload(); // reload to show new job
                        } else {
                            alert('Error: ' + data.error);
                        }
                    })
                    .catch(err => alert('Request failed: ' + err));
            });

            // Populate Edit Modal
            const editModal = document.getElementById('editModal');

            editModal.addEventListener('show.bs.modal', function(event) {
                const btn = event.relatedTarget;

                document.getElementById('editJobId').value = btn.getAttribute('data-id');
                document.getElementById('editTitle').value = btn.getAttribute('data-title');
                document.getElementById('editDescription').value = btn.getAttribute('data-desc');
                document.getElementById('editEmployer').value = btn.getAttribute('data-employer');

                const contactInfo = btn.getAttribute('data-contact') || '';
                const contactParts = contactInfo.split(',');
                document.getElementById('editEmail').value = contactParts[0] || '';
                document.getElementById('editPhone').value = contactParts[1] || '';
            });



            // Handle Update via AJAX
            const editForm = document.getElementById('editForm');
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(editForm);

                fetch('admin_manage_jobs.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('Job updated successfully!');
                            window.location.reload();
                        } else {
                            alert('Error: ' + data.error);
                        }
                    })
                    .catch(err => alert('Request failed: ' + err));
            });

            // VIEW JOB DETAILS
            const viewModal = document.getElementById('viewModal');
            viewModal.addEventListener('show.bs.modal', function(event) {
                const btn = event.relatedTarget;
                document.getElementById('jobTitle').textContent = btn.getAttribute('data-title');
                document.getElementById('jobDesc').textContent = btn.getAttribute('data-desc');
                document.getElementById('jobEmployer').textContent = btn.getAttribute('data-employer');
                document.getElementById('jobContact').textContent = btn.getAttribute('data-contact');
                document.getElementById('jobAuthor').textContent = btn.getAttribute('data-author');
                document.getElementById('jobDate').textContent = btn.getAttribute('data-date');
            });

            // DELETE JOB CONFIRMATION
            const deleteBtns = document.querySelectorAll('.deleteBtn');
            const deleteModal = document.getElementById('deleteModal');
            let deleteJobId = null;

            deleteBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    deleteJobId = this.getAttribute('data-id');
                    document.getElementById('deleteJobTitle').textContent = this.getAttribute('data-title');
                });
            });

            const confirmDeleteBtn = document.getElementById('confirmDelete');
            confirmDeleteBtn.addEventListener('click', function() {
                if (!deleteJobId) return;

                // Redirect with GET parameter for PHP delete handling
                window.location.href = `admin_manage_jobs.php?action=delete&id=${deleteJobId}`;
            });

            // RESET FILTER
            const clearFilterBtn = document.getElementById('clearFilter');
            clearFilterBtn.addEventListener('click', function() {
                window.location.href = 'admin_manage_jobs.php';
            });
        });
    </script>
</body>

</html>