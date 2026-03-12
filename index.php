<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="PaySheet Pro — The modern employee payroll management system for Sri Lankan companies. Automate EPF, ETF, APIT, OT, leave, and payslip generation.">
  <title>PaySheet Pro — Smart Payroll for Sri Lankan Companies</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://api.fontshare.com">
  <link href="https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600,700&f[]=satoshi@400,500,700&display=swap" rel="stylesheet">

  <!-- Stylesheet -->
  <link rel="stylesheet" href="assets/css/home.css">
</head>
<body>

<!-- ── Background ── -->
<div class="bg-canvas">
  <div class="bg-orb bg-orb-1"></div>
  <div class="bg-orb bg-orb-2"></div>
  <div class="bg-orb bg-orb-3"></div>
</div>
<div class="bg-grid"></div>

<!-- ============================================================
     NAVBAR
     ============================================================ -->
<nav class="navbar" id="navbar" role="navigation" aria-label="Main navigation">
  <a href="index.php" class="nav-logo" aria-label="PaySheet Pro Home">
    <div class="nav-logo-icon">₨</div>
    <div class="nav-logo-text">PaySheet <span>Pro</span></div>
  </a>

  <ul class="nav-links" role="list">
    <li><a href="#features">Features</a></li>
    <li><a href="#how-it-works">How It Works</a></li>
 
   
  </ul>

  <div class="nav-cta">
    <a href="index.php?page=login" class="btn-nav-outline">Sign In</a>
    <a href="register.php?page=register" class="btn-nav-primary">Sign Up</a>
  </div>

  <button class="hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false">
    <span></span><span></span><span></span>
  </button>
</nav>

<!-- Mobile Menu -->
<div id="mobileMenu" style="display:none; position:fixed; top:70px; left:0; right:0; z-index:999; background:rgba(6,13,31,0.97); backdrop-filter:blur(20px); border-bottom:1px solid #1a2d52; padding:20px 5%; flex-direction:column; gap:8px;">
  <a href="#features" style="color:#8ca8d4; text-decoration:none; font-size:15px; padding:10px 0; border-bottom:1px solid #1a2d52; display:block;">Features</a>
  <a href="#how-it-works" style="color:#8ca8d4; text-decoration:none; font-size:15px; padding:10px 0; border-bottom:1px solid #1a2d52; display:block;">How It Works</a>
  <a href="#showcase" style="color:#8ca8d4; text-decoration:none; font-size:15px; padding:10px 0; border-bottom:1px solid #1a2d52; display:block;">Paysheet</a>
  <a href="#pricing" style="color:#8ca8d4; text-decoration:none; font-size:15px; padding:10px 0; border-bottom:1px solid #1a2d52; display:block;">Pricing</a>
  <div style="display:flex; gap:10px; padding-top:14px;">
    <a href="index.php?page=login" style="flex:1; text-align:center; padding:11px; border-radius:9px; border:1px solid #1a2d52; color:#8ca8d4; text-decoration:none; font-size:14px; font-weight:600;">Sign In</a>
    <a href="index.php?page=register" style="flex:1; text-align:center; padding:11px; border-radius:9px; background:linear-gradient(135deg,#00e5b0,#00c49a); color:#060d1f; text-decoration:none; font-size:14px; font-weight:700;">Get Started</a>
  </div>
</div>

<script>
  // Inline mobile menu toggle (before JS loads)
  document.getElementById('hamburger').addEventListener('click', function() {
    const m = document.getElementById('mobileMenu');
    m.style.display = m.style.display === 'flex' ? 'none' : 'flex';
  });
</script>

<!-- ============================================================
     HERO
     ============================================================ -->
