<?php
session_start();
require_once 'db.php'; // Your database connection

// Assuming the main page with the Lost/Found list and the modal is 'lost_and_found.php'
$main_page = 'lost_and_found.php';
// Define a clear success page, or just redirect back to the main page
$success_page = 'submit_report.php'; // Keep this, or change to $main_page if no confirmation page exists

// --- Function to handle errors and redirect ---
function handle_error_and_redirect($message, $target_page) {
    // Store the error message in the session to display on the form page
    $_SESSION['report_error'] = $message;
    // Redirect back to the main listings page where the form modal is
    header("Location: $target_page");
    exit;
}

// --- Basic Auth Check (optional but recommended) ---
// if (!isset($_SESSION['currentUser'])) {
//     handle_error_and_redirect('Error: You must be logged in to report an item.', $main_page);
// }

// --- Validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    handle_error_and_redirect('Invalid request method.', $main_page);
}

$required_fields = ['status', 'item_name', 'category', 'description', 'location', 'date', 'full_name', 'contact_number'];
foreach ($required_fields as $field) {
    if (empty(trim($_POST[$field]))) {
        handle_error_and_redirect("Please fill in all required fields. (" . htmlspecialchars($field) . " is missing)", $main_page);
    }
}

// --- Collect Data ---
$status = strtolower(trim($_POST['status'])); // 'lost' or 'found'
$itemName = trim($_POST['item_name']);
$category = trim($_POST['category']);
$description = trim($_POST['description']);
$location = trim($_POST['location']);
$date = trim($_POST['date']);
$reportedBy = trim($_POST['full_name']);
$contactInfo = trim($_POST['contact_number']);
$imagePath = null; // Default image path

// --- Image Upload Handling ---
if (isset($_FILES['item_photo']) && $_FILES['item_photo']['error'] == 0) {
    $uploadDir = 'uploads/'; // Make sure this folder exists and is writable!
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $fileType = mime_content_type($_FILES['item_photo']['tmp_name']);

    if (!in_array($fileType, $allowedTypes)) {
        handle_error_and_redirect('Invalid file type. Only JPG and PNG are allowed.', $main_page);
    }

    if ($_FILES['item_photo']['size'] > 5 * 1024 * 1024) { // 5MB limit
        handle_error_and_redirect('File is too large. Maximum size is 5MB.', $main_page);
    }
    
    // Create a unique filename
    $fileExtension = pathinfo($_FILES['item_photo']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $fileExtension;
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['item_photo']['tmp_name'], $targetFile)) {
        $imagePath = $targetFile; // e.g., "uploads/5f8a..."
    } else {
        handle_error_and_redirect('There was an error uploading your file.', $main_page);
    }
}

// --- Database Insertion ---
try {
    $sql = "INSERT INTO lost_and_found (
                status, 
                item_name, 
                category, 
                description, 
                last_seen_location, 
                date_lost_found, 
                posted_by, 
                contact_info, 
                image_path,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $status,
        $itemName,
        $category,
        $description,
        $location,
        $date,
        $reportedBy,
        $contactInfo,
        $imagePath
    ]);
    
    $lastId = $pdo->lastInsertId();

    // --- Success Response (Redirection) ---
    // Store a success message and redirect.
    $_SESSION['report_success'] = "Report for **" . htmlspecialchars($itemName) . "** submitted successfully! Your item ID is #$lastId.";
    
    // Change the redirect target to your desired success page (e.g., confirmation.php)
    header("Location: $success_page"); 

} catch (PDOException $e) {
    // Handle database errors
    error_log($e->getMessage()); // Log the error for debugging
    handle_error_and_redirect('A database error occurred. Please try again later.', $main_page);
}

exit;
?>