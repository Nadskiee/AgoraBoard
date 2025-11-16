<?php
// --- 1. INCLUDE DATABASE CONNECTION ---
// This line brings in the $pdo variable (PDO connection object) from db.php
require_once 'db_connect.php'; 

// --- 2. FETCH JOB DATA ---
$jobs = [];

// --- DUMMY DATA FOR TESTING (OPTIONAL: KEEP THIS WHILE YOU TEST) ---
$dummy_jobs = [
    [
        'id' => 99999, 
        'title' => 'Senior Frontend Developer',
        'description' => 'We\'re seeking an experienced Frontend Developer to join our team. You\'ll be responsible for designing and maintaining web applications, collaborating with designers and backend engineers, and implementing best practices. Requirements include 5+ years of React experience, strong TypeScript skills, and familiarity with modern frontend tooling.',
        'employer' => 'TechVision Solutions',
        'contact_info' => 'careers@techvision.com, +63 912 345 6789',
        'posted_by' => 'HR Manager',
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')), 
    ],
    [
        'id' => 99998, 
        'title' => 'UX/UI Designer',
        'description' => 'Create beautiful and intuitive user interfaces for web and mobile applications. Collaborate with product teams to define and implement innovative solutions for the product direction, visuals, and experience.',
        'employer' => 'Creative Digital Agency',
        'contact_info' => 'hello@creativedigital.com, +63 917 234 5678',
        'posted_by' => 'Design Lead',
        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')), 
    ],
];

// --- 3. FETCH REAL JOB DATA (Original Logic) ---
$sql = "SELECT id, title, description, employer, contact_info, posted_by, created_at FROM jobs ORDER BY created_at DESC";

