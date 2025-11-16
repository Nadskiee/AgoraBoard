<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteering - AgoraBoard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sage: #10b981;
            /* Primary Green */
            --sage-light: #059669;
            /* Slightly darker/Emerald-600 for contrast/accents */
            --sage-dark: #047857;
            --cream: #f5f5f0;
            --bg: #fdfdfc;
            --muted-text: #6c757d;
            --dark-text: #3b3a36;
            --border-color: #eae8e3;
        }

        /* Set a light background for the whole page */
        body {
            background-color: var(--bg);
            color: var(--dark-text);
        }

        /* Style for the opportunity cards */
        .opp-card, .card.shadow-sm {
            background-color: var(--cream);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            transition: transform .3s, box-shadow .3s;
        }

        .opp-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        }
        
        /* Button Overrides */
        .btn-primary {
            background-color: var(--sage);
            border-color: var(--sage);
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: var(--sage-light);
            border-color: var(--sage-light);
        }
        
        .btn-outline-primary {
            color: var(--sage);
            border-color: var(--sage);
        }
        .btn-outline-primary:hover {
            background-color: var(--sage);
            border-color: var(--sage);
            color: #ffffff;
        }
        
        .btn-outline-secondary {
            color: var(--muted-text);
            border-color: var(--border-color);
        }
        .btn-outline-secondary:hover {
            background-color: var(--bg);
            border-color: var(--border-color);
            color: var(--dark-text);
        }

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

        /* Style for the category badges */
        .badge.bg-environmental,
        .badge.bg-educational,
        .badge.bg-community {
            background-color: rgba(16, 185, 129, 0.1) !important; /* Light sage background */
            color: var(--sage-dark) !important;           
        }

        /* Card footer */
        .opp-card .card-footer {
            background-color: var(--cream);
            border-top: 1px solid var(--border-color);
        }
        
        .text-muted {
            color: var(--muted-text) !important;
        }
        
        /* Modal Content */
        .modal-content {
             background-color: var(--cream);
             border: 1px solid var(--border-color);
             color: var(--dark-text);
        }
        
        .modal-header {
            border-bottom: 1px solid var(--border-color);
        }

        /* Style for the contact info in the modal */
        .contact-item {
            background-color: var(--bg);
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        hr {
            color: var(--border-color);
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

    <div class="container py-5">
        <div class="col-lg-10 mx-auto">
            
         <div class="container mt-3 mb-0">
    <a href="dashboard.php" class="back-btn-top"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
  </div>

            <div class="text-center mb-4">
                <h2 class="fw-bold">Volunteer Opportunities</h2>
                <p class="text-muted">Make a difference in your community</p>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Filter Opportunities</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category-select" class="form-label fw-semibold">Category</label>
                            <select id="category-select" class="form-select">
                                <option>All</option>
                                <option>Environmental</option>
                                <option>Educational</option>
                                <option>Community Service</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="date-select" class="form-label fw-semibold">Date</label>
                            <select id="date-select" class="form-select">
                                <option>Anytime</option>
                                <option>This Week</option>
                                <option>This Month</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary">Apply Filters</button>
                    </div>
                </div>
            </div>

            <div class="opportunity-listings">

                <div class="card opp-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold mb-1">Community Clean-Up Drive</h5>
                                <span class="badge bg-environmental rounded-pill fw-medium">Environmental</span>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#cleanUpModal">Learn More</button>
                        </div>
                        <p class="text-muted small mb-1"><i class="fas fa-building fa-fw me-2"></i>GreenEarth Foundation</p>
                        <p class="text-muted small mb-1"><i class="fas fa-calendar-alt fa-fw me-2"></i>2025-10-25 | 8:00 AM – 12:00 PM</p>
                        <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt fa-fw me-2"></i>Central Park, City Center</p>
                        <p>Join us in cleaning and revitalizing our local parks this weekend.</p>
                    </div>
                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2 px-4 py-3">
                        <div class="contacts">
                            <span class="text-muted small me-3"><i class="fas fa-envelope fa-fw me-1"></i>volunteer@greenearth.org</span>
                            <span class="text-muted small"><i class="fas fa-phone fa-fw me-1"></i>+63 987 654 3210</span>
                        </div>
                        <span class="text-muted small fst-italic">Posted by AdminUser2 on October 21, 2025</span>
                    </div>
                </div>

                <div class="card opp-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold mb-1">Reading Program for Kids</h5>
                                <span class="badge bg-educational rounded-pill fw-medium">Educational</span>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#readingProgramModal">Learn More</button>
                        </div>
                        <p class="text-muted small mb-1"><i class="fas fa-building fa-fw me-2"></i>Literacy Champions</p>
                        <p class="text-muted small mb-1"><i class="fas fa-calendar-alt fa-fw me-2"></i>Every Tuesday & Thursday | 3:00 PM – 5:00 PM</p>
                        <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt fa-fw me-2"></i>Community Library, Downtown</p>
                        <p>Volunteer as a reading buddy to help children develop their literacy skills.</p>
                    </div>
                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2 px-4 py-3">
                        <div class="contacts">
                            <span class="text-muted small me-3"><i class="fas fa-envelope fa-fw me-1"></i>read@literacychampions.org</span>
                            <span class="text-muted small"><i class="fas fa-phone fa-fw me-1"></i>+63 912 345 6789</span>
                        </div>
                        <span class="text-muted small fst-italic">Posted by Education Coordinator on October 20, 2025</span>
                    </div>
                </div>

                <div class="card opp-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold mb-1">Food Bank Distribution</h5>
                                <span class="badge bg-community rounded-pill fw-medium">Community Service</span>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#foodBankModal">Learn More</button>
                        </div>
                        <p class="text-muted small mb-1"><i class="fas fa-building fa-fw me-2"></i>Hearts & Hands Charity</p>
                        <p class="text-muted small mb-1"><i class="fas fa-calendar-alt fa-fw me-2"></i>Every Saturday | 7:00 AM – 11:00 AM</p>
                        <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt fa-fw me-2"></i>Hearts & Hands Warehouse, Industrial Area</p>
                        <p>Help pack and distribute food supplies to families in need.</p>
                    </div>
                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2 px-4 py-3">
                        <div class="contacts">
                            <span class="text-muted small me-3"><i class="fas fa-envelope fa-fw me-1"></i>volunteer@heartsandhands.org</span>
                            <span class="text-muted small"><i class="fas fa-phone fa-fw me-1"></i>+63 918 234 5678</span>
                        </div>
                        <span class="text-muted small fst-italic">Posted by Operations Manager on October 18, 2025</span>
                    </div>
                </div>

                <div class="card opp-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="fw-bold mb-1">Animal Shelter Support</h5>
                                <span class="badge bg-community rounded-pill fw-medium">Community Service</span>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#animalShelterModal">Learn More</button>
                        </div>
                        <p class="text-muted small mb-1"><i class="fas fa-building fa-fw me-2"></i>Paws & Claws Rescue</p>
                        <p class="text-muted small mb-1"><i class="fas fa-calendar-alt fa-fw me-2"></i>Flexible Schedule | Min. 4 hours/week</p>
                        <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt fa-fw me-2"></i>Paws & Claws Shelter, West District</p>
                        <p>Spend time caring for rescued animals and helping with shelter operations.</p>
                    </div>
                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2 px-4 py-3">
                        <div class="contacts">
                            <span class="text-muted small me-3"><i class="fas fa-envelope fa-fw me-1"></i>info@pawsandclaws.org</span>
                            <span class="text-muted small"><i class="fas fa-phone fa-fw me-1"></i>+63 917 987 6543</span>
                        </div>
                        <span class="text-muted small fst-italic">Posted by Volunteer Coordinator on October 17, 2025</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

   <footer class="text-center">
        &copy; 2025 AgoraBoard — Volunteer Opportunities
    </footer>

    <div class="modal fade" id="readingProgramModal" tabindex="-1" aria-labelledby="readingProgramModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="readingProgramModalLabel">Reading Program for Kids</h5>
                        <span class="badge bg-educational rounded-pill fw-medium">Educational</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <p class="text-muted small mb-1"><i class="fas fa-building fa-fw me-2"></i>Literacy Champions</p>
                    <p class="text-muted small mb-1"><i class="fas fa-calendar-alt fa-fw me-2"></i>Every Tuesday & Thursday | 3:00 PM – 5:00 PM</p>
                    <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt fa-fw me-2"></i>Community Library, Downtown</p>
                    
                    <hr>

                    <h6 class="fw-bold mt-4 mb-2">About This Opportunity</h6>
                    <p>Make a difference in a child's life! Our reading program pairs volunteers with elementary students for one-on-one reading sessions. No teaching experience required – just a love for reading and patience. Sessions are held twice a week, and full training is provided. Help us inspire the next generation of readers!</p>

                    <h6 class="fw-bold mt-4 mb-3">Contact Information</h6>
                    <div class="contact-item d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-envelope fa-fw me-2 text-muted"></i>read@literacychampions.org</span>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard(this, 'read@literacychampions.org')">
                            <i class="fas fa-copy me-1"></i>Copy
                        </button>
                    </div>
                    <div class="contact-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-phone fa-fw me-2 text-muted"></i>+63 912 345 6789</span>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard(this, '+639123456789')">
                            <i class="fas fa-copy me-1"></i>Copy
                        </button>
                    </div>

                    <hr class="mt-4">
                    <p class="text-muted small text-end fst-italic mb-0">Posted by Education Coordinator on October 20, 2025</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="cleanUpModal" tabindex="-1" aria-labelledby="cleanUpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="cleanUpModalLabel">Community Clean-Up Drive</h5>
                        <span class="badge bg-environmental rounded-pill fw-medium">Environmental</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <p class="text-muted small mb-1"><i class="fas fa-building fa-fw me-2"></i>GreenEarth Foundation</p>
                    <p class="text-muted small mb-1"><i class="fas fa-calendar-alt fa-fw me-2"></i>2025-10-25 | 8:00 AM – 12:00 PM</p>
                    <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt fa-fw me-2"></i>Central Park, City Center</p>
                    
                    <hr>

                    <h6 class="fw-bold mt-4 mb-2">About This Opportunity</h6>
                    <p>Join us in cleaning and revitalizing our local parks this weekend. We'll provide gloves, bags, and all necessary equipment. This is a great way to get outdoors, meet your neighbors, and make a tangible impact on our local environment. All ages welcome!</p>

                    <h6 class="fw-bold mt-4 mb-3">Contact Information</h6>
                    <div class="contact-item d-flex justify-content-between align-items-center mb-2">
                        <span><i class="fas fa-envelope fa-fw me-2 text-muted"></i>volunteer@greenearth.org</span>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard(this, 'volunteer@greenearth.org')">
                            <i class="fas fa-copy me-1"></i>Copy
                        </button>
                    </div>
                    <div class="contact-item d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-phone fa-fw me-2 text-muted"></i>+63 987 654 3210</span>
                        <button class="btn btn-outline-secondary btn-sm" onclick="copyToClipboard(this, '+639876543210')">
                            <i class="fas fa-copy me-1"></i>Copy
                        </button>
                    </div>

                    <hr class="mt-4">
                    <p class="text-muted small text-end fst-italic mb-0">Posted by AdminUser2 on October 21, 2025</p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="foodBankModal" tabindex="-1" aria-labelledby="foodBankModalLabel" aria-hidden="true">
        </div>

    <div class="modal fade" id="animalShelterModal" tabindex="-1" aria-labelledby="animalShelterModalLabel" aria-hidden="true">
        </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
        function copyToClipboard(element, text) {
            navigator.clipboard.writeText(text).then(function() {
                // Success!
                const originalHtml = element.innerHTML;
                element.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
                element.disabled = true;
                
                // Revert back after 2 seconds
                setTimeout(function() {
                    element.innerHTML = originalHtml;
                    element.disabled = false;
                }, 2000);
            }, function(err) {
                // Error
                console.error('Could not copy text: ', err);
                alert('Failed to copy.');
            });
        }
    </script>
</body>
</html>