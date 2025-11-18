<?php
session_start();
require_once 'db_connect.php'; // PDO connection

// Filter & search
$severityFilter = $_GET['severity'] ?? 'all';
$timeFilter = $_GET['time'] ?? 'all';
$search = trim($_GET['search'] ?? '');

// Pagination
$reports_per_page = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $reports_per_page;

// Build query
$where = "WHERE r.deleted_at IS NULL";
$params = [];

if ($severityFilter !== 'all') {
  if ($severityFilter === 'high') {
    $where .= " AND (r.report_type LIKE '%Fire%' OR r.report_type LIKE '%Hazard%' OR r.report_type LIKE '%Warning%')";
  } elseif ($severityFilter === 'medium') {
    $where .= " AND (r.report_type LIKE '%Traffic%' OR r.report_type LIKE '%Advisory%')";
  } elseif ($severityFilter === 'low') {
    $where .= " AND r.report_type LIKE '%Test%'";
  }
}


if ($timeFilter === 'today') $where .= " AND DATE(r.created_at) = CURDATE()";
elseif ($timeFilter === 'week') $where .= " AND YEARWEEK(r.created_at, 1) = YEARWEEK(CURDATE(), 1)";
elseif ($timeFilter === 'month') $where .= " AND MONTH(r.created_at) = MONTH(CURDATE()) AND YEAR(r.created_at) = YEAR(CURDATE())";

if ($search !== '') {
  $where .= " AND (r.report_type LIKE :search OR r.description LIKE :search OR r.location LIKE :search)";
  $params[':search'] = "%$search%";
}

