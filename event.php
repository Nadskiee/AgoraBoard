<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Events - AgoraBoard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .events-container {
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 2rem 0;
    }
    .events-card {
      background: white;
      border-radius: 15px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }
    .event-card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      transition: transform 0.3s ease;
      margin-bottom: 1.5rem;
    }
    .event-card:hover { transform: translateY(-5px); }
    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
      transform: translateY(-2px);
    }
    .category-filter {
      background: white;
      border-radius: 10px;
      padding: 1.5rem;
      box-shadow: 0 5px 15px rgba(0,0,0,0.08);
      margin-bottom: 2rem;
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold text-primary" href="index.php">
        <i class="fas fa-bullhorn me-2"></i>AgoraBoard
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="index.php#home">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#announcements">Announcements</a></li>
          <li class="nav-item"><a class="nav-link active" href="event.php">Events</a></li>
          <li class="nav-item"><a class="nav-link" href="index.php#categories">Categories</a></li>
        </ul>
        <div class="d-flex">
          <?php if (isset($_SESSION['user_id'])): ?>
            <span class="me-3 text-muted">👋 Hi, <strong>
              <?php echo htmlspecialchars($_SESSION['first_name']); ?>
            </strong></span>
            <a href="logout.php" class="btn btn-outline-danger">
              <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
          <?php else: ?>
            <a href="register.php" class="btn btn-outline-primary me-2">
              <i class="fas fa-user-plus me-1"></i>Register
            </a>
            <a href="login.php" class="btn btn-primary">
              <i class="fas fa-sign-in-alt me-1"></i>Login
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <!-- Events Section -->
  <div class="events-container">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="events-card p-5">
            <div class="text-center mb-5">
              <h2 class="fw-bold text-dark">Community Events</h2>
              <p class="text-muted">Gatherings, workshops, and celebrations</p>
              <p class="text-muted fw-semibold">23 upcoming events</p>
            </div>
            
            <!-- Filter Section -->
            <form method="GET" class="category-filter mb-4">
              <h5 class="fw-semibold mb-3">Filter Events</h5>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="category" class="form-label fw-semibold">Category</label>
                  <select class="form-select" id="category" name="category">
                    <option value="">All Categories</option>
                    <option value="Workshops">Workshops</option>
                    <option value="Community Gatherings">Community Gatherings</option>
                    <option value="Celebrations">Celebrations</option>
                    <option value="Conferences">Conferences</option>
                    <option value="Networking">Networking</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="date" class="form-label fw-semibold">Date</label>
                  <select class="form-select" id="date" name="date">
                    <option value="">All Dates</option>
                    <option value="This Week">This Week</option>
                    <option value="This Month">This Month</option>
                    <option value="Next Month">Next Month</option>
                  </select>
                </div>
              </div>
              <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-filter me-2"></i>Apply Filters
                </button>
              </div>
            </form>
            
            <!-- Events Listing -->
            <div class="events-listing">
              <?php
              // Example filter reading (static events for now)
              $filterCategory = $_GET['category'] ?? '';
              $filterDate = $_GET['date'] ?? '';

              if ($filterCategory || $filterDate) {
                echo "<p class='text-muted mb-4'><strong>Filters applied:</strong> ";
                if ($filterCategory) echo "Category = " . htmlspecialchars($filterCategory) . " ";
                if ($filterDate) echo "Date = " . htmlspecialchars($filterDate);
                echo "</p>";
              }
              ?>

              <!-- (Your same static event cards here, unchanged layout) -->
              <!-- Event 1 -->
              <div class="event-card p-4">
                <div class="row">
                  <div class="col-md-3">
                    <div class="bg-primary text-white text-center p-3 rounded">
                      <h4 class="fw-bold mb-0">15</h4>
                      <p class="mb-0">MAR</p>
                      <small>2025</small>
                    </div>
                    <div class="mt-2 text-center">
                      <small class="text-muted"><i class="fas fa-clock me-1"></i>2:00 PM - 5:00 PM</small>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <h5 class="fw-bold">Community Gardening Workshop</h5>
                    <p class="text-muted">Learn sustainable gardening practices and connect with fellow green thumbs in our community.</p>
                    <div class="d-flex align-items-center">
                      <span class="badge bg-primary me-2">Workshop</span>
                      <span class="badge bg-secondary">Outdoor</span>
                    </div>
                  </div>
                  <div class="col-md-3 text-md-end d-flex flex-column justify-content-between">
                    <div>
                      <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Central Park</p>
                      <p class="mb-3"><i class="fas fa-users me-2 text-primary"></i>24 attending</p>
                    </div>
                    <button class="btn btn-primary btn-sm">
                      <i class="fas fa-calendar-plus me-1"></i>RSVP
                    </button>
                  </div>
                </div>
              </div>

              <!-- (Event 2 and Event 3 same as before, keep layout) -->
            </div>

            <!-- Pagination -->
            <nav aria-label="Event pagination" class="mt-5">
              <ul class="pagination justify-content-center">
                <li class="page-item disabled"><a class="page-link">Previous</a></li>
                <li class="page-item active"><a class="page-link">1</a></li>
                <li class="page-item"><a class="page-link">2</a></li>
                <li class="page-item"><a class="page-link">3</a></li>
                <li class="page-item"><a class="page-link">Next</a></li>
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-light py-4">
    <div class="container text-center">&copy; 2025 AgoraBoard</div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
