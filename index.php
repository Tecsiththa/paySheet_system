<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Paysheet Management System</title>
    <link rel="stylesheet" href="accests/css/style.css">
    <link rel="stylesheet" href="accests/css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <a href="auth/login.php" class="btn-nav-login">Login</a>
                <a href="auth/company_register.php" class="btn-nav-register">Get Started</a>
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
            <p>&copy; 2024 PaySheetPro. All rights reserved.</p>
        </div>
    </footer>

    <script src="accests/js/main.js"></script>
</body>
</html>