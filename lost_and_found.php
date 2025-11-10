<?php
session_start();
require_once 'db.php';

// 🔐 Authentication Check
if (!isset($_SESSION['currentUser'])) {
    header("Location: login.php");
    exit;
}
$currentUser = $_SESSION['currentUser'];
$userId = $currentUser['id'] ?? null;

// 🗂 Fetch Items
$items = [];
try {
    $stmt = $pdo->query("SELECT 
        id, 
        status, 
        item_name, 
        description, 
        posted_by AS reported_by, 
        date_lost_found AS date, 
        last_seen_location AS location, 
        category, 
        image_path AS image
    FROM lost_and_found 
    ORDER BY created_at DESC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger container mt-5'>Error fetching items: " . $e->getMessage() . "</div>";
    $items = []; 
}

$prefill_name = trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? ''));
$prefill_contact = $currentUser['contact'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lost & Found - AgoraBoard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --emerald-50: #ecfdf5;
      --emerald-500: #10b981;
      --emerald-600: #059669;
      --emerald-700: #047857;
      --blue-600: #2563eb;
      --blue-700: #1d4ed8;
      --danger-500: #ef4444;
      --gray-100: #f3f4f6;
      --gray-700: #374151;
    }
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--gray-100);
    }
    .back-btn-top {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: linear-gradient(90deg, #059669, #10b981);
      color: #fff;
      padding: 10px 20px;
      border-radius: 30px;
      text-decoration: none;
      font-weight: 600;
      box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
      transition: all 0.2s ease;
    }
    .back-btn-top:hover {
      background: linear-gradient(90deg, #047857, #059669);
      transform: translateY(-2px);
    }
    .header-hero {
      background: linear-gradient(to top, rgba(255,255,255,0.7), rgba(255,255,255,0.9));
      padding: 2rem 0 0;
      margin-bottom: 2rem;
    }
    .item-list-container {
      background-color: white;
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .nav-tabs .nav-link.active {
      color: white;
      background-color: var(--emerald-500);
    }
    .report-button {
      background-color: var(--emerald-500);
      border-color: var(--emerald-500);
      font-weight: 600;
      color: white;
    }
    .item-row {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      margin-bottom: 1rem;
      padding: 1.5rem;
      display: flex;
      align-items: center;
      transition: 0.2s;
    }
    .item-row:hover {
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .item-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      margin-right: 1.5rem;
    }
    .status-tag-list {
      padding: 0.25rem 0.75rem;
      font-size: 0.8rem;
      font-weight: 600;
      border-radius: 20px;
      color: white;
    }
    .status-tag-list.lost { background-color: var(--danger-500); }
    .status-tag-list.found { background-color: var(--emerald-500); }
  </style>
</head>
<body>

  <div class="container mt-3 mb-0">
    <a href="dashboard.php" class="back-btn-top"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
  </div>

  <div class="header-hero text-center">
    <div class="container py-5">
      <h1 class="fw-bold text-dark mb-2">Find What's Lost, Return What's Found</h1>
      <p class="text-secondary fs-6 mb-4">Helping our community reconnect with their belongings.</p>

      <div class="row justify-content-center mb-3">
        <div class="col-lg-8">
          <div class="input-group input-group-lg shadow-sm">
            <input type="search" class="form-control" placeholder="Search items or locations..." id="searchInput">
            <button class="btn btn-outline-secondary" type="button" id="searchButton"><i class="bi bi-search"></i></button>
          </div>
        </div>
      </div>

      <ul class="nav nav-tabs justify-content-center border-0 mb-3" id="itemTabs">
        <li class="nav-item"><button class="nav-link active" data-tab-filter="lost-item">Lost Items</button></li>
        <li class="nav-item"><button class="nav-link" data-tab-filter="lost-pet">Lost Pets</button></li>
        <li class="nav-item"><button class="nav-link" data-tab-filter="found">Found Items</button></li>
      </ul>
    </div>
  </div>

  <div class="container item-list-container">
    <div class="d-flex justify-content-end mb-4">
      <button class="btn report-button" data-bs-toggle="modal" data-bs-target="#reportItemModal">
        <i class="bi bi-plus-lg me-2"></i> Report New Item
      </button>
    </div>

    <div id="itemList">
      <?php if (empty($items)): ?>
        <div id="noItemsMessage" class="alert alert-info text-center">Wala pay na-report nga items. Be the first to report!</div>
      <?php else: ?>
        <?php foreach ($items as $item): 
          $filterCat = $item['status'];
          if ($filterCat === 'lost' && $item['category'] === 'Pet') $filterCat = 'lost-pet';
          else if ($filterCat === 'lost') $filterCat = 'lost-item';
          $searchData = strtolower($item['item_name'] . ' ' . $item['description'] . ' ' . $item['location']);
          $imagePath = $item['image'] ?? 'assets/placeholder.png';
          if (empty($item['image']) || !file_exists($item['image'])) $imagePath = 'assets/placeholder.png';
        ?>
        <div class="item-row" data-filter-tab="<?= $filterCat ?>" data-search="<?= htmlspecialchars($searchData) ?>">
          <img src="<?= htmlspecialchars($imagePath) ?>" class="item-image" alt="<?= htmlspecialchars($item['item_name']) ?>">
          <div class="item-details flex-grow-1">
            <h5 class="fw-bold mb-1"><?= htmlspecialchars($item['item_name']) ?></h5>
            <p class="text-muted small mb-0"><?= htmlspecialchars(substr($item['description'], 0, 90)) ?>...</p>
            <div class="small text-secondary mt-2">
              <i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars($item['date']) ?> &nbsp;
              <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($item['location']) ?>
            </div>
          </div>
          <div class="ms-auto text-end">
            <span class="status-tag-list <?= htmlspecialchars($item['status']) ?>"><?= ucfirst($item['status']) ?></span><br>
            <button class="btn btn-link btn-sm text-decoration-none mt-2" data-bs-toggle="modal" data-bs-target="#itemDetailModal<?= $item['id'] ?>">
              <i class="bi bi-eye me-1"></i>View Details
            </button>
          </div>
        </div>

        <!-- Item Details Modal -->
        <div class="modal fade" id="itemDetailModal<?= $item['id'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><?= htmlspecialchars($item['item_name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <img src="<?= htmlspecialchars($imagePath) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($item['item_name']) ?>" style="width:100%;height:300px;object-fit:cover;">
                  </div>
                  <div class="col-md-6">
                    <span class="status-tag-list <?= htmlspecialchars($item['status']) ?> mb-3 d-inline-block"><?= ucfirst($item['status']) ?></span>
                    <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                    <hr>
                    <p><strong><i class="bi bi-calendar-event me-2"></i>Date:</strong> <?= htmlspecialchars($item['date']) ?></p>
                    <p><strong><i class="bi bi-geo-alt me-2"></i>Location:</strong> <?= htmlspecialchars($item['location']) ?></p>
                    <p><strong><i class="bi bi-person-fill me-2"></i>Reported By:</strong> <?= htmlspecialchars($item['reported_by']) ?></p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#messageModal<?= $item['id'] ?>">
                      <i class="bi bi-chat-dots me-1"></i> Message Reporter
                    </button>
                  </div>
                </div>
              </div>
              <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Message Modal -->
        <div class="modal fade" id="messageModal<?= $item['id'] ?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form action="send_message.php" method="POST">
                <div class="modal-header">
                  <h5 class="modal-title">Message to <?= htmlspecialchars($item['reported_by']) ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="receiver" value="<?= htmlspecialchars($item['reported_by']) ?>">
                  <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['id']) ?>">
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Your Message</label>
                    <textarea class="form-control" name="message" rows="4" placeholder="Write your message here..." required></textarea>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i> Send Message</button>
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <?php endforeach; ?>
        <div id="noItemsFoundMessage" class="alert alert-info text-center" style="display:none;">No items match your criteria.</div>
      <?php endif; ?>
    </div>
  </div>

  <footer class="bg-white text-dark py-4 mt-5 border-top">
    <div class="container text-center small text-muted">&copy; 2025 AgoraBoard</div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.addEventListener("DOMContentLoaded", function() {
    const items = document.querySelectorAll('.item-row');
    const tabs = document.querySelectorAll('#itemTabs .nav-link');
    const searchInput = document.getElementById('searchInput');
    const noItemsMessage = document.getElementById('noItemsMessage');
    const noItemsFoundMessage = document.getElementById('noItemsFoundMessage');
    let currentFilter = 'lost-item';
    let currentSearch = '';
    function filterAndSearch() {
      let visibleCount = 0;
      items.forEach(item => {
        const filterMatch = item.getAttribute('data-filter-tab') === currentFilter;
        const searchMatch = item.getAttribute('data-search').includes(currentSearch);
        if (filterMatch && searchMatch) {
          item.style.display = 'flex';
          visibleCount++;
        } else item.style.display = 'none';
      });
      if (noItemsFoundMessage) noItemsFoundMessage.style.display = (visibleCount === 0 && items.length > 0) ? 'block' : 'none';
      if (noItemsMessage) noItemsMessage.style.display = 'none';
    }
    tabs.forEach(tab => {
      tab.addEventListener('click', function() {
        tabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        currentFilter = this.getAttribute('data-tab-filter');
        filterAndSearch();
      });
    });
    searchInput.addEventListener('input', function() {
      currentSearch = this.value.toLowerCase().trim();
      filterAndSearch();
    });
    filterAndSearch();
  });
  </script>
</body>
</html>
