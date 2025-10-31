<?php
session_start();
require_once 'db_connect.php'; // ✅ PDO connection

// 🔐 Check if admin is logged in
if (
    !isset($_SESSION['currentUser']) ||
    $_SESSION['currentUser']['role'] !== 'admin'
) {
    header("Location: login.php");
    exit;
}



// Logged-in admin info
$adminName = $_SESSION['currentUser']['name'] ?? 'Administrator';


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/admin_dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'admin_sidebar.php'; ?>

            <!-- Main Content -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="dashboard-header border-bottom mb-4 pb-2">
                    <h1 class="h3">Manage Posts</h1>
                </div>

                <!-- Posts Table -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-teal text-white">
                        <h6 class="m-0">All Posts</h6>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-striped align-middle text-center mb-0">
                            <thead class="table-teal text-white">
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Author</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Missing Pet Notice</td>
                                    <td>Lost &amp; Found</td>
                                    <td>Juan Dela Cruz</td>
                                    <td>Oct 21, 2025</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="confirmDelete(1)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <footer class="footer text-center mt-auto pt-3">
                    &copy; <?= date('Y'); ?> AgoraBoard Admin Panel
                </footer>
            </main>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this post?")) {
                alert("Post #" + id + " deleted (simulation).");
            }
        }
    </script>
</body>

</html>