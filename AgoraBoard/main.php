<?php
// main.php - User home after login
session_start();
require_once("db_connect.php"); // Connect to DB
include "navbar.php";

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userName = $_SESSION['name'];
$userRole = $_SESSION['role'];

// Fetch upcoming events (limit 5)
$events = $conn->query("SELECT * FROM events ORDER BY event_date ASC LIMIT 5");

// Fetch latest announcements (from community_posts, limit 5)
$announcements = $conn->query("SELECT cp.*, u.first_name, u.last_name 
    FROM community_posts cp
    LEFT JOIN users u ON cp.created_by = u.id
    ORDER BY cp.created_at DESC LIMIT 5");

// Fetch categories (for now we can just list unique post types)
$categories = ['Events', 'Announcements', 'Jobs', 'Lost & Found', 'Volunteering', 'Polls', 'Safety Reports'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgoraBoard - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #ecfdf5, #e0f2fe);
        }
        .gradient-text {
            background: linear-gradient(135deg, #059669, #047857);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            transition: 0.3s;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <!-- Welcome -->
    <div class="mb-5 text-center">
        <h2 class="gradient-text fw-bold">Welcome, <?= htmlspecialchars($userName) ?>!</h2>
        <p class="text-muted">Explore community updates, announcements, and events below.</p>
    </div>

    <!-- Announcements -->
    <section id="announcements" class="mb-5">
        <h3 class="fw-bold gradient-text mb-3">Latest Announcements</h3>
        <div class="row g-3">
            <?php if ($announcements->num_rows > 0): ?>
                <?php while ($row = $announcements->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-hover shadow-sm">
                            <div class="card-body">
                                <p class="card-text"><?= htmlspecialchars($row['content']) ?></p>
                                <small class="text-muted">By <?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?> on <?= date("M d, Y", strtotime($row['created_at'])) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No announcements yet.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Upcoming Events -->
    <section id="events" class="mb-5">
        <h3 class="fw-bold gradient-text mb-3">Upcoming Events</h3>
        <div class="row g-3">
            <?php if ($events->num_rows > 0): ?>
                <?php while ($row = $events->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-hover shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($row['description']) ?></p>
                                <small class="text-muted">Date: <?= date("M d, Y", strtotime($row['event_date'])) ?></small>
                                <?php if ($row['location']): ?>
                                    <br><small class="text-muted">Location: <?= htmlspecialchars($row['location']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">No upcoming events.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Categories -->
    <section id="categories" class="mb-5">
        <h3 class="fw-bold gradient-text mb-3">Explore Categories</h3>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($categories as $category): ?>
                <a href="category.php?type=<?= urlencode(strtolower($category)) ?>" class="btn btn-outline-success"><?= $category ?></a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
