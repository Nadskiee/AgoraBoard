<?php
session_start();
require_once 'db_connect.php';

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
      /* Your new green theme colors */
      --sage: #10b981;
      /* Primary Green */
      --sage-light: #059669;
      /* Slightly darker/Emerald-600 for contrast/accents */
      --sage-dark: #047857;
      /* Your other colors */
      --cream: #f5f5f0; /* Keeping cream for cards and modals */
      --bg: #fdfdfc; /* Reverted main background to a very light, almost white */
      --muted-text: #6c757d;
      --dark-text: #3b3a36;
      --border-color: #eae8e3; /* Consistent border color */

      /* Existing variables */
      --emerald-50: #ecfdf5;
      --emerald-500: #10b981;
      --emerald-600: #059669;
      --emerald-700: #047857;
      --blue-600: #2563eb;
      --blue-700: #1d4ed8;
      --danger-500: #ef4444;
      --danger-600: #dc2626;
      --warning-400: #facc15;
      --warning-500: #eab308;
      --gray-50: #f9fafb;
      --gray-100: #f3f4f6;
      --gray-200: #e5e7eb;
      --gray-400: #9ca3af;
      --gray-600: #4b5563;
      --gray-700: #374151;
      --gray-800: #1f2937;
    }

    /* --- 🎨 REVISED --- */
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg); /* Main page background */
    }

    /* --- 🎨 REMOVED .bg-cream-card class --- */
    /* It's no longer needed if we target specific elements directly */

   .back-btn-top {
            display: inline-block;
            background: linear-gradient(135deg, var(--sage), var(--sage-dark));
            color: #fff;
            border-radius: 50px;
            padding: 0.5rem 1.3rem;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .back-btn-top:hover {
            background: linear-gradient(135deg, var(--sage-light), var(--sage-dark));
            color: #fff;
            transform: translateY(-2px);
            text-decoration: none;
        }

    /* New Header */
    .page-header {
      text-align: center;
      padding: 3rem 0;
    }
    .page-header h1 {
      font-weight: 700;
      color: var(--dark-text);
    }
    .page-header p {
      font-size: 1.1rem;
      color: var(--muted-text);
      margin-bottom: 1.5rem;
    }

    /* New Filter Buttons */
    .filter-btn-group .nav-link {
      background-color: var(--gray-100); /* Reverted to match original design */
      color: var(--gray-700);
      border: 1px solid var(--gray-200); /* Reverted to match original design */
      border-radius: 50px !important; /* Force pill shape */
      margin: 0 5px;
      font-weight: 500;
      transition: all 0.2s ease-in-out;
    }
    .filter-btn-group .nav-link:hover {
      background-color: var(--gray-200);
    }
    .filter-btn-group .nav-link.active {
      background-color: var(--sage-light);
      color: white;
      border-color: var(--sage-light);
    }

    /* --- 🎨 UPDATED --- */
    /* New Item Card */
    .item-card {
      background-color: var(--cream); /* ONLY THE ITEM CARD IS CREAM */
      border: 1px solid var(--border-color); /* Consistent border */
      border-radius: 12px;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
      transition: all 0.2s ease-in-out;
      overflow: hidden; /* Ensures rounded corners on image */
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    .item-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px -3px rgba(0,0,0,0.07), 0 4px 6px -2px rgba(0,0,0,0.05);
    }
    .card-img-top-wrapper {
      position: relative;
    }
    .card-img-top {
      width: 100%;
      height: 220px;
      object-fit: cover;
    }

    /* New Status Tags (on image) */
    .status-tag {
      font-size: 0.8rem;
      font-weight: 600;
      padding: 0.35em 0.75em;
    }
    .status-tag.status-lost {
      background-color: var(--danger-500);
      color: white;
    }
    .status-tag.status-found {
      background-color: var(--sage);
      color: white;
    }
    
    /* New Category Tag (in body) */
    .category-tag {
      font-size: 0.75rem;
      font-weight: 500;
      background-color: var(--gray-100); /* Reverted to match original design */
      color: var(--gray-700);
      padding: 0.25em 0.6em;
    }

    .item-card .card-body {
      display: flex;
      flex-direction: column;
      flex-grow: 1; /* Makes card body fill height */
    }
    .item-card .card-title {
      font-weight: 600;
      color: var(--dark-text);
    }
    .item-card .card-text {
      color: var(--muted-text);
      font-size: 0.9rem;
      flex-grow: 1; /* Pushes meta and button to bottom */
    }
    .item-meta {
      font-size: 0.85rem;
      color: var(--muted-text);
    }
    .item-meta i {
      color: var(--gray-400);
      width: 20px; /* Aligns icons */
    }

    /* Button Styling */
    .report-button {
      background-color: var(--sage);
      border-color: var(--sage);
      font-weight: 600;
      color: white;
    }
    .report-button:hover {
      background-color: var(--sage-light);
      border-color: var(--sage-light);
    }
    .item-card .btn-primary {
      background-color: var(--sage);
      border-color: var(--sage);
      font-weight: 500;
    }
    .item-card .btn-primary:hover {
      background-color: var(--sage-light);
      border-color: var(--sage-light);
    }

    /* Standardize all success buttons to your theme */
    .btn-success {
       background-color: var(--sage);
       border-color: var(--sage);
    }
    .btn-success:hover {
       background-color: var(--sage-light);
       border-color: var(--sage-light);
    }

    /* --- 🎨 REVISED Modal/Search Theme Fixes (only apply to specific elements) --- */
    /* Modal content should be cream */
    .modal-content {
       background-color: var(--cream);
       border-color: var(--border-color);
       color: var(--dark-text);
    }
    /* Modal footer should match the main page bg */
    .modal-footer {
       background-color: var(--bg); /* Match main page background */
       border-top: 1px solid var(--border-color);
    }
    .modal-header {
       border-bottom: 1px solid var(--border-color);
    }
    .modal-title {
        color: var(--dark-text);
    }

    /* Search bar to match the main content container's background for better integration */
    .input-group-text, 
    .form-control {
        background-color: var(--bg); /* Light background for search */
        border-color: var(--border-color) !important; /* Use border color */
        color: var(--dark-text);
    }
    /* Ensure the search bar inside the main cream section has its own styling */
    .bg-white .input-group-text, 
    .bg-white .form-control {
        background-color: var(--gray-100) !important; /* Slightly off-white for search bar in the main white section */
        border: 1px solid var(--gray-200) !important;
    }

    /* Placeholder text for search input */
    .form-control::placeholder {
        color: var(--muted-text);
    }
    footer {
            background-color: var(--sage-dark);
            color: #e7f5ee;
            padding: 1.2rem 0;
            font-size: 0.9rem;
            margin-top: 4rem;
        }

 </style>
</head>
<body>

  <div class="container mt-3 mb-0">
    <a href="dashboard.php" class="back-btn-top"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
  </div>

  <div class="page-header container">
    <h1 class="display-5">Recent Items</h1>
    <p>Browse through recently reported lost and found items. Help reunite people with their belongings.</p>
    
    <div class="filter-btn-group nav justify-content-center" id="itemTabs" role="tablist">
      <button class="btn nav-link active" data-tab-filter="all">All Items</button>
      <button class="btn nav-link" data-tab-filter="lost-item">Lost Items</button>
      <button class="btn nav-link" data-tab-filter="lost-pet">Lost Pets</button>
      <button class="btn nav-link" data-tab-filter="found">Found</button>
    </div>
  </div>

  <div class="container">
    <div class="bg-white p-4 p-md-5 rounded-3 shadow-sm mb-5">
      
      <div class="row g-3 mb-4 align-items-center">
        <div class="col-md-8 col-lg-9">
          <div class="input-group">
            <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
            <input type="search" class="form-control border-0 bg-light" placeholder="Search by item name, description, or location..." id="searchInput">
          </div>
        </div>
        <div class="col-md-4 col-lg-3 text-md-end">
          <button class="btn report-button w-100" data-bs-toggle="modal" data-bs-target="#reportItemModal">
            <i class="bi bi-plus-lg me-2"></i> Report New Item
          </button>
        </div>
      </div>

      <div id="itemList" class="row g-4">
        <?php if (empty($items)): ?>
          <div id="noItemsMessage" class="alert alert-info text-center">Wala pay na-report nga items. Be the first to report!</div>
        <?php else: ?>
          <?php foreach ($items as $item): 
            // PHP logic for filters (untouched)
            $filterCat = $item['status'];
            if ($filterCat === 'lost' && $item['category'] === 'Pet') $filterCat = 'lost-pet';
            else if ($filterCat === 'lost') $filterCat = 'lost-item';
            $searchData = strtolower($item['item_name'] . ' ' . $item['description'] . ' ' . $item['location']);
            $imagePath = $item['image'] ?? 'assets/placeholder.png';
            if (empty($item['image']) || !file_exists($item['image'])) $imagePath = 'assets/placeholder.png';

            // New: PHP logic for status tag styling
            $status_class = $item['status'] === 'found' ? 'status-found' : 'status-lost';
          ?>

          <div class="col-lg-4 col-md-6 item-card-wrapper" data-filter-tab="<?= $filterCat ?>" data-search="<?= htmlspecialchars($searchData) ?>">
            <div class="card item-card">
              
              <div class="card-img-top-wrapper">
                <img src="<?= htmlspecialchars($imagePath) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['item_name']) ?>">
                <span class="badge status-tag <?= $status_class ?> position-absolute top-0 end-0 m-3"><?= ucfirst($item['status']) ?></span>
              </div>
              
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h5 class="card-title mb-0 me-2"><?= htmlspecialchars($item['item_name']) ?></h5>
                  <span class="badge rounded-pill category-tag flex-shrink-0"><?= htmlspecialchars($item['category']) ?></span>
                </div>
                
                <p class="card-text mb-3">
                  <?= htmlspecialchars(substr($item['description'], 0, 100)) ?><?= strlen($item['description']) > 100 ? '...' : '' ?>
                </p>

                <div class="item-meta mb-3">
                  <div class="mb-1 text-truncate"><i class="bi bi-geo-alt-fill me-2"></i><?= htmlspecialchars($item['location']) ?></div>
                  <div class="text-truncate"><i class="bi bi-calendar-event-fill me-2"></i><?= htmlspecialchars($item['date']) ?></div>
                </div>

                <button class="btn btn-primary w-100 mt-auto" data-bs-toggle="modal" data-bs-target="#itemDetailModal<?= $item['id'] ?>">
                  View Details
                </button>
              </div>
            </div>
          </div>
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
                      <span class="badge status-tag <?= $status_class ?> mb-3 d-inline-block"><?= ucfirst($item['status']) ?></span>
                      <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                      <hr>
                      <p><strong><i class="bi bi-calendar-event me-2"></i>Date:</strong> <?= htmlspecialchars($item['date']) ?></p>
                      <p><strong><i class="bi bi-geo-alt me-2"></i>Location:</strong> <?= htmlspecialchars($item['location']) ?></p>
                      <p><strong><i class="bi bi-person-fill me-2"></i>Reported By:</strong> <?= htmlspecialchars($item['reported_by']) ?></p>
                      <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#messageModal<?= $item['id'] ?>">
                        <i class="bi bi-chat-dots me-1"></i> Message Reporter
                      </button>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
              </div>
            </div>
          </div>

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
  </div>
  
  <div class="modal fade" id="reportItemModal" tabindex="-1" aria-labelledby="reportItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        
        <form action="report_item.php" method="POST" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title" id="reportItemModalLabel">Report a New Item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            
            <input type="hidden" name="posted_by" value="<?= htmlspecialchars($prefill_name) ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" required>
                  <option value="lost">I Lost Something</option>
                  <option value="found">I Found Something</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="category" class="form-label">Category</label>
                <select class="form-select" id="category" name="category" required>
                  <option value="Electronics">Electronics</option>
                  <option value="Keys">Keys</option>
                  <option value="Wallet">Wallet/Bags</option>
                  <option value="Pet">Pet</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              
              <div class="col-12">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" class="form-control" id="item_name" name="item_name" placeholder="e.g., Black Smartphone, Brown Dog" required>
              </div>
              
              <div class="col-12">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Describe the item, including any identifying marks." required></textarea>
              </div>

              <div class="col-md-6">
                <label for="location" class="form-label">Last Seen Location</label>
                <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Starbucks, 5th Ave" required>
              </div>
              <div class="col-md-6">
                <label for="date" class="form-label">Date Lost/Found</label>
                <input type="date" class="form-control" id="date" name="date_lost_found" required>
              </div>
              
              <div class="col-12">
                <label for="itemImage" class="form-label">Upload Image</label>
                <input class="form-control" type="file" id="itemImage" name="itemImage" accept="image/png, image/jpeg, image/jpg" required>
                <div class="form-text">Max file size: 5MB. Allowed types: JPG, PNG, JPEG.</div>
              </div>
              
            </div>
            
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary report-button">Submit Report</button>
          </div>
        </form>
        
      </div>
    </div>
  </div>
  
   <footer class="text-center">
        &copy; 2025 AgoraBoard — Lost and Found Items
    </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
  document.addEventListener("DOMContentLoaded", function() {
    // 1. Updated selector to find the new cards
    const items = document.querySelectorAll('.item-card-wrapper');
    const tabs = document.querySelectorAll('#itemTabs .nav-link');
    const searchInput = document.getElementById('searchInput');
    const noItemsMessage = document.getElementById('noItemsMessage');
    const noItemsFoundMessage = document.getElementById('noItemsFoundMessage');
    
    // 2. Set default filter to 'all' to match the new 'All Items' button
    let currentFilter = 'all';
    let currentSearch = '';

    function filterAndSearch() {
      let visibleCount = 0;
      items.forEach(item => {
        // 3. Added check for 'all' filter
        const filterMatch = (currentFilter === 'all') || (item.getAttribute('data-filter-tab') === currentFilter);
        const searchMatch = item.getAttribute('data-search').includes(currentSearch);
        
        if (filterMatch && searchMatch) {
          // 4. Changed display to 'block' for Bootstrap columns
          item.style.display = 'block';
          visibleCount++;
        } else {
          item.style.display = 'none';
        }
      });
      
      if (noItemsFoundMessage) {
        noItemsFoundMessage.style.display = (visibleCount === 0 && items.length > 0) ? 'block' : 'none';
      }
      if (noItemsMessage && items.length === 0) {
        noItemsMessage.style.display = 'block';
      } else if (noItemsMessage) {
        noItemsMessage.style.display = 'none';
      }
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

    // Initial filter on page load
    filterAndSearch();
  });
  </script>
</body>
</html>