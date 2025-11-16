<?php
session_start();
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
    
    /* These styles are for the PAGE CONTENT, not the layout */
    .page-header h1 {
      font-weight: 700;
      color: var(--sage-dark);
    }
    .page-header p { color: var(--muted); }

    .filter-card, .alert-card {
      background: var(--cream);
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
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
    .alert-icon { font-size: 1.2rem; margin-right: 8px; }
    .badge-high { background-color: #fee2e2; color: #b91c1c; }
    .badge-medium { background-color: #fef9c3; color: #854d0e; }
    .badge-low { background-color: #dcfce7; color: #166534; }
  </style>
</head>

<body>
<div class="dashboard-layout d-flex">

  <?php include 'user_sidebar.php'; ?>

  <div class="main-content flex-grow-1 p-4">
    <div class="page-header">
      <h1><i class="fas fa-shield-alt me-2"></i>Public Safety Alerts</h1>
      <p>Stay updated with the latest safety advisories and community warnings.</p>
      <small class="text-secondary">6 active alerts found</small>
    </div>

    <div class="filter-card">
      <h5><i class="fas fa-filter me-2 text-success"></i>Filter Alerts</h5>
      <div class="row mt-3">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Severity Level</label>
          <select id="severityFilter" class="form-select">
            <option value="all">All Levels</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Time Period</label>
          <select id="timeFilter" class="form-select">
            <option value="all">All Dates</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
          </select>
        </div>
      </div>
      <div class="d-flex justify-content-end">
        <button class="btn btn-gradient px-4" onclick="applyFilters()"><i class="fas fa-filter me-2"></i>Apply Filters</button>
      </div>
    </div>

    <div id="alerts-container">
      <div class="alert-card" data-severity="high">
        <h5 class="fw-bold text-danger"><i class="fas fa-cloud-showers-heavy alert-icon"></i>Severe Weather Warning</h5>
        <p class="text-muted mb-2">Heavy rainfall expected this evening. Residents are advised to stay indoors and avoid low-lying areas.</p>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">Issued: Nov 3, 2025 | Location: All Districts</small>
          <span class="badge badge-high px-3 py-2 rounded-pill">High</span>
        </div>
      </div>

      <div class="alert-card" data-severity="low">
        <h5 class="fw-bold"><i class="fas fa-broadcast-tower alert-icon"></i>Emergency Alert System Test</h5>
        <p class="text-muted mb-2">Scheduled test of the emergency alert system at 2:00 PM today. No action required.</p>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">Issued: Nov 3, 2025 | Location: Citywide</small>
          <span class="badge badge-low px-3 py-2 rounded-pill">Low</span>
        </div>
      </div>

      <div class="alert-card" data-severity="medium">
        <h5 class="fw-bold text-warning"><i class="fas fa-car-crash alert-icon"></i>Traffic Advisory - Highway 101</h5>
        <p class="text-muted mb-2">Expect delays due to a multi-vehicle accident. Emergency responders are on site.</p>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <small class="text-muted">Issued: Nov 3, 2025 | Location: Highway 101</small>
          <span class="badge badge-medium px-3 py-2 rounded-pill">Medium</span>
        </div>
      </div>
    </div>

    <footer class="mt-4 text-center text-muted">
      &copy; 2025 AgoraBoard — Public Safety & Community Bulletin
    </footer>
  </div>
</div>

<script>
  function applyFilters() {
    const severity = document.getElementById("severityFilter").value;
    const alerts = document.querySelectorAll(".alert-card");

    alerts.forEach(alert => {
      const alertSeverity = alert.getAttribute("data-severity");
      if (severity === "all" || alertSeverity === severity) {
        alert.style.display = "block";
      } else {
        alert.style.display = "none";
      }
    });
  }

  // This function is still needed for the logout button, which is now inside user_sidebar.php
  function confirmLogout() {
      if (confirm("Are you sure you want to logout?")) {
          document.getElementById("logoutForm").submit();
      }
  }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>