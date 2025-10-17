
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgoraBoard - Community Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --emerald-50: #ecfdf5;
            --emerald-100: #d1fae5;
            --emerald-500: #10b981;
            --emerald-600: #059669;
            --emerald-700: #047857;
            --blue-500: #3b82f6;
            --blue-600: #2563eb;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, var(--emerald-600), var(--emerald-700));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gradient-text-hero {
            background: linear-gradient(135deg, var(--emerald-600), var(--emerald-500), var(--blue-600));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .btn-emerald {
            background: linear-gradient(135deg, var(--emerald-600), var(--emerald-700));
            border: none;
            color: white;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.25);
        }
        
        .btn-emerald:hover {
            background: linear-gradient(135deg, var(--emerald-700), var(--emerald-600));
            color: white;
            transform: translateY(-2px);
        }
        
        .feature-card {
            transition: all 0.3s ease;
            border: none;
            background: linear-gradient(135deg, #ffffff, rgba(236, 253, 245, 0.3));
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.1);
        }
        
        .feature-icon {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover .feature-icon {
            transform: scale(1.1);
        }
        
        .bg-emerald { background: linear-gradient(135deg, var(--emerald-500), var(--emerald-600)); }
        .bg-blue { background: linear-gradient(135deg, var(--blue-500), var(--blue-600)); }
        .bg-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .bg-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
        .bg-teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }
        .bg-rose { background: linear-gradient(135deg, #f43f5e, #e11d48); }
        
        .hero-bg {
            background: linear-gradient(135deg, rgba(236, 253, 245, 0.5), transparent, rgba(219, 234, 254, 0.3));
            position: relative;
            overflow: hidden;
        }
        
        .hero-bg::before {
            content: '';
            position: absolute;
            top: 80px;
            left: 40px;
            width: 288px;
            height: 288px;
            background: rgba(167, 243, 208, 0.2);
            border-radius: 50%;
            filter: blur(60px);
        }
        
        .hero-bg::after {
            content: '';
            position: absolute;
            bottom: 80px;
            right: 40px;
            width: 384px;
            height: 384px;
            background: rgba(147, 197, 253, 0.2);
            border-radius: 50%;
            filter: blur(60px);
        }
        
        .backdrop-blur {
            backdrop-filter: blur(12px);
            background-color: rgba(255, 255, 255, 0.8);
        }
        
        .badge-emerald {
            background-color: var(--emerald-100);
            color: var(--emerald-700);
            border: 1px solid rgba(167, 243, 208, 0.5);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <nav class="navbar navbar-expand-lg sticky-top backdrop-blur border-bottom">
        <div class="container">
            <div class="navbar-brand d-flex align-items-center">
                <div class="position-relative me-3">
                    <div class="feature-icon bg-emerald" style="width: 40px; height: 40px; margin-bottom: 0;">
                        <i class="fas fa-users" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <div>
                    <h1 class="h4 mb-0 gradient-text fw-bold">AgoraBoard</h1>
                    <small class="text-muted">Community Hub</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="login.php" class="btn btn-outline-success">Login</a>
                <a href="register.php" class="btn btn-emerald">Register</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-bg py-5" style="padding: 5rem 0;">
        <div class="container text-center position-relative" style="z-index: 10;">
            <div class="mb-4">
                <span class="badge badge-emerald px-3 py-2">
                    <i class="fas fa-sparkles me-2"></i>Community Hub
                </span>
            </div>
            
            <h2 class="display-1 fw-bold mb-4 lh-1">
                Welcome to Your 
                <span class="gradient-text-hero">Community's</span> 
                Digital Bulletin Board
            </h2>
            
            <p class="lead fs-4 text-muted mb-5 mx-auto" style="max-width: 48rem;">
                Stay connected with your neighbors, discover local events, and share important announcements in one
                centralized, always-accessible platform.
            </p>
            
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a href="register.php" class="btn btn-emerald btn-lg px-4 py-3">
                    Join Our Community
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
                <a href="browse.php" class="btn btn-outline-success btn-lg px-4 py-3">
                    Explore Announcements
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5" style="background: linear-gradient(to bottom, #ffffff, rgba(249, 250, 251, 0.3)); padding: 5rem 0;">
        <div class="container">
            <div class="text-center mb-5">
                <h3 class="display-4 fw-bold mb-4">
                    Everything Your 
                    <span class="gradient-text-hero">Community</span> 
                    Needs
                </h3>
                <p class="lead text-muted mx-auto" style="max-width: 48rem;">
                    From safety alerts to volunteer opportunities, AgoraBoard keeps your community informed and engaged.
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-emerald">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <h5 class="card-title">Community Events</h5>
                            <p class="card-text">
                                Discover local events, festivals, and gatherings happening in your neighborhood.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-blue">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h5 class="card-title">Safety Alerts</h5>
                            <p class="card-text">
                                Stay informed about important safety updates and public announcements from local authorities.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-purple">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5 class="card-title">Volunteer Opportunities</h5>
                            <p class="card-text">
                                Find ways to give back to your community and connect with local organizations.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-orange">
                                <i class="fas fa-search"></i>
                            </div>
                            <h5 class="card-title">Lost & Found</h5>
                            <p class="card-text">
                                Help reunite community members with their lost items and pets.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-teal">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h5 class="card-title">Community Discussions</h5>
                            <p class="card-text">
                                Engage in constructive conversations and provide feedback on local announcements.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="card feature-card h-100">
                        <div class="card-body p-4">
                            <div class="feature-icon bg-rose">
                                <i class="fas fa-thumbtack"></i>
                            </div>
                            <h5 class="card-title">Priority Announcements</h5>
                            <p class="card-text">
                                Never miss urgent updates with our pinned announcement system for critical information.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(59, 130, 246, 0.05), rgba(139, 92, 246, 0.05)); padding: 5rem 0;">
        <div class="container text-center position-relative" style="z-index: 10;">
            <h3 class="display-4 fw-bold mb-4">
                Ready to Connect with Your 
                <span class="gradient-text-hero">Community?</span>
            </h3>
            <p class="lead text-muted mb-5 mx-auto" style="max-width: 48rem;">
                Join thousands of residents who stay informed and engaged through AgoraBoard. Create your account today and
                start participating in your local community.
            </p>
            <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
                <a href="register.php" class="btn btn-emerald btn-lg px-4 py-3">
                    Create Account
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
                <a href="login.php" class="btn btn-outline-success btn-lg px-4 py-3">
                    Sign In
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-top py-5" style="background: linear-gradient(to bottom, rgba(249, 250, 251, 0.3), rgba(249, 250, 251, 0.5));">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="feature-icon bg-emerald me-2" style="width: 24px; height: 24px; margin-bottom: 0;">
                            <i class="fas fa-users" style="font-size: 1rem;"></i>
                        </div>
                        <span class="fw-semibold">AgoraBoard</span>
                    </div>
                    <p class="text-muted small">
                        Connecting communities through digital communication and shared information.
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-semibold mb-3">Community</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="guidelines.php" class="text-muted text-decoration-none small">Community Guidelines</a></li>
                        <li class="mb-2"><a href="privacy.php" class="text-muted text-decoration-none small">Privacy Policy</a></li>
                        <li class="mb-2"><a href="terms.php" class="text-muted text-decoration-none small">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="fw-semibold mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="help.php" class="text-muted text-decoration-none small">Help Center</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-muted text-decoration-none small">Contact Us</a></li>
                        <li class="mb-2"><a href="feedback.php" class="text-muted text-decoration-none small">Send Feedback</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="text-muted small mb-0">&copy; 2024 AgoraBoard. Built for communities, by communities.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
<<<<<<< HEAD
</html>
=======
</html>
>>>>>>> c2d31a1 (Initial commit of agora-ui folder)