try {
    $stmt = $pdo->query($sql);

    if ($stmt) {
        $real_jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $jobs = array_merge($real_jobs, $dummy_jobs); 
        
        usort($jobs, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
    }
} catch (PDOException $e) {
    $jobs = $dummy_jobs;
}

$jobCount = count($jobs);

// Close the connection
$pdo = null; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Opportunities - AgoraBoard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        /* UPDATED COLORS TO MATCH PUBLIC SAFETY PAGE */
        :root {
            --sage: #10b981;
            --sage-light: #059669;
            --sage-dark: #047857;
            --cream: #f5f5f0;
            --bg: #fdfdfc;
            --muted-text: #6c757d;
            --dark-text: #3b3a36;
            --border-color: #eae8e3;
        }

        body {
            background-color: var(--bg);
            color: var(--dark-text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            max-width: 1140px; /* UPDATED FROM 900px */
            margin: auto;
        }

        /* NEW: Back to Dashboard Button Style */
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

        /* NEW: Page Header Style */
        .page-header h1 {
            font-weight: 700;
            color: var(--sage-dark);
        }
        .page-header p {
            color: var(--muted-text);
        }

        /* NEW: Search Icon Color */
        .search-icon {
            color: var(--sage);
        }
        
        .job-card {
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            background-color: var(--cream); /* UPDATED */
            transition: box-shadow 0.2s ease-in-out, transform 0.2s ease-in-out;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
        }

        .job-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transform: translateY(-3px); /* Added subtle lift */
        }
        
        .job-card h4 {
            color: var(--dark-text);
        }

        .job-meta span, .text-muted {
            color: var(--muted-text) !important;
            font-size: 0.875rem; 
        }

        .job-meta i {
            color: #6c757d; 
            font-size: 0.8rem;
        }
        
        /* NEW: Button Style */
        .btn-gradient {
            background: linear-gradient(135deg, var(--sage), var(--sage-dark));
            border: none;
            color: white;
            font-weight: 500;
            border-radius: 50px;
            transition: all 0.3s ease;
            padding: 0.5rem 1rem; 
            font-size: 0.9rem;
            min-width: 120px;
        }

        .btn-gradient:hover {
            background: linear-gradient(135deg, var(--sage-light), var(--sage-dark));
            transform: translateY(-2px);
            color: #fff;
        }

        .contact-info-line {
            display: flex;
            gap: 1.5rem; 
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .contact-item-inline {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: var(--muted-text);
        }

        .contact-item-inline i {
            color: var(--sage); /* UPDATED */
            margin-right: 0.4rem;
        }
        .contact-item-inline a {
            color: var(--dark-text);
            text-decoration: none;
        }

        .modal-content {
             border-color: var(--border-color);
             background-color: var(--bg); /* Match body bg */
        }
        .contact-item {
            background-color: #f8f9fa; 
            border: 1px solid #dee2e6;
        }

        .form-control:focus {
            border-color: var(--sage); /* UPDATED */
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25); /* UPDATED */
        }
        
        /* NEW: Footer Style */
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

    <div class="container mt-4">
        <a href="dashboard.php" class="back-btn-top">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <div class="container main-container py-5">

        <div class="text-center mb-5 page-header">
            <h1 class="fw-bold"><i class="fas fa-briefcase me-2"></i>Job Opportunities</h1>
            <p class="text-muted">Discover exciting career opportunities shared within the AgoraBoard community.</p>
        </div>

        <div class="input-group input-group-lg mb-4 shadow-sm">
            <span class="input-group-text bg-white" style="border-color: var(--border-color);"><i class="fas fa-search search-icon"></i></span>
            <input type="text" class="form-control" placeholder="Search by job title, employer, or keyword..." style="border-color: var(--border-color);">
        </div>

        <p class="text-muted mb-4"><?php echo $jobCount; ?> jobs found</p>

        <div class="job-listings">
            <?php foreach ($jobs as $job): ?>
            <?php
                // Process Data For Display
                $full_desc = htmlspecialchars($job['description']);
                // Use nl2br to preserve line breaks in the modal
                $full_desc_modal = nl2br(htmlspecialchars($job['description']));
                
                $short_desc = strlen($full_desc) > 120 ? substr($full_desc, 0, 120) . '...' : $full_desc;
                $post_date = date('F j, Y', strtotime($job['created_at']));
                
                // Parse contact info (always needs two parts, even if one is empty)
                $contact_parts = array_pad(array_map('trim', explode(',', $job['contact_info'])), 2, '');
                $email = $contact_parts[0];
                $phone = $contact_parts[1];
            ?>
            
            <div class="job-card p-4 mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    
                    <div class="flex-grow-1 me-4">
                        <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($job['title']); ?></h4>
                        
                        <div class="job-meta d-flex align-items-center flex-wrap mb-3" style="gap: 1rem;">
                            <span class="d-flex align-items-center"><i class="far fa-building me-1"></i><?php echo htmlspecialchars($job['employer']); ?></span>
                            <span class="d-flex align-items-center"><i class="fas fa-user me-1"></i>Posted by <?php echo htmlspecialchars($job['posted_by']); ?></span>
                            <span class="d-flex align-items-center"><i class="fas fa-calendar-alt me-1"></i><?php echo htmlspecialchars($post_date); ?></span>
                        </div>
                        
                        <p class="text-secondary mb-3"><?php echo $short_desc; // short_desc is already escaped ?></p>
                        
                        <div class="contact-info-line">
                            <?php if (!empty($email)): ?>
                                <div class="contact-item-inline">
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($email); ?>"><?php echo htmlspecialchars($email); ?></a>
                                    <i class="far fa-copy ms-2 text-muted" style="cursor: pointer;" onclick="copyToClipboard('<?php echo htmlspecialchars($email); ?>', this)"></i>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($phone)): ?>
                                <div class="contact-item-inline">
                                    <i class="fas fa-phone"></i>
                                    <a href="tel:<?php echo htmlspecialchars($phone); ?>"><?php echo htmlspecialchars($phone); ?></a>
                                    <i class="far fa-copy ms-2 text-muted" style="cursor: pointer;" onclick="copyToClipboard('<?php echo htmlspecialchars($phone); ?>', this)"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="flex-shrink-0">
                        <button class="btn btn-gradient" 
                                data-bs-toggle="modal" 
                                data-bs-target="#jobDetailModal"
                                data-title="<?php echo htmlspecialchars($job['title']); ?>"
                                data-company="<?php echo htmlspecialchars($job['employer']); ?>"
                                data-posted-by="<?php echo htmlspecialchars($job['posted_by']); ?>"
                                data-date="<?php echo htmlspecialchars($post_date); ?>"
                                data-long-desc="<?php echo $full_desc_modal; // Use nl2br version ?>"
                                data-email="<?php echo htmlspecialchars($email); ?>"
                                data-phone="<?php echo htmlspecialchars($phone); ?>">
                            <i class="fas fa-eye me-1"></i> View Details
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="text-center">
        &copy; 2025 AgoraBoard — Job Opportunities & Community Bulletin
    </footer>

    <div class="modal fade" id="jobDetailModal" tabindex="-1" aria-labelledby="jobDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bold" id="modal-title">Job Title Here</h4>
                        <div class="job-meta d-flex align-items-center flex-wrap" style="gap: 1rem;">
                            <span class="d-flex align-items-center" id="modal-company"><i class="far fa-building me-1"></i>Company</span>
                            <span class="d-flex align-items-center" id="modal-posted-by"><i class="fas fa-user me-1"></i>Posted by</span>
                            <span class="d-flex align-items-center" id="modal-date"><i class="fas fa-calendar-alt me-1"></i>Date</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-4">
                    <h5 class="fw-bold">Job Description</h5>
                    <p id="modal-long-desc" class="text-secondary" style="white-space: pre-wrap;">Full job description goes here.</p>
                    
                    <h5 class="fw-bold mt-4">Contact Information</h5>
                    <div class="d-flex flex-column gap-2">
                        <div class="contact-item p-3 rounded d-flex justify-content-between align-items-center" id="modal-email-container">
                            <div>
                               <i class="fas fa-envelope me-2 text-muted"></i>
                               <span id="modal-email">email@example.com</span>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary copy-btn" data-copy-target="#modal-email">
                                <i class="far fa-copy me-1"></i> Copy
                            </button>
                        </div>
                        <div class="contact-item p-3 rounded d-flex justify-content-between align-items-center" id="modal-phone-container">
                             <div>
                                <i class="fas fa-phone me-2 text-muted"></i>
                                <span id="modal-phone">+12 345 678 90</span>
                             </div>
                            <button class="btn btn-sm btn-outline-secondary copy-btn" data-copy-target="#modal-phone">
                                <i class="far fa-copy me-1"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Universal Copy Function for card and modal
        function copyToClipboard(text, element) {
            navigator.clipboard.writeText(text).then(() => {
                const originalContent = element.innerHTML;
                element.innerHTML = '<i class="fas fa-check"></i>';
                element.classList.add('text-success'); 
                element.style.cursor = 'default';

                setTimeout(() => {
                    element.innerHTML = originalContent;
                    element.classList.remove('text-success');
                    element.style.cursor = 'pointer';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
        
        // Modal functionality
        document.addEventListener('DOMContentLoaded', function () {
            const jobDetailModal = document.getElementById('jobDetailModal');
            
            jobDetailModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const title = button.getAttribute('data-title');
                const company = button.getAttribute('data-company');
                const postedBy = button.getAttribute('data-posted-by');
                const date = button.getAttribute('data-date');
                const longDesc = button.getAttribute('data-long-desc');
                const email = button.getAttribute('data-email');
                const phone = button.getAttribute('data-phone');

                jobDetailModal.querySelector('#modal-title').textContent = title;
                jobDetailModal.querySelector('#modal-company').innerHTML = `<i class="far fa-building me-1"></i>${company}`;
                jobDetailModal.querySelector('#modal-posted-by').innerHTML = `<i class="fas fa-user me-1"></i>Posted by ${postedBy}`;
                jobDetailModal.querySelector('#modal-date').innerHTML = `<i class="fas fa-calendar-alt me-1"></i>${date}`;
                
                // Use .innerHTML to render the <br> tags
                jobDetailModal.querySelector('#modal-long-desc').innerHTML = longDesc; 
                
                const emailEl = jobDetailModal.querySelector('#modal-email');
                const phoneEl = jobDetailModal.querySelector('#modal-phone');
                const emailContainer = jobDetailModal.querySelector('#modal-email-container');
                const phoneContainer = jobDetailModal.querySelector('#modal-phone-container');

                if (email) {
                    emailEl.textContent = email;
                    emailContainer.style.display = 'flex';
                } else {
                    emailContainer.style.display = 'none';
                }

                if (phone) {
                    phoneEl.textContent = phone;
                    phoneContainer.style.display = 'flex';
                } else {
                    phoneContainer.style.display = 'none';
                }
            });

            // Re-implement the copy button handler for the modal buttons
            const modalCopyButtons = jobDetailModal.querySelectorAll('.copy-btn');
            modalCopyButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const targetSelector = this.getAttribute('data-copy-target');
                    const textToCopy = jobDetailModal.querySelector(targetSelector).innerText;
                    
                    navigator.clipboard.writeText(textToCopy).then(() => {
                        const originalText = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
                        this.classList.add('btn-success');
                        this.classList.remove('btn-outline-secondary', 'copy-btn');
                        
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.classList.remove('btn-success');
                            this.classList.add('btn-outline-secondary', 'copy-btn');
                        }, 2000);
                    }).catch(err => {
                        console.error('Failed to copy: ', err);
                    });
                });
            });
        });
    </script>
</body>
</html>