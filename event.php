<?php
session_start();
require_once 'db_connect.php';

// 🛡️ Auth check
if (!isset($_SESSION['currentUser'])) {
  header("Location: login.php");
  exit;
}
$currentUser = $_SESSION['currentUser'];
$userId = $currentUser['id'] ?? null;

function sane($s)
{
  return htmlspecialchars(trim($s ?? ''), ENT_QUOTES, 'UTF-8');
}

// 📅 Determine current month and year
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
$firstDayOfMonth = strtotime("$year-$month-01");
$totalDays = date('t', $firstDayOfMonth);
$monthName = date('F', $firstDayOfMonth);
$startWeekday = date('w', $firstDayOfMonth); // 0 (Sun) - 6 (Sat)

// 🎉 Fetch events for this month
$stmt = $pdo->prepare("
    SELECT id, title, description, event_date, location
    FROM events
    WHERE MONTH(event_date) = ? AND YEAR(event_date) = ?
    ORDER BY event_date ASC
");
$stmt->execute([$month, $year]);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

$eventDays = [];
foreach ($events as $event) {
  $day = (int)date('j', strtotime($event['event_date']));
  $eventDays[$day][] = $event;
}

// 🟢 Handle Create Event form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $description = trim($_POST['description']);
  $event_date = $_POST['event_date'];
  $location = trim($_POST['location']);

  $pdo->beginTransaction();
  try {
    // Insert event
    $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $event_date, $location, $userId]);
    $eventId = $pdo->lastInsertId();

    // Auto add creator as attendee
    $attendeeStmt = $pdo->prepare("INSERT INTO event_attendees (event_id, user_id, status) VALUES (?, ?, 'confirmed')");
    $attendeeStmt->execute([$eventId, $userId]);

    $pdo->commit();
    header("Location: event.php?success=1");
    exit;
  } catch (Exception $e) {
    $pdo->rollBack();
    die("Error creating event: " . $e->getMessage());
  }
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AgoraBoard - Events</title>
  <link rel="stylesheet" href="assets/dashboard.css?v=<?= time(); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .calendar {
      width: 100%;
      background: #fff;
      border: 1px solid var(--border-color);
      border-radius: 12px;
      overflow: hidden;
    }

    .calendar th {
      text-align: center;
      background: var(--sage-light);
      color: white;
      padding: 10px;
    }

    .calendar td {
      height: 100px;
      vertical-align: top;
      padding: 6px;
      border: 1px solid #eee;
      position: relative;
      cursor: pointer;
    }

    .calendar td.empty {
      background-color: #f9f9f9;
    }

    .calendar .day-number {
      font-weight: bold;
      font-size: 0.9rem;
    }

    .calendar .event-item {
      font-size: 0.85rem;
      color: var(--sage-dark);
      margin-top: 4px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .calendar .event-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background-color: var(--sage);
    }

    .event-modal .modal-content {
      border-radius: 16px;
    }
  </style>
</head>

