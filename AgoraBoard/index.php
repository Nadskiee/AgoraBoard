<?php
include 'db_connect.php'; // make sure this file exists in the same folder
include 'navbar.php';    // include your navbar for consistent navigation

// Optional: Fetch sample data (e.g., latest events or announcements)
$recentEvents = $conn->query("SELECT title, description, event_date FROM events ORDER BY created_at DESC LIMIT 3");
$recentJobs = $conn->query("SELECT title, description FROM jobs ORDER BY created_at DESC LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgoraBoard - Community Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* 🎨 Keep your full existing CSS styles exactly as before */
        :root {
            --emerald-50: #ecfdf5;
            --emerald-100: #d1fae5;
            --emerald-500: #10b981;
            --emerald-600: #059669;
            --emerald-700: #047857;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
        }
        .gradient-text {
            background: linear-gradient(135deg, var(--emerald-600), var(--emerald-700));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .gradient-text-hero {
            background: linear-gradient(135deg, var(--emerald-600), var(--emerald-500), var(--blue-600));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .btn-emerald { background: linear-gradient(135deg, var(--emerald-600), var(--emerald-700)); border: none; color: white; box-shadow: 0 10px 25px rgba(16, 185, 129, 0.25); }
        .btn-emerald:hover { background: linear-gradient(135deg, var(--emerald-700), var(--emerald-600)); color: white; transform: translateY(-2px); }
        .feature-card { transition: all 0.3s ease; border: none; background: linear-gradient(135deg, #ffffff, rgba(236, 253, 245, 0.3)); }
        .feature-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(16, 185, 129, 0.1); }
        .feature-icon { width: 64px; height: 64px; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; margin-bottom: 1rem; transition: transform 0.3s ease; }
        .feature-card:hover .feature-icon { transform: scale(1.1); }
        .bg-emerald { background: linear-gradient(135deg, var(--emerald-500), var(--emerald-600)); }
        .bg-blue { background: linear-gradient(135deg, var(--blue-500), var(--blue-600)); }
        .bg-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .bg-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
        .bg-teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }
        .bg-rose { background: linear-gradient(135deg, #f43f5e, #e11d48); }
        .hero-bg { background: linear-gradient(135deg, rgba(236, 253, 245, 0.5), transparent, rgba(219, 234, 254, 0.3)); position: relative; overflow: hidden; }
        .hero-bg::before { content: ''; position: absolute; top: 80px; left: 40px; width: 288px; height: 288px; background: rgba(167, 243, 208, 0.2); border-radius: 50%; filter: blur(60px); }
        .hero-bg::after { content: ''; position: absolute; bottom: 80px; right: 40px; width: 384px; height: 384px; background: rgba(147, 197, 253, 0.2); border-radius: 50%; filter: blur(60px); }
        .backdrop-blur { backdrop-filter: blur(12px); background-color: rgba(255, 255, 255, 0.8); }
        .badge-emerald { background-color: var(--emerald-100); color: var(--emerald-700); border: 1px solid rgba(167, 243, 208, 0.5); }
    </style>
</head>
<body>
    

    <!-- Hero Section -->
    <section class="hero-bg py-5">
        <div class="container text-center position-relative" style="z-index: 10;">
            <h2 class="display-1 fw-bold mb-4 lh-1">
                Welcome to Your 
                <span class="gradient-text-hero">Community's</span> 
                Digital Bulletin Board
            </h2>
            <p class="lead fs-4 text-muted mb-5 mx-auto" style="max-width: 48rem;">
                Stay connected with your neighbors, discover local events, and share important announcements.
            </p>
        </div>
    </section>

    <!-- 🌟 Dynamic Section: Recent Events -->
    <section class="py-5 bg-light">
        <div class="container">
            <h3 class="fw-bold mb-4 gradient-text">Latest Community Events</h3>
            <div class="row">
                <?php while($event = $recentEvents->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($event['title']); ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars(substr($event['description'], 0, 100)); ?>...</p>
                                <small class="text-secondary"><i class="fa fa-calendar"></i> <?= $event['event_date']; ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

   <!-- Footer -->
<footer class="border-top py-5" style="background: linear-gradient(to bottom, rgba(249,250,251,0.3), rgba(249,250,251,0.5));">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="fw-bold mb-2">AgoraBoard</h5>
                <p class="text-muted small">Connecting communities through digital communication.</p>
            </div>
            <div class="col-md-4">
                <h6 class="fw-semibold mb-2">Community</h6>
                <ul class="list-unstyled small">
                    <li><a href="#" class="text-muted text-decoration-none">Guidelines</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="fw-semibold mb-2">Support</h6>
                <ul class="list-unstyled small">
                    <li><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Contact Us</a></li>
                    <li><a href="#" class="text-muted text-decoration-none">Feedback</a></li>
                </ul>
            </div>
        </div>
        <hr class="my-3">
        <div class="text-center">
            <p class="text-muted small mb-1">&copy; 2025 AgoraBoard. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>
