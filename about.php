<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - PaySheetPro</title>
    <link rel="stylesheet" href="accests/css/style.css">
    <link rel="stylesheet" href="accests/css/landing.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .about-hero {
            background: var(--gradient-primary);
            color: white;
            padding: 100px 20px;
            text-align: center;
        }

        .about-hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .about-hero p {
            font-size: 20px;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.9;
            color: #000;
        }

        .about-content {
            max-width: 1200px;
            margin: 80px auto;
            padding: 0 20px;
        }

        .about-section {
            margin-bottom: 80px;
        }

        .about-section h2 {
            font-size: 36px;
            margin-bottom: 20px;
            color: var(--text-primary);
        }

        .about-section p {
            font-size: 18px;
            line-height: 1.8;
            color: var(--text-secondary);
            margin-bottom: 15px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            margin-top: 40px;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            text-align: center;
            transition: transform var(--transition-normal);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-xl);
        }

        .feature-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .feature-card p {
            font-size: 16px;
            color: var(--text-secondary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 30px;
            margin: 60px 0;
        }

        .stat-box {
            background: var(--gradient-primary);
            color: white;
            padding: 40px;
            border-radius: var(--border-radius-lg);
            text-align: center;
        }

        .stat-number {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 16px;
            opacity: 0.9;
        }

        .team-section {
            background: var(--gray-50);
            padding: 80px 20px;
            margin-top: 80px;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
            max-width: 1200px;
            margin: 40px auto 0;
            justify-items: center;
            align-items: center;
        }

        .team-member {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius-lg);
            text-align: center;
            box-shadow: var(--shadow-sm);
            max-width: 300px;
        }

        .team-avatar {
            width: 120px;
            height: 120px;
            background: var(--gradient-primary);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
        }

        .team-name {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-primary);
        }

        .team-role {
            font-size: 16px;
            color: var(--text-secondary);
            margin-bottom: 15px;
        }

        .team-bio {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .features-grid,
            .stats-grid,
            .team-grid {
                grid-template-columns: 1fr;
            }

            .about-hero h1 {
                font-size: 32px;
            }
        }

        /* Navigation Bar */
        nav.navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-menu {
            display: flex;
            list-style: none;
            gap: 40px;
            margin: 0;
            padding: 0;
        }

        .navbar-menu a {
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 500;
            transition: color var(--transition-normal);
        }

        .navbar-menu a:hover,
        .navbar-menu a.active {
            color: var(--primary);
        }

        .navbar-actions {
            display: flex;
            gap: 15px;
        }

        .navbar-actions .btn-login {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            padding: 8px 20px;
            border-radius: var(--border-radius-md);
            text-decoration: none;
            font-weight: 600;
            transition: all var(--transition-normal);
        }

        .navbar-actions .btn-login:hover {
            background: var(--primary);
            color: white;
        }

        .navbar-actions .btn-register {
            background: var(--gradient-primary);
            color: white;
            padding: 10px 25px;
            border-radius: var(--border-radius-md);
            text-decoration: none;
            font-weight: 600;
            transition: transform var(--transition-normal);
        }

        .navbar-actions .btn-register:hover {
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .navbar-menu {
                gap: 20px;
            }

            .navbar-actions {
                flex-direction: column;
                gap: 10px;
            }
        }

        /* Footer */
        footer {
            background: var(--text-primary);
            color: white;
            padding: 60px 20px 20px;
            margin-top: 100px;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: white;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-section ul li {
            margin-bottom: 12px;
        }

        .footer-section ul li a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: color var(--transition-normal);
        }

        .footer-section ul li a:hover {
            color: white;
        }

        .footer-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin: 40px 0;
            padding-top: 20px;
        }

        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .footer-copyright {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }

        .footer-social {
            display: flex;
            gap: 20px;
        }

        .footer-social a {
            color: rgba(255, 255, 255, 0.7);
            font-size: 20px;
            text-decoration: none;
            transition: color var(--transition-normal);
        }

        .footer-social a:hover {
            color: white;
        }

        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 30px;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }
        }
    
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
                <span>💼</span>
                <span>PaySheet<span style="color: var(--primary);">Pro</span></span>
            </a>
            <ul class="navbar-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php" class="active">About</a></li>
                
                <li><a href="contact.php">Contact</a></li>
            </ul>
            
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="about-hero">
        <h1>About PaySheetPro</h1>
        <p>Revolutionizing payroll management for modern businesses with cutting-edge automation and intelligent solutions</p>
    </div>

    <!-- Main Content -->
    <div class="about-content">

        <!-- Mission Section -->
        <div class="about-section">
            <h2>Our Mission</h2>
            <p>
                At PaySheetPro, we're on a mission to simplify payroll management for businesses of all sizes. 
                We believe that managing employee salaries, leaves, and benefits shouldn't be complicated or time-consuming.
            </p>
            <p>
                Our platform combines powerful automation with an intuitive interface to help HR teams and business 
                owners focus on what matters most - growing their business and taking care of their employees.
            </p>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number">100%</div>
                <div class="stat-label">Automated Calculations</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">24/7</div>
                <div class="stat-label">System Availability</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">99.9%</div>
                <div class="stat-label">Accuracy Rate</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">1000+</div>
                <div class="stat-label">Happy Users</div>
            </div>
        </div>

        <!-- Features -->
        <div class="about-section">
            <h2>What We Offer</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">💰</div>
                    <h3>Automated Payroll</h3>
                    <p>Calculate salaries with EPF, ETF, and APIT tax automatically</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📅</div>
                    <h3>Leave Management</h3>
                    <p>Track annual, casual, and sick leaves effortlessly</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Detailed Reports</h3>
                    <p>Generate monthly and annual reports with insights</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <h3>Loan Tracking</h3>
                    <p>Manage employee loans and salary advances</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔒</div>
                    <h3>Secure & Safe</h3>
                    <p>Your data is protected with industry-standard security</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <h3>Mobile Friendly</h3>
                    <p>Access from anywhere, on any device</p>
                </div>
            </div>
        </div>

        <!-- Why Choose Us -->
        <div class="about-section">
            <h2>Why Choose PaySheetPro?</h2>
            <p>
                <strong>Easy to Use:</strong> Our intuitive interface makes it simple for anyone to manage payroll, 
                even without technical expertise.
            </p>
            <p>
                <strong>Compliant:</strong> Built-in compliance with Sri Lankan labor laws, EPF, ETF, and APIT regulations.
            </p>
            <p>
                <strong>Time-Saving:</strong> Automate repetitive tasks and reduce payroll processing time by up to 90%.
            </p>
            <p>
                <strong>Accurate:</strong> Eliminate human errors with our automated calculation engine.
            </p>
            <p>
                <strong>Scalable:</strong> Whether you have 10 employees or 1000, our system grows with your business.
            </p>
        </div>

    </div>

    <!-- Team Section -->
    <div class="team-section">
        <div class="about-content">
            <h2 style="text-align: center; margin-bottom: 20px;">Meet Our Team</h2>
            <p style="text-align: center; color: var(--text-secondary); margin-bottom: 40px;">
                Passionate professionals dedicated to making payroll management simple
            </p>
            <div class="team-grid">
                <div class="team-member">
                    <div class="team-avatar">👨‍💼</div>
                    <div class="team-name">Niwantha Dilshan</div>
                    <div class="team-role">Founder & CEO</div>
                    <p class="team-bio">5+ years experience in It industry</p>
                </div>
                <div class="team-member">
                    <div class="team-avatar">👩‍💻</div>
                    <div class="team-name">sithum wijethunga</div>
                    <div class="team-role">Lead Developer</div>
                    <p class="team-bio">Expert in building scalable web applications</p>
                </div>
               
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3>About PaySheetPro</h3>
                    <p style="color: rgba(255, 255, 255, 0.7); line-height: 1.6;">
                        Revolutionizing payroll management with cutting-edge automation and intelligent solutions for modern businesses.
                    </p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="auth/company_register.php">Register</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Features</h3>
                    <ul>
                        <li><a href="index.php#features">Automated Payroll</a></li>
                        <li><a href="index.php#features">Leave Management</a></li>
                        <li><a href="index.php#features">Reports</a></li>
                        <li><a href="index.php#features">Loan Tracking</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <ul>
                        <li style="margin-bottom: 8px;">📧 info@paysheetpro.com</li>
                        <li style="margin-bottom: 8px;">📞 +94 70 123 4567</li>
                        <li style="margin-bottom: 8px;">📍 Colombo, Sri Lanka</li>
                    </ul>
                </div>
            </div>

            <div class="footer-divider"></div>

           
        </div>
    </footer>

</body>
</html>