<section class="hero" id="home">
  <div>
    <div class="hero-badge">
      <span class="hero-badge-dot"></span>
      Sri Lanka's #1 Payroll System — Fully Automated
    </div>

    <h1 class="hero-title" id="heroTitle">
      Payroll Made<br>
      <span class="line-teal">Effortless.</span>
      <br>
      Payslips Made<br>
      <span class="line-gold">Instant.</span>
    </h1>

    <p class="hero-sub">
      Register your company once. Manage employees, calculate EPF/ETF/APIT, track leaves, handle OT — and generate professional payslip PDFs in one click.
    </p>

    <div class="hero-actions">
      <a href="register.php?page=register" class="btn-hero-primary">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
        Start Now
      </a>
      <a href="#features" class="btn-hero-secondary">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
        See Features
      </a>
    </div>

    <div class="hero-stats">
      <div class="hero-stat">
        <div class="hero-stat-value" data-target="500" data-suffix="+" >0+</div>
        <div class="hero-stat-label">Companies Registered</div>
      </div>
      <div class="hero-stat-divider"></div>
      <div class="hero-stat">
        <div class="hero-stat-value" data-target="12000" data-suffix="+">0+</div>
        <div class="hero-stat-label">Payslips Generated</div>
      </div>
      <div class="hero-stat-divider"></div>
      <div class="hero-stat">
        <div class="hero-stat-value" data-target="99.9" data-suffix="%" data-decimals="1">0%</div>
        <div class="hero-stat-label">Calculation Accuracy</div>
      </div>
      <div class="hero-stat-divider"></div>
      <div class="hero-stat">
        <div class="hero-stat-value" data-target="18">0</div>
        <div class="hero-stat-label">System Interfaces</div>
      </div>
    </div>

    <!-- Mockup -->
    <div class="hero-mockup">
      <div class="mockup-float mockup-float-1">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        EPF & ETF Auto-Calculated
      </div>
      <div class="mockup-float mockup-float-2">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16h12a2 2 0 0 0 2-2V8z"/></svg>
        PDF Payslip Ready
      </div>
      <div class="mockup-float mockup-float-3">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Leave Tracked
      </div>

      <div class="mockup-card">
        <div class="mockup-header">
          <span class="mockup-company">Acme Technologies (Pvt) Ltd.</span>
          <span class="mockup-month">March 2025</span>
        </div>
        <div class="mockup-emp">
          <div class="mockup-emp-name">Kamal Perera</div>
          <div class="mockup-emp-id">EMP-004 · Senior Software Engineer</div>
        </div>
        <div class="mockup-rows">
          <div class="mockup-row earn"><span>Basic Salary</span><span class="val">LKR 85,000.00</span></div>
          <div class="mockup-row earn"><span>Travel Allowance</span><span class="val">LKR 5,000.00</span></div>
          <div class="mockup-row earn"><span>OT Payment (12 hrs)</span><span class="val">LKR 7,969.00</span></div>
          <div class="mockup-row deduct"><span>EPF (Employee 8%)</span><span class="val">– LKR 6,800.00</span></div>
          <div class="mockup-row deduct"><span>APIT Tax</span><span class="val">– LKR 2,220.00</span></div>
          <div class="mockup-row deduct"><span>Loan Installment</span><span class="val">– LKR 5,000.00</span></div>
        </div>
        <div class="mockup-net">
          <span class="mockup-net-label">Net Salary</span>
          <span class="mockup-net-value">LKR 83,949.00</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     FEATURES
     ============================================================ -->
