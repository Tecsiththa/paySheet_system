<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Paysheet Management System</title>
    <link rel="stylesheet" href="accests/css/style.css">
    <link rel="stylesheet" href="accests/css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .footer {
            background: #05122a;
            color: #f2f4f7;
            padding: 60px 20px;
            margin-top: 100px;
        }
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            align-items: flex-start;
        }
        .footer-section h3 {
            font-size: 24px;
            margin-bottom: 16px;
            color: #ffffff;
        }
        .footer-section p,
        .footer-section li {
            color: rgba(255, 255, 255, 0.78);
            font-size: 16px;
            line-height: 1.6;
        }
        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-section ul li {
            margin-bottom: 10px;
        }
        .footer-section ul li a {
            color: rgba(255, 255, 255, 0.78);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .footer-section ul li a:hover {
            color: #00a4ff;
        }
        .footer-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.18);
            margin: 38px 0;
        }
        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            color: rgba(255, 255, 255, 0.68);
        }
        .footer-social {
            display: flex;
            gap: 14px;
        }
        .footer-social a {
            color: rgba(255, 255, 255, 0.78);
            text-decoration: none;
            font-size: 20px;
        }
        @media(max-width: 900px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media(max-width: 640px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }
            .footer-bottom {
                justify-content: center;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-background">
        <div class="gradient-circle circle-1"></div>
        <div class="gradient-circle circle-2"></div>
        <div class="gradient-circle circle-3"></div>
    </div>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <span class="logo-icon">💼</span>
                <span class="logo-text">PaySheet<span class="highlight">Pro</span></span>
            </div>
            <div class="nav-links">
                <a href="#features" class="nav-link">Features</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="contact.php" class="nav-link">Contact</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <div class="badge">🚀 Smart Payroll Solution</div>
                <h1 class="hero-title">
                    Manage Your<br>
                    <span class="gradient-text">Employee Paysheets</span><br>
                    Effortlessly
                </h1>
                <p class="hero-description">
                    Automated salary calculations, leave management, and paysheet generation 
                    all in one powerful platform. Built for Sri Lankan businesses.
                </p>
                <div class="hero-buttons">
                    <a href="auth/company_register.php" class="btn-primary btn-large">
                        <span>Register Your Company</span>
                        <span class="btn-icon">→</span>
                    </a>
                    <a href="auth/login.php" class="btn-secondary btn-large">
                        <span>Login to Dashboard</span>
                    </a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number">100%</div>
                        <div class="stat-label">Automated</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Access</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">Secure</div>
                        <div class="stat-label">Data Protected</div>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="floating-card card-1">
                    <div class="card-icon">📊</div>
                    <div class="card-title">Paysheet Generated</div>
                    <div class="card-value">LKR 2,450,000</div>
                </div>
                <div class="floating-card card-2">
                    <div class="card-icon">👥</div>
                    <div class="card-title">Active Employees</div>
                    <div class="card-value">150+</div>
                </div>
                <div class="floating-card card-3">
                    <div class="card-icon">✅</div>
                    <div class="card-title">Leave Approved</div>
                    <div class="card-value">12 Today</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="section-container">
            <div class="section-header">
                <h2 class="section-title">Powerful Features</h2>
                <p class="section-subtitle">Everything you need to manage employee paysheets</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon gradient-1">💰</div>
                    <h3 class="feature-title">Automated Salary Calculation</h3>
                    <p class="feature-description">EPF, ETF, APIT tax, OT payments automatically calculated</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon gradient-2">📅</div>
                    <h3 class="feature-title">Leave Management</h3>
                    <p class="feature-description">Annual, Casual, Sick leave tracking with auto-deductions</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon gradient-3">📄</div>
                    <h3 class="feature-title">PDF Paysheets</h3>
                    <p class="feature-description">Professional paysheets with detailed breakdowns</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon gradient-4">💳</div>
                    <h3 class="feature-title">Loan Management</h3>
                    <p class="feature-description">Track employee loans and monthly deductions</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon gradient-5">⚡</div>
                    <h3 class="feature-title">Salary Advances</h3>
                    <p class="feature-description">Record and deduct salary advances automatically</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon gradient-6">📊</div>
                    <h3 class="feature-title">Detailed Reports</h3>
                    <p class="feature-description">Monthly and annual salary reports at your fingertips</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-container">
            <h2 class="cta-title">Ready to Get Started?</h2>
            <p class="cta-description">Join hundreds of companies managing paysheets efficiently</p>
            <a href="auth/company_register.php" class="btn-cta">
                <span>Register Your Company Now</span>
                <span class="btn-icon">→</span>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>About PaySheetPro</h3>
                    <p style="color: rgba(255, 255, 255, 0.7); line-height: 1.6; margin-top: 10px;">
                        Revolutionizing payroll management with cutting-edge automation and intelligent solutions for modern businesses.
                    </p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="margin-bottom: 12px;"><a href="index.php" style="color: rgba(255, 255, 255, 0.7); text-decoration: none;">Home</a></li>
                        <li style="margin-bottom: 12px;"><a href="about.php" style="color: rgba(255, 255, 255, 0.7); text-decoration: none;">About Us</a></li>
                        <li style="margin-bottom: 12px;"><a href="#features" style="color: rgba(255, 255, 255, 0.7); text-decoration: none;">Features</a></li>
                        <li style="margin-bottom: 12px;"><a href="auth/company_register.php" style="color: rgba(255, 255, 255, 0.7); text-decoration: none;">Register</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Features</h3>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="margin-bottom: 12px;"><a href="#features" style="color: rgba(255, 255, 255, 0.7); text-decoration: none;">Automated Payroll</a></li>
                        <li style="margin-bottom: 12px;"><a href="#features" style="color: rgba(255, 255, 255, 0.7); text-decoration: none;">Leave Management</a></li>
                        <li style="margin-bottom: 12px;"><a href="#features" style="color: rgba(255, 255, 255, 0.7); text-decoration: none;">Reports</a></li>
                        <li style="margin-bottom: 12px;"><a href="#features" style="color: rgba(255, 255, 255, 0.7); text-decoration: none;">Loan Tracking</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <ul style="list-style: none; padding: 0; margin: 0;">
                        <li style="margin-bottom: 8px; color: rgba(255, 255, 255, 0.7);">📧 info@paysheetpro.com</li>
                        <li style="margin-bottom: 8px; color: rgba(255, 255, 255, 0.7);">📞 +94 70 123 4567</li>
                        <li style="margin-bottom: 8px; color: rgba(255, 255, 255, 0.7);">📍 Colombo, Sri Lanka</li>
                    </ul>
                </div>
            </div>

            <div class="footer-divider"></div>
            <div class="footer-bottom">
                
                
            </div>
        </div>
    </footer>

    <script src="accests/js/main.js"></script>
</body>
</html>