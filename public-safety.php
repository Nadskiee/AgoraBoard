<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Public Safety Alerts - AgoraBoard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8fafc;
    }
    .safety-container {
      padding: 2rem 0;
    }
    .filter-card, .alert-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      padding: 2rem;
      margin-bottom: 1.5rem;
    }
    .alert-card:hover {
      transform: translateY(-3px);
      transition: 0.3s ease;
    }
    .badge-high {
      background-color: #f8d7da;
      color: #c82333;
    }
    .badge-low {
      background-color: #d1ecf1;
      color: #0c5460;
    }
    .btn-gradient {
      background: linear-gradient(135deg, #ff6a00 0%, #ee0979 100%);
      border: none;
      color: white;
    }
    .btn-gradient:hover {
      background: linear-gradient(135deg, #e65c00 0%, #d6076e 100%);
      color: white;
      transform: translateY(-2px);
    }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="container text-center my-5">
    <h1 class="fw-bold"><i class="fas fa-shield-alt me-2 text-primary"></i>Public Safety Alerts</h1>
    <p class="text-muted">Stay informed with important safety updates and notices</p>
    <small class="text-secondary">6 alerts found</small>
  </div>

  <!-- Filter Section -->
  <div class="container mb-4">
    <div class="filter-card">
      <h5 class="fw-semibold mb-3"><i class="fas fa-filter me-2 text-primary"></i>Filter Alerts</h5>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Severity Level</label>
          <select class="form-select">
            <option selected>All Levels</option>
            <option>High</option>
            <option>Medium</option>
            <option>Low</option>
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label fw-semibold">Time Period</label>
          <select class="form-select">
            <option selected>All Dates</option>
            <option>Today</option>
            <option>This Week</option>
            <option>This Month</option>
          </select>
        </div>
      </div>
      <div class="d-flex justify-content-end">
        <button class="btn btn-gradient"><i class="fas fa-filter me-2"></i>Apply Filters</button>
      </div>
    </div>
  </div>

  <!-- Alerts Section -->
  <div class="container">
    <div class="alert-card">
      <h5 class="fw-bold text-danger"><i class="fas fa-cloud-showers-heavy me-2"></i>Severe Weather Warning</h5>
      <p class="text-muted mb-2">Heavy rainfall expected this evening. Residents are advised to stay indoors and avoid low-lying areas prone to flooding. Emergency services are on standby.</p>
      <div class="d-flex justify-content-between">
        <small class="text-muted">Issued: Jan 15, 2025 | Location: All Districts</small>
        <span class="badge badge-high px-3 py-2 rounded-pill">High</span>
      </div>
    </div>

    <div class="alert-card">
      <h5 class="fw-bold text-primary"><i class="fas fa-broadcast-tower me-2"></i>Emergency Alert System Test</h5>
      <p class="text-muted mb-2">The city will conduct a test of the emergency alert system today at 2:00 PM. This is only a test, no action is required.</p>
      <div class="d-flex justify-content-between">
        <small class="text-muted">Issued: Jan 15, 2025 | Location: Citywide</small>
        <span class="badge badge-low px-3 py-2 rounded-pill">Low</span>
      </div>
    </div>

    <div class="alert-card">
      <h5 class="fw-bold text-warning"><i class="fas fa-car-crash me-2"></i>Traffic Advisory - Highway 101</h5>
      <p class="text-muted mb-2">Expect delays on Highway 101 northbound due to multi-vehicle accident. Emergency crews are on scene. Consider alternate routes.</p>
      <div class="d-flex justify-content-between">
        <small class="text-muted">Issued: Jan 15, 2025 | Location: Highway 101</small>
        <span class="badge badge-high px-3 py-2 rounded-pill">High</span>
      </div>
    </div>
  </div>

  <!-- Back Button -->
  <div class="container text-center my-5">
    <a href="dashboard.php" class="btn btn-gradient px-4 py-2">
      <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
  </div>

  <footer class="bg-dark text-light py-4 text-center mt-5">
    &copy; 2025 AgoraBoard
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
