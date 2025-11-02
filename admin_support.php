<?php
session_start();
require_once "db_connect.php";

// Check if admin is logged in
if (!isset($_SESSION['currentUser']) || $_SESSION['currentUser']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle marking as read
if (isset($_GET['mark_read'])) {
    $id = (int)$_GET['mark_read'];
    $stmt = $pdo->prepare("UPDATE support_requests SET status = 'read' WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_support.php");
    exit;
}

// Fetch all support requests
$stmt = $pdo->query("SELECT * FROM support_requests ORDER BY created_at DESC");
$supportRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$active_page = 'support'; // For sidebar highlight
$adminName = $_SESSION['currentUser']['name'] ?? "Admin";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Support Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/admin_dashboard.css?v=<?php echo time(); ?>">

</head>
<<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Support Requests</h1>
                </div>

                <?php if (count($supportRequests) === 0): ?>
                    <div class="alert alert-info">No support requests found.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>User Email</th>
                                    <th>Name</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Submitted At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($supportRequests as $req): ?>
                                    <tr class="table-status-<?= $req['status'] ?>">
                                        <td><?= htmlspecialchars($req['id']) ?></td>
                                        <td><?= htmlspecialchars($req['user_email']) ?></td>
                                        <td><?= htmlspecialchars($req['user_name'] ?? '-') ?></td>
                                        <td><?= nl2br(htmlspecialchars($req['message'])) ?></td>
                                        <td><?= ucfirst($req['status']) ?></td>
                                        <td><?= htmlspecialchars($req['created_at']) ?></td>
                                        <td>
                                            <?php if ($req['status'] === 'unread'): ?>
                                                <a href="?mark_read=<?= $req['id'] ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i> Mark as Read
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">Read</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        </body>

</html>