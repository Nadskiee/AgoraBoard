<?php
session_start();
require_once 'db_connect.php';

// 🔒 Check admin
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Handle POST actions first
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? '';
    $post_id = $_POST['post_id'] ?? null;
    $post_type = $_POST['post_type'] ?? null;
    $report_id = $_POST['report_id'] ?? null;

    try {
        if ($action === 'delete_post' && $post_id && $post_type) {
            $table = ($post_type === 'comment') ? 'comments' : (($post_type === 'general') ? 'community_posts' : null);

            if (!$table) {
                echo json_encode(['success' => false, 'message' => 'Unknown post type.']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE $table SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$post_id]);

            // ✅ Friendly label mapping
            $labelMap = [
                'general' => 'Community post',
                'comment' => 'Comment',
                'user' => 'User profile',
                'poll' => 'Poll',
                'event' => 'Event',
                'job' => 'Job post',
                'lost_and_found' => 'Lost & found post',
                'safety_report' => 'Safety report',
                'volunteering' => 'Volunteer post'
            ];
            $label = $labelMap[$post_type] ?? ucfirst($post_type);

            echo json_encode(['success' => true, 'message' => "$label deleted"]);
            exit;
        }

        if ($action === 'delete_report' && $report_id) {
            $stmt = $pdo->prepare("UPDATE reports SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$report_id]);
            echo json_encode(['success' => true, 'message' => 'Report deleted']);
            exit;
        }

        if ($action === 'dismiss_report' && $report_id) {
            $stmt = $pdo->prepare("UPDATE reports SET dismissed_at = NOW() WHERE id = ?");
            $stmt->execute([$report_id]);
            echo json_encode(['success' => true, 'message' => 'Report dismissed']);
            exit;
        }

        if ($action === 'undismiss_report' && $report_id) {
            $stmt = $pdo->prepare("UPDATE reports SET dismissed_at = NULL WHERE id = ?");
            $stmt->execute([$report_id]);
            echo json_encode(['success' => true, 'message' => 'Report reactivated']);
            exit;
        }

        if ($action === 'fetch_post' && $post_id && $post_type) {
            switch ($post_type) {
                case 'comment':
                    $table = 'comments';
                    break;
                case 'poll':
                case 'event':
                case 'job':
                case 'lost_and_found':
                case 'safety_report':
                case 'volunteering':
                    $table = 'community_posts';
                    break;
                case 'user':
                    echo json_encode(['success' => false, 'message' => 'User reports are not viewable as content.']);
                    exit;
                default:
                    echo json_encode(['success' => false, 'message' => 'Unknown post type.']);
                    exit;
            }

            $stmt = $pdo->prepare("SELECT content FROM $table WHERE id = ? AND deleted_at IS NULL");
            $stmt->execute([$post_id]);
            $content = $stmt->fetchColumn();
            echo json_encode(['success' => true, 'content' => htmlspecialchars($content ?? 'Not found')]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Invalid action or missing parameters.']);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        exit;
    }
}


// Pagination
$reportsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $reportsPerPage;

try {
    // Base query to get reports with reporter info
    $query = "
        SELECT r.*, u.first_name, u.last_name
        FROM reports r
        LEFT JOIN users u ON r.reporter_id = u.id
        ORDER BY r.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $reportsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalReports = (int)$pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
    $totalPages = ceil($totalReports / $reportsPerPage);
} catch (PDOException $e) {
    $reports = [];
    $totalPages = 1;
    error_log("Error fetching reports: " . $e->getMessage());
}

$status_filter = $_GET['status'] ?? '';
$search_term = trim($_GET['search'] ?? '');

$where_conditions = [];
$where_conditions[] = 'r.deleted_at IS NULL';
$params = [];

if ($status_filter === 'active') {
    $where_conditions[] = 'r.dismissed_at IS NULL';
} elseif ($status_filter === 'dismissed') {
    $where_conditions[] = 'r.dismissed_at IS NOT NULL';
}

if (!empty($search_term)) {
    $where_conditions[] = '(r.reason LIKE :search OR r.post_type LIKE :search)';
    $params[':search'] = "%$search_term%";
}

$query = "
    SELECT r.*, 
           u.first_name AS reporter_first_name, u.last_name AS reporter_last_name,
           a.first_name AS author_first_name, a.last_name AS author_last_name,
           p.title, p.content AS post_content, p.is_pinned, p.deleted_at AS post_deleted_at,
           c.name AS category_name
    FROM reports r
    LEFT JOIN users u ON r.reporter_id = u.id
    LEFT JOIN community_posts p ON r.post_id = p.id AND r.post_type = 'general'
    LEFT JOIN users a ON p.created_by = a.id
    LEFT JOIN categories c ON p.category_id = c.id
";


if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $reportsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pagination function
function renderPagination($totalPages, $currentPage)
{
    if ($totalPages <= 1) return '';
    echo '<nav class="mb-3"><ul class="pagination justify-content-center">';
    for ($i = 1; $i <= $totalPages; $i++) {
        $active = $i === $currentPage ? 'active' : '';
        echo "<li class='page-item $active'><a class='page-link' href='?page=$i'>$i</a></li>";
    }
    echo '</ul></nav>';
}

$adminName = $_SESSION['currentUser']['name'] ?? "Admin";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Reports - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/admin_dashboard.css?v=<?php echo time(); ?>">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card-report {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            transition: transform 0.2s;
        }

        .card-report:hover {
            transform: scale(1.01);
        }


        .btn-group-vertical .btn {
            width: 100%;
            margin-bottom: 0.25rem;
        }

        .report-badge {
            background-color: #dc3545;
            color: #fff;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .timestamp {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Reports</h1>
                </div>
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <form method="GET" action="admin_manage_reports.php" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Reason, post type..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Reports</option>
                                    <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="dismissed" <?= ($_GET['status'] ?? '') === 'dismissed' ? 'selected' : ''; ?>>Dismissed</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i> Filter
                                </button>
                                <a href="admin_manage_reports.php" class="btn btn-secondary">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            No reports found.
                        </td>
                    </tr>

                <?php else: ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 fw-bold text-primary">All Reports (<?= count($reports); ?>)</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Reporter</th>
                                            <th>Post Type</th>
                                            <th>Post ID</th>
                                            <th>Reason</th>
                                            <th>Reported At</th>
                                            <th>Actions</th>
                                        </tr>

                                    </thead>
                                    <tbody>
                                        <?php foreach ($reports as $r): ?>
                                            <tr class="<?= $r['dismissed_at'] ? 'table-secondary' : ''; ?>">
                                                <td>
                                                    <strong><?= $r['reporter_first_name'] && $r['reporter_last_name'] ? htmlspecialchars($r['reporter_first_name'] . ' ' . $r['reporter_last_name']) : 'Deleted User'; ?></strong>
                                                    <?php if ($r['dismissed_at']): ?>
                                                        <span class="badge bg-success ms-2">Dismissed</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($r['post_type']); ?></td>
                                                <td><?= $r['post_id'] ?? 'N/A'; ?></td>
                                                <td><?= htmlspecialchars($r['reason']); ?></td>
                                                <td class="small text-muted"><?= date('M j, Y H:i', strtotime($r['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button"
                                                            class="btn btn-outline-info"
                                                            title="View"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#viewModal"
                                                            data-post-id="<?= $r['post_id']; ?>"
                                                            data-post-title="<?= htmlspecialchars($r['title'] ?? 'Untitled'); ?>"
                                                            data-post-content="<?= htmlspecialchars($r['post_content'] ?? 'No content available.'); ?>"
                                                            data-post-category="<?= htmlspecialchars($r['category_name'] ?? 'Uncategorized'); ?>"
                                                            data-post-author="<?= ($r['author_first_name'] && $r['author_last_name']) ? htmlspecialchars($r['author_first_name'] . ' ' . $r['author_last_name']) : 'Deleted User'; ?>"
                                                            data-post-date="<?= date('F j, Y \a\t g:i A', strtotime($r['created_at'])); ?>"
                                                            data-post-deleted="<?= !empty($r['post_deleted_at']) ? '1' : '0'; ?>"
                                                            data-post-pinned="<?= !empty($r['is_pinned']) ? '1' : '0'; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>


                                                        <button class="btn btn-outline-danger" title="Delete Post"
                                                            onclick="deletePost(<?= $r['post_id']; ?>, '<?= $r['post_type']; ?>')"
                                                            <?= $r['dismissed_at'] ? 'disabled' : ''; ?>>
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>

                                                        <button class="btn btn-outline-danger" title="Delete Report"
                                                            onclick="deleteReport(<?= $r['id']; ?>)"
                                                            <?= $r['dismissed_at'] ? 'disabled' : ''; ?>>
                                                            <i class="fas fa-file-excel"></i>
                                                        </button>
                                                        <?php if ($r['dismissed_at']): ?>
                                                            <button class="btn btn-outline-secondary" title="Undismiss"
                                                                onclick="undismissReport(<?= $r['id']; ?>)">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <button class="btn btn-outline-success" title="Dismiss"
                                                                onclick="dismissReport(<?= $r['id']; ?>)">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Reports pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?= $page - 1; ?>&status=<?= urlencode($_GET['status'] ?? '') ?>&search=<?= urlencode($_GET['search'] ?? '') ?>">Previous</a>
                            </li>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?= $i; ?>&status=<?= urlencode($_GET['status'] ?? '') ?>&search=<?= urlencode($_GET['search'] ?? '') ?>"><?= $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?= $page + 1; ?>&status=<?= urlencode($_GET['status'] ?? '') ?>&search=<?= urlencode($_GET['search'] ?? '') ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
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



    <script>
        // Handle view modal data
        const viewModal = document.getElementById('viewModal');
        viewModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const isDeleted = button.getAttribute('data-post-deleted') === '1';
            const contentElement = document.getElementById('viewPostContent');

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

            if (isDeleted) {
                contentElement.innerHTML = '<div class="alert alert-warning"><i class="fas fa-trash-alt me-1"></i>This post has been deleted.</div>';
            } else {
                contentElement.textContent = button.getAttribute('data-post-content');
            }
        });

        function deletePost(id, type) {
            const labelMap = {
                general: 'community post',
                comment: 'comment',
                user: 'user profile',
                poll: 'poll',
                event: 'event',
                job: 'job post',
                lost_and_found: 'lost & found post',
                safety_report: 'safety report',
                volunteering: 'volunteer post'
            };

            const label = labelMap[type] || type;

            if (!confirm(`Delete this ${label}?`)) return;

            fetch('admin_manage_reports.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=delete_post&post_id=${id}&post_type=${type}`
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message || `${label} deleted`);
                    location.reload();
                })
                .catch(err => console.error(err));
        }

        function deleteReport(reportId) {
            if (!confirm("Delete this report?")) return;
            fetch('admin_manage_reports.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=delete_report&report_id=${reportId}`
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message || "Report deleted");
                    location.reload();
                })
                .catch(err => console.error(err));
        }


        function dismissReport(reportId) {
            if (!confirm("Mark this report as handled?")) return;
            fetch('admin_manage_reports.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=dismiss_report&report_id=${reportId}`
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message || "Report dismissed");
                    location.reload();
                })
                .catch(err => console.error(err));
        }

        function undismissReport(reportId) {
            if (!confirm("Reactivate this report?")) return;
            fetch('admin_manage_reports.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=undismiss_report&report_id=${reportId}`
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message || "Report reactivated");
                    location.reload();
                })
                .catch(err => console.error(err));
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>