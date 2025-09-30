<?php 
session_start();

// Initialize posts (simulate feed)
if (!isset($_SESSION['posts'])) {
    $_SESSION['posts'] = [
        ["user" => "Maria", "role" => "Community Member", "time" => "2 hours ago", "content" => "Nawala akong selpon sa plaza. Kung kinsa makakita, palihug ko ug uli 🙏", "tag" => "Lost & Found", "likes" => 2, "comments" => 1, "shares" => 0],
        ["user" => "Admin", "role" => "Admin", "time" => "5 hours ago", "content" => "Welcome to AgoraBoard! 🎉", "tag" => "General", "likes" => 5, "comments" => 3, "shares" => 1],
    ];
}

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
    $tag = $_POST['tag'] ?? "General";
    $newPost = [
        "user" => "You",
        "role" => "Community Member",
        "time" => "Just now",
        "content" => htmlspecialchars($_POST['content']),
        "tag" => $tag,
        "likes" => 0,
        "comments" => 0,
        "shares" => 0
    ];
    array_unshift($_SESSION['posts'], $newPost); // Add on top
}

// Hardcoded events (clickable to add post)
$events = [
    ["title" => "Community BBQ & Family Fun Day", "category" => "Social", "date" => "Saturday, Dec 23", "time" => "12:00 - 6:00 PM", "location" => "Central Park Pavilion", "attendees" => 47],
    ["title" => "Holiday Light Tour", "category" => "Holiday", "date" => "Sunday, Dec 24", "time" => "7:00 - 9:00 PM", "location" => "Neighborhood Streets", "attendees" => 23],
    ["title" => "New Year's Eve Celebration", "category" => "Celebration", "date" => "Sunday, Dec 31", "time" => "8:00 PM - 12:30 AM", "location" => "Community Center", "attendees" => 120],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>AgoraBoard - Community Bulletin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
    body {
      background-color: #FAF9F6; /* very light warm beige */
      color: #3B3A36; /* dark slate gray */
      scroll-behavior: smooth;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .sidebar {
      height: 100vh;
      background: #7A9E7E; /* muted sage green */
      color: #E6E3D3; /* soft cream */
      padding: 20px 0;
      position: fixed;
      width: 220px;
      overflow-y: auto;
      transition: width 0.3s ease;
      font-weight: 600;
    }

    .sidebar h4 {
      text-align: center;
      margin-bottom: 20px;
      font-weight: 700;
      letter-spacing: 1.5px;
      color: #E6E3D3;
    }

    .sidebar a {
      color: #E6E3D3;
      text-decoration: none;
      padding: 12px 20px;
      display: block;
      font-weight: 600;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .sidebar a:hover,
    .sidebar a.active {
      background: #5F7A66; /* darker sage */
      color: #FAF9F6; /* very light beige */
      border-radius: 5px;
    }

    .navbar-custom {
      background-color: #7A9E7E; /* muted sage green */
      color: #E6E3D3;
      position: fixed;
      top: 0;
      left: 220px;
      right: 0;
      height: 56px;
      display: flex;
      align-items: center;
      padding: 0 20px;
      z-index: 1030;
      font-weight: 700;
      user-select: none;
      box-shadow: 0 2px 6px rgb(122 158 126 / 0.5);
    }

    .navbar-custom h5 {
      margin: 0;
      color: #E6E3D3;
    }

    .main-content {
      margin-left: 220px;
      margin-top: 56px;
      padding: 20px;
      min-height: 100vh;
      display: flex;
      gap: 1rem;
      color: #3B3A36;
    }

    .right-panel {
      width: 300px;
      position: sticky;
      top: 80px;
      height: fit-content;
    }

    .post-box {
      background: #FFF9F4; /* very light cream */
      border: 1px solid #7A9E7E; /* sage border */
      border-radius: 10px;
      padding: 15px 15px 50px 15px; /* extra bottom padding for button */
      margin-bottom: 20px;
      box-shadow: 0 2px 6px rgb(122 158 126 / 0.15);
      transition: box-shadow 0.3s ease;
      position: relative;
    }

    .post-box:focus-within {
      box-shadow: 0 4px 12px rgb(122 158 126 / 0.3);
    }

    /* Post button bottom right */
    .post-box button[type="submit"] {
      position: absolute;
      bottom: 15px;
      right: 15px;
      min-width: 90px;
      font-weight: 600;
      border-radius: 25px;
      padding: 6px 18px;
      background-color: #7A9E7E;
      border: 1px solid #5F7A66;
      color: #E6E3D3;
      box-shadow: 0 3px 6px rgb(122 158 126 / 0.4);
      transition: background-color 0.3s ease, box-shadow 0.3s ease, color 0.3s ease;
    }
    .post-box button[type="submit"]:hover,
    .post-box button[type="submit"]:focus {
      background-color: #5F7A66;
      border-color: #4A5B4A;
      color: #FAF9F6;
      box-shadow: 0 5px 12px rgb(95 122 102 / 0.6);
    }

    .post {
      background: #FFF9F4; /* very light cream */
      border: 1px solid #D9C9B6; /* soft brown */
      border-radius: 10px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 1px 4px rgb(59 58 54 / 0.1);
      transition: box-shadow 0.3s ease;
    }

    .post:hover {
      box-shadow: 0 4px 12px rgb(59 58 54 / 0.2);
    }

    .badge {
      font-size: 12px;
      user-select: none;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    /* Badge colors for tags */
    .badge.bg-secondary { background-color: #A3B18A; color: #3B3A36; } /* pastel sage */
    .badge.bg-success { background-color: #B5CDA3; color: #3B3A36; } /* soft green */
    .badge.bg-danger { background-color: #A36B5B; color: #FAF9F6; } /* muted brownish red */
    .badge.bg-warning { background-color: #D2C17B; color: #3B3A36; } /* warm muted yellow */
    .badge.bg-info { background-color: #A3C4BC; color: #3B3A36; } /* soft teal */

    /* Buttons */
    .btn-primary {
      background-color: #7A9E7E;
      border-color: #5F7A66;
      font-weight: 600;
      color: #E6E3D3;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .btn-primary:hover, .btn-primary:focus {
      background-color: #5F7A66;
      border-color: #4A5B4A;
      color: #FAF9F6;
    }

    /* Event cards */
    .event-card {
      border: 1px solid #D9C9B6;
      border-radius: 10px;
      padding: 12px;
      margin-bottom: 12px;
      background: #FFF9F4;
      transition: background-color 0.2s ease, transform 0.2s ease;
      text-align: left;
      width: 100%;
      color: #3B3A36;
      box-shadow: 0 1px 3px rgb(59 58 54 / 0.1);
    }

    .event-card:hover {
      background: #FAF9F6;
      transform: scale(1.02);
      box-shadow: 0 4px 12px rgb(59 58 54 / 0.15);
    }

    /* Like button */
    .like-btn {
      cursor: pointer;
      color: #5F7A66; /* muted sage */
      transition: color 0.3s ease;
      user-select: none;
    }

    .like-btn.liked {
      color: #7A9E7E; /* sage */
    }

    /* Scrollbar for sidebar */
    .sidebar::-webkit-scrollbar {
      width: 6px;
    }

    .sidebar::-webkit-scrollbar-thumb {
      background-color: #5F7A66;
      border-radius: 3px;
    }

    /* Textarea auto resize */
    textarea {
      resize: none;
      min-height: 80px;
      max-height: 180px;
      transition: height 0.2s ease;
      font-family: inherit;
      font-size: 1rem;
      color: #3B3A36;
      background-color: #FFF9F4;
      border: 1px solid #D9C9B6;
      border-radius: 6px;
      padding: 10px 14px;
      box-shadow: inset 0 1px 3px rgb(0 0 0 / 0.05);
      transition: border-color 0.3s ease;
    }
    textarea:focus {
      border-color: #7A9E7E;
      outline: none;
      box-shadow: 0 0 6px rgb(122 158 126 / 0.5);
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h4><i class="bi bi-people-fill"></i> AgoraBoard</h4>
    <a href="#" class="active"><i class="bi bi-house-door"></i> Home</a>
    <a href="#"><i class="bi bi-calendar-event"></i> Events</a>
    <a href="#"><i class="bi bi-bell"></i> Notifications</a>
    <a href="#"><i class="bi bi-heart"></i> Volunteers</a>
    <a href="#"><i class="bi bi-chat-dots"></i> Discussions</a>
    <a href="#"><i class="bi bi-search"></i> Lost & Found</a>
    <a href="#"><i class="bi bi-people"></i> Members</a>
    <a href="#"><i class="bi bi-bar-chart"></i> Analytics</a>
  </div>

  <!-- Navbar -->
  <div class="navbar-custom">
    <h5><i class="bi bi-house-door"></i> Home</h5>
  </div>

  <!-- Main -->
  <div class="main-content d-flex">
    <!-- Center Stream -->
    <div class="flex-grow-1 me-3">
      <h4 class="mb-3">Main Stream</h4>

      <!-- Post Box -->
      <form method="POST" class="post-box" id="postForm" autocomplete="off">
        <div class="d-flex mb-3">
          <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:40px; height:40px;">Y</div>
          <textarea name="content" class="form-control ms-2" placeholder="What's happening in your community?" required></textarea>
        </div>

        <!-- Hashtags (filled pills) -->
        <div class="mb-3">
          <input type="radio" class="btn-check" name="tag" id="general" value="General" checked>
          <label class="btn btn-outline-secondary btn-sm rounded-pill me-1" for="general">General</label>

          <input type="radio" class="btn-check" name="tag" id="event" value="Event">
          <label class="btn btn-outline-success btn-sm rounded-pill me-1" for="event">Event</label>

          <input type="radio" class="btn-check" name="tag" id="alert" value="Alert">
          <label class="btn btn-outline-danger btn-sm rounded-pill me-1" for="alert">Alert</label>

          <input type="radio" class="btn-check" name="tag" id="lost" value="Lost & Found">
          <label class="btn btn-outline-warning btn-sm rounded-pill me-1" for="lost">Lost & Found</label>

          <input type="radio" class="btn-check" name="tag" id="volunteer" value="Volunteer">
          <label class="btn btn-outline-info btn-sm rounded-pill me-1" for="volunteer">Volunteer</label>
        </div>

        <button type="submit" class="btn btn-primary">Post</button>
      </form>

      <!-- Posts -->
      <?php foreach ($_SESSION['posts'] as $index => $post): ?>
        <div class="post" data-post-index="<?= $index; ?>">
          <div class="d-flex align-items-center mb-2">
            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width:40px; height:40px;">
              <?= strtoupper(substr($post['user'], 0, 1)); ?>
            </div>
            <div class="ms-2">
              <strong><?= $post['user']; ?></strong> <small class="text-muted">• <?= $post['role']; ?> • <?= $post['time']; ?></small>
            </div>
            <div class="ms-auto">
              <?php 
                $tagColors = [
                  "General" => "secondary",
                  "Event" => "success",
                  "Alert" => "danger",
                  "Lost & Found" => "warning",
                  "Volunteer" => "info"
                ];
                $color = $tagColors[$post['tag']] ?? "secondary";
              ?>
              <span class="badge bg-<?= $color; ?>"><?= $post['tag']; ?></span>
            </div>
          </div>
          <p><?= $post['content']; ?></p>
          <div class="d-flex text-muted align-items-center gap-3">
            <small class="like-btn" title="Like" data-index="<?= $index; ?>">
              <i class="bi bi-heart<?= $post['likes'] > 0 ? '-fill liked' : ''; ?>"></i> <span class="like-count"><?= $post['likes']; ?></span>
            </small>
            <small><i class="bi bi-chat"></i> <?= $post['comments']; ?></small>
            <small><i class="bi bi-share"></i> <?= $post['shares']; ?></small>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Right Panel -->
    <div class="right-panel">
      <div class="card mb-3 shadow-sm">
        <div class="card-header bg-white fw-bold"><i class="bi bi-calendar-event"></i> Upcoming Community Events</div>
        <div class="card-body">
          <?php foreach ($events as $event): ?>
            <form method="POST" class="mb-3">
              <input type="hidden" name="content" value="📢 <?= $event['title']; ?> - <?= $event['date']; ?> at <?= $event['location']; ?>">
              <input type="hidden" name="tag" value="Event">
              <button type="submit" class="event-card w-100 text-start">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <strong><?= $event['title']; ?></strong>
                  <span class="badge bg-success"><?= $event['category']; ?></span>
                </div>
                <small class="text-muted d-block"><i class="bi bi-calendar"></i> <?= $event['date']; ?> • <?= $event['time']; ?></small>
                <small class="text-muted d-block"><i class="bi bi-geo-alt"></i> <?= $event['location']; ?></small>
                <small class="text-muted d-block"><i class="bi bi-people"></i> <?= $event['attendees']; ?> attending</small>
              </button>
            </form>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.