<section class="features" id="features">
  <div class="features-header reveal">
   
  
    <h2 class="section-title">Built for Sri Lankan<br>Payroll Compliance</h2>
    <p class="section-sub">Every calculation, every deduction, every PDF — exactly as required by Sri Lankan labor law and IRD regulations.</p>
  </div>

  <div class="features-grid">
    <!-- Card 1 -->
    <div class="feature-card reveal reveal-delay-1">
      <div class="feature-icon fi-teal">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#00e5b0" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="feature-title">Employee Management</div>
      <div class="feature-desc">Register unlimited employees with salary details, allowances, department, designation and full employment history tracked automatically.</div>
      <span class="feature-tag ft-teal">Unlimited Employees</span>
    </div>

    <!-- Card 2 -->
    <div class="feature-card reveal reveal-delay-2">
      <div class="feature-icon fi-blue">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4f8eff" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <div class="feature-title">Smart Leave Management</div>
      <div class="feature-desc">Track Annual (14 days), Casual (7 days) and Sick Leave (7 days) separately. Unapproved leave is automatically deducted from salary at daily rate.</div>
      <span class="feature-tag ft-blue">3 Leave Types + Auto-Deduction</span>
    </div>

    <!-- Card 3 -->
    <div class="feature-card reveal reveal-delay-3">
      <div class="feature-icon fi-gold">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f5c542" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
      <div class="feature-title">OT Calculation</div>
      <div class="feature-desc">Overtime calculated precisely: Hourly Rate = Salary ÷ 240, then OT Rate = Hourly × 1.5. Real-time preview as you enter hours.</div>
      <span class="feature-tag ft-gold">×1.5 OT Rate Formula</span>
    </div>

    <!-- Card 4 -->
    <div class="feature-card reveal reveal-delay-1">
      <div class="feature-icon fi-teal">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#00e5b0" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      </div>
      <div class="feature-title">EPF &amp; ETF Compliant</div>
      <div class="feature-desc">Automatically computes EPF Employee (8%), EPF Employer (12%) and ETF (3%) contributions every month on the basic salary.</div>
      <span class="feature-tag ft-teal">IRD Compliant</span>
    </div>

    <!-- Card 5 -->
    <div class="feature-card reveal reveal-delay-2">
      <div class="feature-icon fi-red">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      </div>
      <div class="feature-title">APIT Tax (7 Brackets)</div>
      <div class="feature-desc">Full progressive APIT tax calculation from 0% to 36% across all 7 income brackets as per Sri Lanka Inland Revenue. Full breakdown in every payslip.</div>
      <span class="feature-tag ft-red">0% – 36% Progressive Tax</span>
    </div>

    <!-- Card 6 -->
    <div class="feature-card reveal reveal-delay-3">
      <div class="feature-icon fi-purple">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      </div>
      <div class="feature-title">Loan &amp; Advance Tracking</div>
      <div class="feature-desc">Record company loans with monthly installments and salary advances. Both are automatically deducted from the employee's monthly net salary.</div>
      <span class="feature-tag ft-purple">Auto Monthly Deduction</span>
    </div>

    <!-- Card 7 -->
    <div class="feature-card reveal reveal-delay-1">
      <div class="feature-icon fi-green">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      </div>
      <div class="feature-title">Professional PDF Payslips</div>
      <div class="feature-desc">One-click PDF payslip generation with company logo, employee details, full salary breakdown, APIT brackets, leave records and signature fields.</div>
      <span class="feature-tag ft-green">Print or Download</span>
    </div>

    <!-- Card 8 -->
    <div class="feature-card reveal reveal-delay-2">
      <div class="feature-icon fi-teal">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#00e5b0" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      </div>
      <div class="feature-title">Company Dashboard</div>
      <div class="feature-desc">Instant overview of active employees, pending leave requests, active loans and this month's paysheet count — all on one screen.</div>
      <span class="feature-tag ft-teal">Real-time Overview</span>
    </div>

    <!-- Card 9 -->
    <div class="feature-card reveal reveal-delay-3">
      <div class="feature-icon fi-blue">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#4f8eff" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div class="feature-title">Multi-Company Secure</div>
      <div class="feature-desc">Each company has its own isolated data, secure login, and full data ownership. Register multiple companies under separate accounts.</div>
      <span class="feature-tag ft-blue">Data Isolated Per Company</span>
    </div>
  </div>
</section>

<!-- ============================================================
     HOW IT WORKS
     ============================================================ -->
