<?php
session_start();
require_once 'db_connect.php';

// 🔒 Check admin
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Pagination
$reportsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $reportsPerPage;

try {
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
                <!-- Sticky Pagination Top -->
                <nav class="sticky-top py-2 bg-white border-bottom" style="z-index: 10;">
                    <ul class="pagination justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            No reports found.
                        </td>
                    </tr>

                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($reports as $r): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card card-report shadow-sm">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <strong><?= $r['first_name'] && $r['last_name'] ? htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) : 'Deleted User'; ?></strong>
                                                    <div class="small text-muted"><?= date('M j, Y H:i', strtotime($r['created_at'])); ?></div>
                                                </div>
                                                <span class="report-badge">Report</span>
                                            </div>

                                            <p class="mb-1"><strong>Post:</strong> <?= htmlspecialchars($r['post_type']); ?> (ID: <?= $r['post_id'] ?? 'N/A'; ?>)</p>
                                            <p class="mb-0"><strong>Reason:</strong> <?= htmlspecialchars($r['reason']); ?></p>
                                        </div>

                                        <div class="d-flex gap-2 mt-3">
                                            <button class="btn btn-sm btn-primary flex-grow-1" onclick="viewPost('<?= $r['post_type']; ?>', <?= $r['post_id']; ?>)">
                                                <i class="fas fa-eye me-1"></i> View
                                            </button>
                                            <button class="btn btn-sm btn-danger flex-grow-1" onclick="deletePost(<?= $r['post_id']; ?>, '<?= $r['post_type']; ?>')">
                                                <i class="fas fa-trash me-1"></i> Delete
                                            </button>
                                            <button class="btn btn-sm btn-success flex-grow-1" onclick="dismissReport(<?= $r['id']; ?>)">
                                                <i class="fas fa-check me-1"></i> Dismiss
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
        function viewPost(type, id) {
            alert(`View ${type} with ID ${id} (implement modal or redirect here)`);
        }

        function deletePost(id, type) {
            if (!confirm(`Are you sure you want to delete this ${type}?`)) return;
            // Example AJAX call placeholder
            fetch('delete_post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `post_id=${id}&post_type=${type}`
                }).then(res => res.json())
                .then(data => {
                    alert(data.message || `${type} deleted`);
                    location.reload();
                }).catch(err => console.error(err));
        }

        function dismissReport(reportId) {
            if (!confirm("Mark this report as handled?")) return;
            // Example AJAX call placeholder
            fetch('dismiss_report.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `report_id=${reportId}`
                }).then(res => res.json())
                .then(data => {
                    alert(data.message || "Report dismissed");
                    location.reload();
                }).catch(err => console.error(err));
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>