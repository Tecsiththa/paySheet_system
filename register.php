<?php
// ============================================================

//   register.php  
// ============================================================
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php?page=dashboard');
    exit;
}

$error   = '';
$success = '';
$old     = [];   // repopulate fields on error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ── Collect & sanitize inputs ──
    $old = [
        'company_name'    => clean($_POST['company_name']    ?? ''),
        'company_email'   => clean($_POST['company_email']   ?? ''),
        'company_phone'   => clean($_POST['company_phone']   ?? ''),
        'registration_no' => clean($_POST['registration_no'] ?? ''),
        'industry'        => clean($_POST['industry']        ?? ''),
        'company_size'    => clean($_POST['company_size']    ?? ''),
        'company_address' => clean($_POST['company_address'] ?? ''),
    ];
    $password = $_POST['password']         ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ── Validation ──
    if (!$old['company_name'] || !$old['company_email'] || !$password) {
        $error = 'Company name, email address, and password are required fields.';

    } elseif (!filter_var($old['company_email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';

    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match. Please re-enter.';

    } else {
        $db = getDB();

        // Check email uniqueness
        $chk = $db->prepare("SELECT id FROM companies WHERE company_email = ?");
        $chk->bind_param('s', $old['company_email']);
        $chk->execute();

        if ($chk->get_result()->num_rows > 0) {
            $error = 'This email address is already registered. Please sign in or use a different email.';
        } else {
            // ── Insert company ──
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("
                INSERT INTO companies
                  (company_name, company_email, company_phone, company_address, registration_no, password)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'ssssss',
                $old['company_name'],
                $old['company_email'],
                $old['company_phone'],
                $old['company_address'],
                $old['registration_no'],
                $hashed
            );

            if ($stmt->execute()) {
                $success = 'Company registered successfully! You can now sign in to your account.';
                $old = [];   // clear form
            } else {
                $error = 'Registration failed. Please try again. (' . $db->error . ')';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register Your Company — PaySheet Pro</title>
  <link rel="preconnect" href="https://api.fontshare.com">
  <link href="https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600,700&f[]=satoshi@400,500,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>

<div class="register-layout">

  <!-- ============================================================
       LEFT PANEL — Branding & Features
       ============================================================ -->
  <div class="left-panel">
    <div class="left-grid"></div>

    <!-- Logo -->
    <a href="home.php" class="left-logo">
      <div class="left-logo-icon">₨</div>
      <div class="left-logo-text">PaySheet <span>Pro</span></div>
    </a>

    <!-- Main content -->
    <div class="left-content">
      <div class="left-tagline">Sri Lanka's Payroll Platform</div>
      <h1 class="left-title">
        Everything your<br>
        company needs for<br>
        <span class="hl">smart payroll.</span>
      </h1>
      <p class="left-desc">
        Register once. Manage your entire workforce — from salary calculations to PDF payslips — fully automated and IRD compliant.
      </p>

      <ul class="left-features">
        <li class="left-feature">
          <div class="lf-icon lfi-teal">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#00e5b0" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
          </div>
          <div class="lf-text">
            <div class="lf-title">EPF, ETF &amp; APIT Auto-Calculated</div>
            <div class="lf-desc">All statutory deductions computed automatically as per Sri Lankan law</div>
          </div>
        </li>
        <li class="left-feature">
          <div class="lf-icon lfi-blue">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#4f8eff" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div class="lf-text">
            <div class="lf-title">Leave Management</div>
            <div class="lf-desc">Annual, Casual, Sick — tracked separately with automatic deductions</div>
          </div>
        </li>
        <li class="left-feature">
          <div class="lf-icon lfi-gold">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#f5c542" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div class="lf-text">
            <div class="lf-title">OT Calculation (×1.5 Rate)</div>
            <div class="lf-desc">Overtime hours computed precisely using the IRD-approved formula</div>
          </div>
        </li>
        <li class="left-feature">
          <div class="lf-icon lfi-green">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
          <div class="lf-text">
            <div class="lf-title">Professional PDF Payslips</div>
            <div class="lf-desc">One-click payslip generation with full salary breakdown per employee</div>
          </div>
        </li>
        <li class="left-feature">
          <div class="lf-icon lfi-purple">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#a78bfa" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </div>
          <div class="lf-text">
            <div class="lf-title">Secure &amp; Isolated Data</div>
            <div class="lf-desc">Your company data is completely private and protected</div>
          </div>
        </li>
      </ul>
    </div>

    <!-- Stats -->
    <div class="left-stats">
      <div class="left-stat">
        <div class="ls-value" data-to="500" data-suffix="+">0+</div>
        <div class="ls-label">Companies</div>
      </div>
      <div class="left-stat">
        <div class="ls-value" data-to="12000" data-suffix="+">0+</div>
        <div class="ls-label">Payslips Generated</div>
      </div>
      <div class="left-stat">
        <div class="ls-value" data-to="18">0</div>
        <div class="ls-label">Interfaces</div>
      </div>
    </div>
  </div>

  <!-- ============================================================
       RIGHT PANEL — Registration Form
       ============================================================ -->
  <div class="right-panel">

    <!-- Form header -->
    <div class="form-header">
      <!-- Step indicator -->
      <div class="form-step-indicator">
        <div class="step-dot done"></div>
        <div class="step-dot active"></div>
        <div class="step-dot"></div>
      </div>

      <h2 class="form-title">Create Your Company Account</h2>
      <p class="form-subtitle">Fill in your company details to get started.</p>
    </div>

    <!-- Alerts -->
    <?php if ($error): ?>
    <div class="alert alert-error">
      <span class="alert-icon">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </span>
      <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success">
      <span class="alert-icon">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      </span>
      <span>
        <?= htmlspecialchars($success) ?>
        &nbsp;<a href="index.php?page=login" style="color:#4ade80;font-weight:700;text-decoration:underline;">Sign in now →</a>
      </span>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <form id="registerForm" method="POST" action="" novalidate>

      <!-- ── Section 1: Company Info ── -->
      <div class="form-section-label">Company Information</div>
      <div class="form-grid-2">

        <div class="fg col-span-2">
          <label for="company_name">
            Company Name <span class="req">*</span>
            <span id="nameCounter" style="margin-left:auto; font-size:10px; color:var(--text-faint); font-weight:400;">0/100</span>
          </label>
          <div class="input-wrap">
            <span class="input-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </span>
            <input
              type="text"
              id="company_name"
              name="company_name"
              placeholder="e.g. Acme Technologies (Pvt) Ltd."
              maxlength="100"
              required
              autocomplete="organization"
              value="<?= htmlspecialchars($old['company_name'] ?? '') ?>"
            >
          </div>
        </div>

        <div class="fg">
          <label for="company_email">Business Email <span class="req">*</span></label>
          <div class="input-wrap">
            <span class="input-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </span>
            <input
              type="email"
              id="company_email"
              name="company_email"
              placeholder="info@company.com"
              required
              autocomplete="email"
              value="<?= htmlspecialchars($old['company_email'] ?? '') ?>"
            >
          </div>
          <div class="field-msg" id="emailMsg"></div>
        </div>

        <div class="fg">
          <label for="company_phone">Phone Number</label>
          <div class="input-wrap">
            <span class="input-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.59 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </span>
            <input
              type="tel"
              id="company_phone"
              name="company_phone"
              placeholder="0112345678 or +94112345678"
              value="<?= htmlspecialchars($old['company_phone'] ?? '') ?>"
            >
          </div>
        </div>

        <div class="fg">
          <label for="registration_no">Business Registration No.</label>
          <div class="input-wrap">
            <span class="input-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </span>
            <input
              type="text"
              id="registration_no"
              name="registration_no"
              placeholder="PV/ABC/12345"
              value="<?= htmlspecialchars($old['registration_no'] ?? '') ?>"
            >
          </div>
          <span class="hint">Optional — your ROC or PV number</span>
        </div>

        <div class="fg">
          <label for="industry">Industry</label>
          <select id="industry" name="industry">
            <option value="" disabled <?= empty($old['industry']) ? 'selected' : '' ?>>Select Industry</option>
            <option value="IT"          <?= ($old['industry'] ?? '') === 'IT'          ? 'selected' : '' ?>>Information Technology</option>
            <option value="Finance"     <?= ($old['industry'] ?? '') === 'Finance'     ? 'selected' : '' ?>>Finance & Banking</option>
            <option value="Healthcare"  <?= ($old['industry'] ?? '') === 'Healthcare'  ? 'selected' : '' ?>>Healthcare</option>
            <option value="Education"   <?= ($old['industry'] ?? '') === 'Education'   ? 'selected' : '' ?>>Education</option>
            <option value="Retail"      <?= ($old['industry'] ?? '') === 'Retail'      ? 'selected' : '' ?>>Retail & Trade</option>
            <option value="Manufacturing"<?= ($old['industry'] ?? '') === 'Manufacturing'? 'selected' : '' ?>>Manufacturing</option>
            <option value="Construction"<?= ($old['industry'] ?? '') === 'Construction'? 'selected' : '' ?>>Construction</option>
            <option value="Hospitality" <?= ($old['industry'] ?? '') === 'Hospitality' ? 'selected' : '' ?>>Hospitality & Tourism</option>
            <option value="Logistics"   <?= ($old['industry'] ?? '') === 'Logistics'   ? 'selected' : '' ?>>Logistics & Transport</option>
            <option value="Other"       <?= ($old['industry'] ?? '') === 'Other'       ? 'selected' : '' ?>>Other</option>
          </select>
        </div>

        <div class="fg">
          <label for="company_size">Company Size</label>
          <select id="company_size" name="company_size">
            <option value="" disabled <?= empty($old['company_size']) ? 'selected' : '' ?>>Number of Employees</option>
            <option value="1-10"    <?= ($old['company_size'] ?? '') === '1-10'    ? 'selected' : '' ?>>1 – 10 employees</option>
            <option value="11-50"   <?= ($old['company_size'] ?? '') === '11-50'   ? 'selected' : '' ?>>11 – 50 employees</option>
            <option value="51-200"  <?= ($old['company_size'] ?? '') === '51-200'  ? 'selected' : '' ?>>51 – 200 employees</option>
            <option value="201-500" <?= ($old['company_size'] ?? '') === '201-500' ? 'selected' : '' ?>>201 – 500 employees</option>
            <option value="500+"    <?= ($old['company_size'] ?? '') === '500+'    ? 'selected' : '' ?>>500+ employees</option>
          </select>
        </div>

        <div class="fg col-span-2">
          <label for="company_address">Company Address</label>
          <textarea
            id="company_address"
            name="company_address"
            placeholder="No. 01, Main Street, Colombo 01, Sri Lanka"
            rows="2"
          ><?= htmlspecialchars($old['company_address'] ?? '') ?></textarea>
        </div>

      </div>

      <hr class="form-divider">

      <!-- ── Section 2: Security ── -->
      <div class="form-section-label">Account Security</div>
      <div class="form-grid-2">

        <div class="fg col-span-2">
          <label for="password">Password <span class="req">*</span></label>
          <div class="pass-wrap input-wrap">
            <span class="input-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Minimum 6 characters"
              required
              autocomplete="new-password"
              style="padding-left:38px;"
            >
            <button type="button" class="pass-toggle" aria-label="Show password">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
          <div class="strength-text" id="strengthText"></div>
        </div>

        <div class="fg col-span-2">
          <label for="confirm_password">Confirm Password <span class="req">*</span></label>
          <div class="pass-wrap input-wrap">
            <span class="input-icon">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input
              type="password"
              id="confirm_password"
              name="confirm_password"
              placeholder="Re-enter your password"
              required
              autocomplete="new-password"
              style="padding-left:38px;"
            >
            <button type="button" class="pass-toggle" aria-label="Show confirm password">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <div class="field-msg" id="matchMsg"></div>
        </div>

      </div>

      <hr class="form-divider">

      <!-- ── Terms ── -->
      <div class="checkbox-group" style="margin-bottom:22px;">
        <input type="checkbox" id="terms" name="terms">
        <label for="terms" style="cursor:pointer; font-size:13px; color:var(--text-mid); line-height:1.5;">
          I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>. I confirm that I am authorised to register this company on PaySheet Pro.
        </label>
      </div>

      <!-- ── Submit ── -->
      <button type="submit" class="btn-submit" id="btnSubmit">
        <span id="btnText">Create Company Account</span>
        <div class="spinner" id="btnSpinner"></div>
        <svg id="btnIcon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
      </button>

      <!-- ── Login link ── -->
      <div class="login-link">
        Already have an account? <a href="index.php?page=login">Sign in here</a>
      </div>

      <!-- ── Trust badges ── -->
      <div class="trust-badges">
        <div class="trust-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#3d5478" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          Secure &amp; 
        </div>
        
        <div class="trust-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#3d5478" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Free to Start
        </div>
        <div class="trust-badge">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#3d5478" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Setup in 2 Minutes
        </div>
      </div>

    </form>
  </div><!-- /.right-panel -->

</div><!-- /.register-layout -->

<script src="assets/js/register.js"></script>
</body>
</html>