<section class="how-it-works" id="how-it-works">
  <div class="how-inner">
    <div>
      <div class="reveal">
        <span class="section-label">Simple Process</span>
        <h2 class="section-title">From Register to<br>Payslip in Minutes</h2>
        <p class="section-sub" style="margin-bottom:40px;">No complex setup. No accountant needed. Just follow these steps every month.</p>
      </div>

      <ol class="steps-list">
        <li class="step reveal">
          <div class="step-num">1</div>
          <div class="step-content">
            <div class="step-title">Register Your Company</div>
            <div class="step-desc">Create your company account in under 2 minutes. Fill in company name, registration number, email and set your password.</div>
          </div>
        </li>
        <li class="step reveal">
          <div class="step-num">2</div>
          <div class="step-content">
            <div class="step-title">Add Your Employees</div>
            <div class="step-desc">Register each employee with their basic salary, allowances, department and designation. Leave balances are created automatically.</div>
          </div>
        </li>
        <li class="step reveal">
          <div class="step-num">3</div>
          <div class="step-content">
            <div class="step-title">Record Monthly Data</div>
            <div class="step-desc">Enter overtime hours, approve leave requests, record any loans or salary advances for the month.</div>
          </div>
        </li>
        <li class="step reveal">
          <div class="step-num">4</div>
          <div class="step-content">
            <div class="step-title">Generate &amp; Download</div>
            <div class="step-desc">Click Generate Paysheet — the system calculates everything automatically. Download a professional PDF payslip for each employee instantly.</div>
          </div>
        </li>
      </ol>
    </div>

    <!-- Visual salary breakdown -->
    <div class="how-visual reveal">
      <div class="vis-title">Salary Breakdown Preview</div>

      <div class="vis-bar-group">
        <div class="vis-bar-label"><span>Basic Salary</span><span>LKR 85,000</span></div>
        <div class="vis-bar-track"><div class="vis-bar-fill vb-teal" style="width:100%"></div></div>
      </div>

      <div class="vis-bar-group">
        <div class="vis-bar-label"><span>Allowances (Travel + Food)</span><span>LKR 8,000</span></div>
        <div class="vis-bar-track"><div class="vis-bar-fill vb-blue" style="width:9%"></div></div>
      </div>

      <div class="vis-bar-group">
        <div class="vis-bar-label"><span>OT Payment</span><span>LKR 7,969</span></div>
        <div class="vis-bar-track"><div class="vis-bar-fill vb-gold" style="width:9%"></div></div>
      </div>

      <div class="vis-bar-group">
        <div class="vis-bar-label"><span>Total Deductions</span><span style="color:#f87171">– LKR 14,020</span></div>
        <div class="vis-bar-track"><div class="vis-bar-fill vb-red" style="width:16%"></div></div>
      </div>

      <div class="vis-net">
        <span class="vis-net-label">Net Take-Home Pay</span>
        <span class="vis-net-value">LKR 86,949</span>
      </div>

      <div style="margin-top:20px; padding-top:16px; border-top:1px solid var(--navy-border); display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; text-align:center;">
        <div>
          <div style="font-size:13px; font-weight:700; color:#00e5b0;">LKR 10,200</div>
          <div style="font-size:10px; color:var(--text-faint); margin-top:2px;">EPF (Employer 12%)</div>
        </div>
        <div>
          <div style="font-size:13px; font-weight:700; color:#4f8eff;">LKR 2,550</div>
          <div style="font-size:10px; color:var(--text-faint); margin-top:2px;">ETF (3%)</div>
        </div>
        <div>
          <div style="font-size:13px; font-weight:700; color:#f5c542;">LKR 2,220</div>
          <div style="font-size:10px; color:var(--text-faint); margin-top:2px;">APIT Tax</div>
        </div>
      </div>
    </div>
  </div>
</section>





<!-- ============================================================
     TESTIMONIALS
     ============================================================ -->
