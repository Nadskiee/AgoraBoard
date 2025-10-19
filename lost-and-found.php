<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lost & Found - AgoraBoard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
      --danger-100: #fee2e2;
      --danger-500: #ef4444;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--emerald-50);
    }

    .header-controls {
        background-color: white;
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 8px 25px rgba(0,0,0,0.07);
        margin-bottom: 2rem;
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
    }
    .btn-back:hover {
        color: var(--emerald-600);
    }

    .filter-btn-group .btn {
        border-radius: 8px;
        font-weight: 500;
        color: var(--emerald-700);
        background-color: white;
        border: 1px solid var(--emerald-100);
    }
    .filter-btn-group .btn.active {
        background-color: var(--emerald-500);
        color: white;
        border-color: var(--emerald-500);
    }
    
    .item-card {
      background: white;
      border: 1px solid #e5e7eb;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      transition: box-shadow 0.3s ease, border-color 0.3s ease;
      overflow: hidden;
      position: relative;
    }
    .item-card:hover {
      border-color: var(--emerald-500);
      box-shadow: 0 10px 30px rgba(16, 185, 129, 0.15);
    }
    .item-card img {
        height: 220px;
        object-fit: cover;
    }
    .item-card .card-body {
        padding: 1rem 1.25rem;
    }
    .status-tag {
        position: absolute;
        top: 10px;
        right: 10px;
        padding: 0.25rem 0.6rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 20px;
        color: white;
    }
    .status-tag.found { background-color: var(--emerald-500); }
    .status-tag.lost { background-color: var(--danger-500); }

    .modal-header {
        border-bottom: 1px solid var(--emerald-100);
    }
    .modal-footer {
        border-top: 1px solid var(--emerald-100);
    }

  </style>
