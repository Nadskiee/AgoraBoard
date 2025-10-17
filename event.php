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
<<<<<<< HEAD
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
=======
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --emerald-50: #ecfdf5;
      --emerald-100: #d1fae5;
      --emerald-500: #10b981;
      --emerald-600: #059669;
      --emerald-700: #047857;
      --secondary-text: #6b7280;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8f9fa;
    }
    
    .hero-section {
        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?q=80&w=2070&auto=format&fit=crop') no-repeat center center;
        background-size: cover;
        padding: 6rem 0;
        border-radius: 1.5rem;
        margin-bottom: 2.5rem;
    }
    
    .hero-section h1 {
        font-size: 3.5rem;
    }

    .header-controls {
        background-color: rgba(255,255,255,0.9);
        backdrop-filter: blur(10px);
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        margin-top: 2rem;
    }
    
    .btn-action {
      background-color: var(--emerald-500);
      border: none;
      font-weight: 600;
      border-radius: 8px;
      padding: 0.6rem 1.5rem;
      color: white;
      transition: background-color 0.2s ease;
    }
    .btn-action:hover {
      background-color: var(--emerald-600);
      color: white;
    }

    .btn-back {
        color: var(--emerald-700);
        font-weight: 600;
        transition: color 0.2s;
        background-color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .btn-back:hover {
        color: var(--emerald-600);
        background-color: #f8f9fa;
    }
    
    .event-card {
      background: white;
      border: 1px solid #e5e7eb;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      transition: box-shadow 0.3s ease, transform 0.3s ease;
      overflow: hidden;
      position: relative;
    }
    .event-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(16, 185, 129, 0.1);
    }
    .event-card img {
        height: 160px;
        object-fit: cover;
    }
    .event-card .card-body {
        padding: 1rem;
    }
    .price-tag {
        position: absolute;
        top: 10px;
        left: 10px;
        background-color: rgba(0,0,0,0.6);
        color: white;
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 20px;
    }

    .btn-attend {
        background-color: var(--emerald-100);
        color: var(--emerald-700);
        font-weight: 600;
    }
    .btn-attend:hover {
        background-color: #a7f3d0;
        color: var(--emerald-700);
    }

  </style>
</head>
<body>
  <div class="container py-4">
     <div class="mb-4">
        <a href="dashboard.html" class="btn btn-link btn-back text-decoration-none p-0">
            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
        </a>
    </div>

    <div class="hero-section text-white text-center">
        <h1 class="fw-bold display-4">Find Your Next Event</h1>
        <p class="lead">Discover workshops, gatherings, and celebrations near you.</p>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="header-controls">
                    <div class="row g-2">
                        <div class="col-lg-8">
                            <input type="search" class="form-control" placeholder="Search events by name or location...">
                        </div>
                        <div class="col-lg-4">
                            <select class="form-select">
                                <option selected>All Categories</option>
                                <option value="1">Music</option>
                                <option value="2">Workshops</option>
                                <option value="3">Community</option>
                                <option value="4">Food & Drink</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <h3 class="fw-bold mb-4">Upcoming Events</h3>
    <!-- Grid of Events -->
    <div class="row">
      <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card event-card h-100 d-flex flex-column">
          <img src="https://images.unsplash.com/photo-1524368535928-5b5e00ddc76b?q=80&w=2070&auto=format&fit=crop" class="card-img-top" alt="Music Festival">
           <div class="price-tag">FREE</div>
          <div class="card-body flex-grow-1">
            <h6 class="card-title fw-bold mb-2">City Music Festival</h6>
            <p class="card-text text-secondary small mb-1"><i class="fas fa-calendar-alt me-2 text-muted"></i>Nov 5, 2025</p>
            <p class="card-text text-secondary small mb-2"><i class="fas fa-map-marker-alt me-2 text-muted"></i>Downtown Plaza</p>
             <p class="card-text text-secondary small"><i class="fas fa-users me-2 text-muted"></i>120 Attendees</p>
          </div>
           <div class="card-footer bg-white border-0 p-3">
             <button class="btn btn-attend w-100 btn-sm">I'm Going!</button>
           </div>
        </div>
      </div>

       <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card event-card h-100 d-flex flex-column">
          <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?q=80&w=1932&auto=format&fit=crop" class="card-img-top" alt="Tech Workshop">
           <div class="price-tag">$25.00</div>
          <div class="card-body flex-grow-1">
            <h6 class="card-title fw-bold mb-2">UI/UX Design Workshop</h6>
            <p class="card-text text-secondary small mb-1"><i class="fas fa-calendar-alt me-2 text-muted"></i>Nov 12, 2025</p>
            <p class="card-text text-secondary small mb-2"><i class="fas fa-map-marker-alt me-2 text-muted"></i>Innovation Hub</p>
            <p class="card-text text-secondary small"><i class="fas fa-users me-2 text-muted"></i>45 Attendees</p>
          </div>
           <div class="card-footer bg-white border-0 p-3">
             <button class="btn btn-attend w-100 btn-sm">I'm Going!</button>
           </div>
        </div>
      </div>

       <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card event-card h-100 d-flex flex-column">
          <img src="https://images.unsplash.com/photo-1529224424268-912b317b9b39?q=80&w=2070&auto=format&fit=crop" class="card-img-top" alt="Food Market">
          <div class="price-tag">FREE</div>
          <div class="card-body flex-grow-1">
            <h6 class="card-title fw-bold mb-2">Weekend Farmers Market</h6>
            <p class="card-text text-secondary small mb-1"><i class="fas fa-calendar-alt me-2 text-muted"></i>Nov 15, 2025</p>
            <p class="card-text text-secondary small mb-2"><i class="fas fa-map-marker-alt me-2 text-muted"></i>Community Park</p>
            <p class="card-text text-secondary small"><i class="fas fa-users me-2 text-muted"></i>250+ Attendees</p>
          </div>
           <div class="card-footer bg-white border-0 p-3">
             <button class="btn btn-attend w-100 btn-sm">I'm Going!</button>
           </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card event-card h-100 d-flex flex-column">
          <img src="https://images.unsplash.com/photo-1511578314322-379afb476865?q=80&w=2070&auto=format&fit=crop" class="card-img-top" alt="Conference">
          <div class="price-tag">$75.00</div>
          <div class="card-body flex-grow-1">
            <h6 class="card-title fw-bold mb-2">Future of Tech Conference</h6>
            <p class="card-text text-secondary small mb-1"><i class="fas fa-calendar-alt me-2 text-muted"></i>Nov 20, 2025</p>
            <p class="card-text text-secondary small mb-2"><i class="fas fa-map-marker-alt me-2 text-muted"></i>Grand Convention Center</p>
            <p class="card-text text-secondary small"><i class="fas fa-users me-2 text-muted"></i>500+ Attendees</p>
          </div>
           <div class="card-footer bg-white border-0 p-3">
             <button class="btn btn-attend w-100 btn-sm">I'm Going!</button>
           </div>
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
        </div>
      </div>
    </div>
  </div>

<<<<<<< HEAD
  <!-- Footer -->
  <footer class="bg-dark text-light py-4">
    <div class="container text-center">&copy; 2025 AgoraBoard</div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
=======
  <footer class="bg-dark text-light py-4 mt-4"><div class="container text-center">&copy; 2025 AgoraBoard</div></footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