<section class="testimonials" id="testimonials">
  <div class="testimonials-header reveal">
    <span class="section-label">Trusted By Companies</span>
    <h2 class="section-title">What Our Customers Say</h2>
  </div>

  <div class="testimonials-grid">
    <div class="testimonial-card reveal">
      <div class="tcard-stars">★★★★★</div>
      <div class="tcard-quote">We used to spend 3 days calculating salaries manually. With PaySheet Pro it takes 20 minutes for all 45 employees. The APIT and EPF automation alone saved us from so many mistakes.</div>
      <div class="tcard-author">
        <div class="tcard-avatar ta-1">KP</div>
        <div><div class="tcard-name">Krishanthi Perera</div><div class="tcard-title">HR Manager — TechVision (Pvt) Ltd, Colombo</div></div>
      </div>
    </div>

    <div class="testimonial-card reveal reveal-delay-1">
      <div class="tcard-stars">★★★★★</div>
      <div class="tcard-quote">The leave management module is excellent. Tracking annual, casual and sick leave separately with automatic deductions for unapproved leave is exactly what we needed.</div>
      <div class="tcard-author">
        <div class="tcard-avatar ta-2">RS</div>
        <div><div class="tcard-name">Roshan Silva</div><div class="tcard-title">Finance Director — Nexus Solutions, Kandy</div></div>
      </div>
    </div>

    <div class="testimonial-card reveal reveal-delay-2">
      <div class="tcard-stars">★★★★★</div>
      <div class="tcard-quote">Finally a payroll system built for Sri Lanka. The PDF payslips look professional and our employees love being able to see the full salary breakdown including APIT brackets.</div>
      <div class="tcard-author">
        <div class="tcard-avatar ta-3">AM</div>
        <div><div class="tcard-name">Ashan Mendis</div><div class="tcard-title">CEO — BlueWave Enterprises, Gampaha</div></div>
      </div>
    </div>

    
  </div>
</section>

<!-- ============================================================
     CTA BANNER
     ============================================================ -->
<section class="cta-banner">
  <div class="cta-inner reveal">
    <span class="section-label">Get Started Today</span>
    <h2 class="cta-title">Ready to Automate<br>Your Company's Payroll?</h2>
    <p class="cta-sub">Register your company for free. No credit card required. Your first month's paysheets on us.</p>
    <div class="cta-actions">
      <a href="index.php?page=register" class="btn-hero-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
        Register Your Company - Now
      </a>
      <a href="index.php?page=login" class="btn-hero-secondary">
        Already have an account? Sign In
      </a>
    </div>
  </div>
</section>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <a href="index.php" class="footer-logo">
        <div class="footer-logo-icon">₨</div>
        <div class="footer-logo-text">PaySheet <span>Pro</span></div>
      </a>
      <p class="footer-desc">The complete payroll management system built specifically for Sri Lankan companies. APIT, EPF, ETF — all automated.</p>
    </div>

    <div>
      <div class="footer-col-title">System</div>
      <ul class="footer-links">
        <li><a href="index.php?page=dashboard">Dashboard</a></li>
        <li><a href="index.php?page=employees">Employees</a></li>
        <li><a href="index.php?page=leave-list">Leave Management</a></li>
        <li><a href="index.php?page=generate-paysheet">Generate Paysheet</a></li>
        <li><a href="index.php?page=paysheet-list">Payslip History</a></li>
      </ul>
    </div>

    <div>
      <div class="footer-col-title">Payroll</div>
      <ul class="footer-links">
        <li><a href="index.php?page=ot-records">OT Records</a></li>
        <li><a href="index.php?page=loans">Loan Management</a></li>
        <li><a href="index.php?page=advances">Salary Advance</a></li>
        <li><a href="index.php?page=leave-balance">Leave Balance</a></li>
        <li><a href="index.php?page=profile">Company Profile</a></li>
      </ul>
    </div>

    <div>
      <div class="footer-col-title">Account</div>
      <ul class="footer-links">
        <li><a href="index.php?page=register">Register Company</a></li>
        <li><a href="index.php?page=login">Sign In</a></li>
        <li><a href="#pricing">Pricing</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="mailto:support@paysheetpro.lk">Support</a></li>
      </ul>
    </div>
  </div>

  <div class="footer-bottom">
    <span>© 2025 PaySheet Pro. Built for Sri Lankan Businesses.</span>
    <span>
      <a href="#">Privacy Policy</a> &nbsp;·&nbsp;
      <a href="#">Terms of Use</a> &nbsp;·&nbsp;
      <a href="#">Contact</a>
    </span>
  </div>
</footer>

<!-- Script -->
<script src="assets/js/home.js"></script>
</body>
</html>