</head>
<body>
  <div class="container py-5">
    <div class="mb-4">
        <a href="dashboard.php" class="btn btn-link btn-back text-decoration-none p-0">
            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
        </a>
    </div>
    <div class="text-center mb-4">
        <h1 class="fw-bold text-dark display-5">Lost & Found</h1>
        <p class="text-secondary fs-6">Help reunite people with their belongings.</p>
    </div>
    
    <!-- Controls -->
    <div class="header-controls">
        <div class="row g-3">
            <div class="col-lg-5">
                <input type="search" class="form-control" placeholder="Search by item name, location...">
            </div>
            <div class="col-lg-4">
                <div class="btn-group w-100 filter-btn-group" role="group">
                    <button type="button" class="btn active">All</button>
                    <button type="button" class="btn">Lost</button>
                    <button type="button" class="btn">Found</button>
                </div>
            </div>
            <div class="col-lg-3 text-end">
                <button class="btn btn-action w-100" data-bs-toggle="modal" data-bs-target="#postItemModal">
                    <i class="fas fa-plus me-2"></i>Post Item
                </button>
            </div>
        </div>
    </div>
    
    <!-- Grid of Items -->
    <div class="row">
      <!-- Item Cards will be dynamically inserted here -->
      <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card item-card h-100 d-flex flex-column">
          <img src="https://images.unsplash.com/photo-1594223274512-ad4803739b7c?q=80&w=2070&auto=format&fit=crop" class="card-img-top" alt="Wallet">
          <div class="card-body flex-grow-1">
            <h6 class="card-title fw-bold mb-1">Black Leather Wallet</h6>
            <p class="card-text text-secondary small mb-1"><i class="fas fa-map-marker-alt me-1 text-muted"></i> Downtown Coffee</p>
            <p class="card-text text-secondary small"><i class="fas fa-calendar-alt me-1 text-muted"></i> Oct 15, 2025</p>
          </div>
           <div class="card-footer bg-white border-0 px-3 pb-3">
             <button class="btn btn-action w-100">Claim Item</button>
           </div>
          <div class="status-tag found">Found</div>
        </div>
      </div>
      <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card item-card h-100 d-flex flex-column">
          <img src="https://images.unsplash.com/photo-1618384887924-3366ab1a72ca?q=80&w=1972&auto=format=fit=crop" class="card-img-top" alt="Keychain">
          <div class="card-body flex-grow-1">
            <h6 class="card-title fw-bold mb-1">Silver Keychain</h6>
            <p class="card-text text-secondary small mb-1"><i class="fas fa-map-marker-alt me-1 text-muted"></i> Central Park</p>
            <p class="card-text text-secondary small"><i class="fas fa-calendar-alt me-1 text-muted"></i> Oct 14, 2025</p>
          </div>
          <div class="card-footer bg-white border-0 px-3 pb-3">
             <button class="btn btn-action w-100">Report Found</button>
           </div>
          <div class="status-tag lost">Lost</div>
        </div>
      </div>
      <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card item-card h-100 d-flex flex-column">
          <img src="https://images.unsplash.com/photo-1585258955234-86a374f67c9c?q=80&w=1974&auto=format=fit=crop" class="card-img-top" alt="Backpack">
          <div class="card-body flex-grow-1">
            <h6 class="card-title fw-bold mb-1">Blue Backpack</h6>
            <p class="card-text text-secondary small mb-1"><i class="fas fa-map-marker-alt me-1 text-muted"></i> Train Station</p>
            <p class="card-text text-secondary small"><i class="fas fa-calendar-alt me-1 text-muted"></i> Oct 13, 2025</p>
          </div>
           <div class="card-footer bg-white border-0 px-3 pb-3">
             <button class="btn btn-action w-100">Claim Item</button>
           </div>
          <div class="status-tag found">Found</div>
        </div>
      </div>
      <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
        <div class="card item-card h-100 d-flex flex-column">
          <img src="https://images.unsplash.com/photo-1611652033935-a6e5942c4b1b?q=80&w=1964&auto=format&fit=crop" class="card-img-top" alt="Necklace">
          <div class="card-body flex-grow-1">
            <h6 class="card-title fw-bold mb-1">Gold Necklace</h6>
            <p class="card-text text-secondary small mb-1"><i class="fas fa-map-marker-alt me-1 text-muted"></i> Shopping Mall</p>
            <p class="card-text text-secondary small"><i class="fas fa-calendar-alt me-1 text-muted"></i> Oct 12, 2025</p>
          </div>
           <div class="card-footer bg-white border-0 px-3 pb-3">
             <button class="btn btn-action w-100">Report Found</button>
           </div>
          <div class="status-tag lost">Lost</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Post Item Modal -->
  <div class="modal fade" id="postItemModal" tabindex="-1" aria-labelledby="postItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="postItemModalLabel">Post a Lost or Found Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="mb-3">
              <label for="itemTitle" class="form-label">Item Title</label>
              <input type="text" class="form-control" id="itemTitle" placeholder="e.g., Black Leather Wallet">
            </div>
            <div class="mb-3">
              <label for="itemCategory" class="form-label">Category</label>
              <select class="form-select" id="itemCategory">
                <option value="lost">Lost</option>
                <option value="found">Found</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="itemLocation" class="form-label">Location</label>
              <input type="text" class="form-control" id="itemLocation" placeholder="Where was it lost or found?">
            </div>
            <div class="mb-3">
              <label for="itemDescription" class="form-label">Description</label>
              <textarea class="form-control" id="itemDescription" rows="3" placeholder="Describe the item in detail..."></textarea>
            </div>
             <div class="mb-3">
              <label for="contactInfo" class="form-label">Contact Information</label>
              <input type="email" class="form-control" id="contactInfo" placeholder="your@email.com">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-action">Post Item</button>
        </div>
      </div>
    </div>
  </div>

  <footer class="bg-dark text-light py-4 mt-5"><div class="container text-center">&copy; 2025 AgoraBoard</div></footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