<body>
  <!-- 🧭 Sidebar -->
  <div class="sidebar">
    <div class="sidebar-content">
      <h4 class="mb-4"><i class="bi bi-people-fill me-2"></i> AgoraBoard</h4>
      <nav class="nav flex-column">
        <a href="dashboard.php" class="nav-link"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="public-safety.php" class="nav-link"><i class="bi bi-shield-exclamation"></i> Public Safety</a>
        <a href="lost-and-found.php" class="nav-link"><i class="bi bi-search"></i> Lost & Found</a>
        <a href="event.php" class="nav-link active"><i class="bi bi-calendar-event"></i> Events</a>
        <a href="jobs.php" class="nav-link"><i class="bi bi-briefcase"></i> Jobs</a>
        <a href="polls_view.php" class="nav-link"><i class="bi bi-bar-chart-line"></i> Polls</a>
        <a href="volunteering.php" class="nav-link"><i class="bi bi-heart"></i> Volunteering</a>
        <hr class="my-3 border-white opacity-25">
        <a href="bookmarks_view.php" class="nav-link"><i class="bi bi-bookmark"></i> Bookmarks</a>
        <a href="#" class="nav-link"><i class="bi bi-gear"></i> Settings</a>
      </nav>
    </div>

    <div class="sidebar-footer">
      <form action="logout.php" method="POST" id="logoutForm">
        <input type="hidden" name="logout" value="1">
        <button type="button" class="nav-link logout-btn w-100 text-start" onclick="confirmLogout()">
          <i class="bi bi-box-arrow-right"></i> Logout
        </button>
      </form>
    </div>
  </div>

  <!-- 🗓️ Main Content -->
  <div class="main-content">
    <div class="main-header mb-4">
      <h3 class="fw-bold"><i class="bi bi-calendar-event me-2"></i> Events Calendar</h3>
    </div>

    <!-- 🔄 Month navigation + Create Event -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="d-flex align-items-center gap-2">
        <a href="?month=<?= ($month == 1 ? 12 : $month - 1); ?>&year=<?= ($month == 1 ? $year - 1 : $year); ?>" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-chevron-left"></i> Prev
        </a>
        <h5 class="fw-bold mb-0"><?= "$monthName $year"; ?></h5>
        <a href="?month=<?= ($month == 12 ? 1 : $month + 1); ?>&year=<?= ($month == 12 ? $year + 1 : $year); ?>" class="btn btn-sm btn-outline-secondary">
          Next <i class="bi bi-chevron-right"></i>
        </a>
      </div>
      <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createEventModal">
        <i class="bi bi-plus-circle"></i> Create Event
      </button>
    </div>

    <!-- 📅 Calendar -->
    <table class="calendar table table-bordered text-center align-middle">
      <thead>
        <tr>
          <th>Sun</th>
          <th>Mon</th>
          <th>Tue</th>
          <th>Wed</th>
          <th>Thu</th>
          <th>Fri</th>
          <th>Sat</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $day = 1;
        $rows = ceil(($totalDays + $startWeekday) / 7);
        for ($i = 0; $i < $rows; $i++) {
          echo "<tr>";
          for ($j = 0; $j < 7; $j++) {
            $cellIndex = $i * 7 + $j;
            if ($cellIndex < $startWeekday || $day > $totalDays) {
              echo '<td class="empty"></td>';
            } else {
              $hasEvents = isset($eventDays[$day]);
              echo '<td class="' . ($hasEvents ? 'has-event' : '') . '" data-day="' . $day . '">';
              echo '<div class="day-number">' . $day . '</div>';
              if ($hasEvents) {
                foreach ($eventDays[$day] as $ev) {
                  echo '<div class="event-item"
                                            data-id="' . $ev['id'] . '"
                                            data-bs-toggle="modal"
                                            data-bs-target="#eventModal"
                                            data-title="' . sane($ev['title']) . '"
                                            data-description="' . sane($ev['description']) . '"
                                            data-date="' . sane($ev['event_date']) . '"
                                            data-location="' . sane($ev['location']) . '">
                                            <span class="event-dot"></span>' . sane($ev['title']) . '
                                          </div>';
                }
              }
              echo '</td>';
              $day++;
            }
          }
          echo "</tr>";
        }
        ?>
      </tbody>
    </table>
  </div>

  <!-- 🗓️ Create Event Modal -->
  <div class="modal fade" id="createEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content p-3 border-0 shadow-sm">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold"><i class="bi bi-calendar-plus me-2"></i>Create New Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form method="POST" action="event.php" class="mt-3">
          <div class="modal-body">
            <div class="mb-3">
              <label for="title" class="form-label fw-semibold">Event Title</label>
              <input type="text" name="title" id="title" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label fw-semibold">Description</label>
              <textarea name="description" id="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
              <label for="event_date" class="form-label fw-semibold">Date</label>
              <input type="date" name="event_date" id="event_date" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="location" class="form-label fw-semibold">Location</label>
              <input type="text" name="location" id="location" class="form-control">
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Create Event</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- 🪩 Event Details Modal -->
  <div class="modal fade event-modal" id="eventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content p-3">
        <div class="modal-header border-0">
          <h5 class="modal-title fw-bold" id="eventTitle"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p><i class="bi bi-calendar-event me-2"></i><strong>Date:</strong> <span id="eventDate"></span></p>
          <p><i class="bi bi-geo-alt me-2"></i><strong>Location:</strong> <span id="eventLocation"></span></p>
          <p><i class="bi bi-info-circle me-2"></i><strong>Details:</strong></p>
          <p id="eventDescription" class="text-muted mb-3"></p>

          <div class="attendees-section border-top pt-3">
            <h6 class="fw-bold mb-2"><i class="bi bi-people-fill me-2"></i>Attendees</h6>
            <div id="attendeesList" class="d-flex flex-wrap gap-2 small text-secondary">
              <em>Loading...</em>
            </div>

            <form method="POST" action="event_attend_action.php" class="mt-3" id="attendForm">
              <input type="hidden" name="event_id" id="attendEventId">
              <input type="hidden" name="action" id="attendAction">
              <button type="submit" class="btn btn-outline-success w-100" id="joinBtn">
                <i class="bi bi-person-plus"></i> Join Event
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ⚙️ Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let modalInstance;
    document.addEventListener('DOMContentLoaded', () => {
      console.log('📦 DOM fully loaded');
      const modalElement = document.getElementById('eventModal');
      modalInstance = new bootstrap.Modal(modalElement);

      // Handle event item clicks
      document.querySelectorAll('.event-item').forEach(item => {
        item.addEventListener('click', async () => {
          const eventId = item.dataset.id;
          const title = item.dataset.title;
          const date = item.dataset.date;
          const desc = item.dataset.description;
          const loc = item.dataset.location;

          console.log(`📅 Event clicked: ${title} (ID: ${eventId})`);

          document.getElementById('eventTitle').textContent = title;
          document.getElementById('eventDate').textContent = new Date(date).toLocaleDateString();
          document.getElementById('eventLocation').textContent = loc || 'N/A';
          document.getElementById('eventDescription').textContent = desc || 'No description.';
          document.getElementById('attendEventId').value = eventId;

          try {
            console.log('🔄 Fetching attendees...');
            const res = await fetch(`get_attendees.php?event_id=${eventId}`);
            const data = await res.json();
            console.log('✅ Attendees fetched:', data);

            const attendees = data.attendees || [];
            const attendeesDiv = document.getElementById('attendeesList');

            if (attendees.length > 0) {
              attendeesDiv.innerHTML = attendees.map(a => `
            <span class="badge bg-light text-dark border">${a.first_name} ${a.last_name}</span>
          `).join('');
            } else {
              attendeesDiv.innerHTML = '<em>No attendees yet.</em>';
            }

            const joinBtn = document.getElementById('joinBtn');
            const attendAction = document.getElementById('attendAction');
            const isAttending = data.attending ?? attendees.some(a => a.is_current_user);

            console.log(`👤 Current user is ${isAttending ? '' : 'not '}attending`);

            if (isAttending) {
              joinBtn.classList.replace('btn-outline-success', 'btn-outline-danger');
              joinBtn.innerHTML = '<i class="bi bi-person-dash"></i> Leave Event';
              attendAction.value = 'leave';
            } else {
              joinBtn.classList.replace('btn-outline-danger', 'btn-outline-success');
              joinBtn.innerHTML = '<i class="bi bi-person-plus"></i> Join Event';
              attendAction.value = 'join';
            }

            console.log('📣 Showing modal...');
            modalInstance.show(); // ✅ reuse the same instance
          } catch (err) {
            console.error(`❌ Failed to load event ${eventId}:`, err);
          }
        });
      });

      // Handle form submission via AJAX
      const attendForm = document.getElementById('attendForm');
      attendForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const eventId = document.getElementById('attendEventId').value;
        const action = document.getElementById('attendAction').value;
        const joinBtn = document.getElementById('joinBtn');

        console.log(`📤 Submitting attendance: ${action} for event ${eventId}`);

        try {
          const res = await fetch('event_attend_action.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: `event_id=${eventId}&action=${action}`
          });

          const result = await res.json();
          console.log('✅ Attendance response:', result);

          if (result.success) {
            console.log('🔄 Re-fetching attendees...');
            const attendeeRes = await fetch(`get_attendees.php?event_id=${eventId}`);
            const data = await attendeeRes.json();
            console.log('✅ Updated attendees:', data);

            const attendees = data.attendees || [];
            const attendeesDiv = document.getElementById('attendeesList');

            if (attendees.length > 0) {
              attendeesDiv.innerHTML = attendees.map(a => `
            <span class="badge bg-light text-dark border">${a.first_name} ${a.last_name}</span>
          `).join('');
            } else {
              attendeesDiv.innerHTML = '<em>No attendees yet.</em>';
            }

            const isAttending = data.attending ?? attendees.some(a => a.is_current_user);
            document.getElementById('attendAction').value = isAttending ? 'leave' : 'join';

            if (isAttending) {
              joinBtn.classList.replace('btn-outline-success', 'btn-outline-danger');
              joinBtn.innerHTML = '<i class="bi bi-person-dash"></i> Leave Event';
            } else {
              joinBtn.classList.replace('btn-outline-danger', 'btn-outline-success');
              joinBtn.innerHTML = '<i class="bi bi-person-plus"></i> Join Event';
            }

            console.log(`🎯 Button updated: ${isAttending ? 'Leave' : 'Join'} mode`);
          } else {
            alert(result.error || 'Failed to update attendance.');
            console.warn('⚠️ Attendance update failed:', result);
          }
        } catch (err) {
          console.error(`❌ Failed to submit attendance for event ${eventId}:`, err);
        }
      });
    });

    // Logout confirmation
    function confirmLogout() {
      if (confirm("Are you sure you want to log out?")) {
        document.getElementById('logoutForm').submit();
      }
    }
  </script>
</body>

</html>