try {
  // Fetch reports
  $query = "SELECT r.*, u.first_name, u.last_name 
              FROM safety_reports r 
              LEFT JOIN users u ON r.created_by = u.id
              $where 
              ORDER BY r.created_at DESC
              LIMIT :limit OFFSET :offset";
  $stmt = $pdo->prepare($query);
  foreach ($params as $k => $v) $stmt->bindValue($k, $v);
  $stmt->bindValue(':limit', $reports_per_page, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
  $stmt->execute();
  $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Count total
  $countQuery = "SELECT COUNT(*) FROM safety_reports r $where";
  $countStmt = $pdo->prepare($countQuery);
  foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
  $countStmt->execute();
  $total_reports = (int)$countStmt->fetchColumn();
  $total_pages = ceil($total_reports / $reports_per_page);
} catch (PDOException $e) {
  $reports = [];
  $total_reports = 0;
  $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Public Safety Alerts - AgoraBoard</title>
  <link rel="stylesheet" href="assets/dashboard.css?v=<?php echo time(); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --sage: #10b981;
      --sage-dark: #047857;
      --sage-light: #34d399;
      --cream: #f8f9f6;
      --muted: #6c757d;
      --text-dark: #2e2e2e;
    }

    .page-header h1 {
      font-weight: 700;
      color: var(--sage-dark);
    }

    .page-header p {
      color: var(--muted);
    }

    .filter-card,
    .alert-card {
      background: var(--cream);
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .filter-card h5 {
      font-weight: 600;
      color: var(--sage-dark);
    }

    .btn-gradient {
      background: linear-gradient(135deg, var(--sage), var(--sage-dark));
      color: #fff;
      border: none;
      border-radius: 50px;
      padding: 0.5rem 1.2rem;
      transition: 0.3s;
    }

    .btn-gradient:hover {
      background: linear-gradient(135deg, var(--sage-light), var(--sage-dark));
      color: #fff;
      transform: translateY(-2px);
    }

    .alert-icon {
      font-size: 1.2rem;
      margin-right: 8px;
    }

    /* Severity badges */
    .badge-high {
      background-color: #dc2626;
      /* stronger red */
      color: #fff;
      font-weight: 600;
    }

    .badge-medium {
      background-color: #f59e0b;
      /* amber/orange */
      color: #fff;
      font-weight: 600;
    }

    .badge-low {
      background-color: #16a34a;
      /* green */
      color: #fff;
      font-weight: 600;
    }

    footer {
      flex-shrink: 0;
      padding: 1rem 0;

      text-align: center;
    }
  </style>
</head>

<body>
  <div class="dashboard-layout d-flex">
    <?php include 'user_sidebar.php'; ?>
    <div class="main-content flex-grow-1 p-4">

      <div class="page-header">
        <h1><i class="fas fa-shield-alt me-2"></i>Public Safety Alerts</h1>
        <p>Stay updated with the latest safety advisories and community warnings.</p>
        <small class="text-secondary"><?= $total_reports; ?> active alerts found</small>
      </div>

      <div class="filter-card">
        <h5><i class="fas fa-filter me-2 text-success"></i>Filter Alerts</h5>
        <form method="GET" class="row mt-3">
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Severity Level</label>
            <select name="severity" class="form-select">
              <option value="all" <?= $severityFilter === 'all' ? 'selected' : ''; ?>>All Levels</option>
              <option value="high" <?= $severityFilter === 'high' ? 'selected' : ''; ?>>High</option>
              <option value="medium" <?= $severityFilter === 'medium' ? 'selected' : ''; ?>>Medium</option>
              <option value="low" <?= $severityFilter === 'low' ? 'selected' : ''; ?>>Low</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">Time Period</label>
            <select name="time" class="form-select">
              <option value="all" <?= $timeFilter === 'all' ? 'selected' : ''; ?>>All Dates</option>
              <option value="today" <?= $timeFilter === 'today' ? 'selected' : ''; ?>>Today</option>
              <option value="week" <?= $timeFilter === 'week' ? 'selected' : ''; ?>>This Week</option>
              <option value="month" <?= $timeFilter === 'month' ? 'selected' : ''; ?>>This Month</option>
            </select>
          </div>
          <div class="col-md-12 d-flex justify-content-end">
            <button class="btn btn-gradient px-4"><i class="fas fa-filter me-2"></i>Apply Filters</button>
          </div>
        </form>
      </div>

      <div id="alerts-container">
        <?php if (!empty($reports)): ?>
          <?php foreach ($reports as $r): ?>
            <?php
            $severityClass = 'badge-low';
            if (stripos($r['report_type'], 'Fire') !== false || stripos($r['report_type'], 'Hazard') !== false || stripos($r['report_type'], 'Warning') !== false) $severityClass = 'badge-high';
            elseif (stripos($r['report_type'], 'Traffic') !== false || stripos($r['report_type'], 'Advisory') !== false) $severityClass = 'badge-medium';
            ?>
            <div class="alert-card" data-severity="<?= $severityClass; ?>">
              <h5 class="fw-bold <?= $severityClass === 'badge-high' ? 'text-danger' : ($severityClass === 'badge-medium' ? 'text-warning' : ''); ?>">
                <i class="fas fa-exclamation-triangle alert-icon"></i><?= htmlspecialchars($r['report_type']); ?>
              </h5>
              <p class="text-muted mb-2"><?= htmlspecialchars($r['description']); ?></p>
              <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">Issued: <?= date('M j, Y', strtotime($r['created_at'])); ?> | Location: <?= htmlspecialchars($r['location']); ?></small>
                <span class="badge <?= $severityClass; ?> px-3 py-2 rounded-pill"><?= ucfirst(str_replace('badge-', '', $severityClass)); ?></span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-center text-muted py-4"><i class="fas fa-inbox fa-3x mb-3 d-block"></i>No alerts found</p>
        <?php endif; ?>
      </div>

      <?php if ($total_pages > 1): ?>
        <nav aria-label="Reports pagination" class="mt-4">
          <ul class="pagination justify-content-center">
            <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
              <a class="page-link" href="?page=<?= $page - 1; ?>&severity=<?= $severityFilter; ?>&time=<?= $timeFilter; ?>">Previous</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?= $page == $i ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?= $i; ?>&severity=<?= $severityFilter; ?>&time=<?= $timeFilter; ?>"><?= $i; ?></a>
              </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : ''; ?>">
              <a class="page-link" href="?page=<?= $page + 1; ?>&severity=<?= $severityFilter; ?>&time=<?= $timeFilter; ?>">Next</a>
            </li>
          </ul>
        </nav>
      <?php endif; ?>

      <footer class="mt-4 text-center text-muted">
        &copy; 2025 AgoraBoard — Public Safety & Community Bulletin
      </footer>

    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>