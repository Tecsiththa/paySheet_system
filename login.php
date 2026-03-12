<?php
// ============================================================
// Company Login Page
// ============================================================
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php?page=dashboard');
    exit;
}

$error   = '';
$success = '';

// ── Check for success message after registration ──
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $success = 'Company registered successfully! Please sign in with your credentials.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email']    ?? '');
    $password = $_POST['password']       ?? '';
    $remember = isset($_POST['remember']);

    // ── Basic validation ──
    if (!$email || !$password) {
        $error = 'Please enter your email address and password.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';

    } else {
        $db = getDB();

        // ── Fetch company by email ──
        $stmt = $db->prepare("SELECT * FROM companies WHERE company_email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $company = $stmt->get_result()->fetch_assoc();

        if ($company && password_verify($password, $company['password'])) {

            if ($company['status'] !== 'active') {
                $error = 'Your company account has been deactivated. Please contact support.';
            } else {
                // ── Regenerate session for security ──
                session_regenerate_id(true);

                // ── Set session ──
                $_SESSION['company_id']   = $company['id'];
                $_SESSION['company_name'] = $company['company_name'];
                $_SESSION['login_time']   = time();

                // ── Redirect to dashboard ──
                header('Location: index.php?page=dashboard');
                exit;
            }
        } else {
            // Generic error — don't reveal which field is wrong
            $error = 'Invalid email address or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — PaySheet Pro</title>
  <link rel="preconnect" href="https://api.fontshare.com">
  <link href="https://api.fontshare.com/v2/css?f[]=clash-display@400,500,600,700&f[]=satoshi@400,500,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<!-- ============================================================
     BACKGROUND SCENE
     ============================================================ -->
<div class="bg-scene">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>

  <!-- Geometric shapes -->
  <div class="geo geo-1"></div>
  <div class="geo geo-2"></div>
  <div class="geo geo-3"></div>
  <div class="geo geo-4"></div>

  <!-- Particle container (filled by JS) -->
  <div class="particles"></div>
</div>

<!-- ============================================================
     LOGIN WRAPPER
     ============================================================ -->
<div class="login-wrapper">

  <!-- Logo -->
  <a href="home.php" class="login-logo" aria-label="Back to PaySheet Pro home">
    <div class="logo-icon">₨</div>
    <div class="logo-text">PaySheet <span>Pro</span></div>
  </a>

  <!-- Login Card -->
  <div class="login-card" id="loginCard">
    <div class="card-glow"></div>

    <!-- Card Header -->
    <div class="card-header">
      <h1>Welcome back</h1>
      <p>Sign in to your company account to manage payroll</p>
    </div>

    <!-- Server-side Alerts -->
    <?php if ($error): ?>
    <div class="alert alert-error" role="alert">
      <span class="alert-icon">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8"  x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </span>
      <span><?= htmlspecialchars($error) ?></span>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success" role="alert">
      <span class="alert-icon">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </span>
      <span><?= htmlspecialchars($success) ?></span>
    </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form id="loginForm" method="POST" action="" novalidate>
      <div class="form-body">

        <!-- Email -->
        <div class="field">
          <label for="email">
            Company Email <span class="req">*</span>
          </label>
          <div class="input-box">
            <span class="i-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
              </svg>
            </span>
            <input
              type="email"
              id="email"
              name="email"
              placeholder="company@example.com"
              required
              autocomplete="email"
              autofocus
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              aria-describedby="emailErr"
            >
          </div>
          <div class="field-err" id="emailErr" role="alert"></div>
        </div>

        <!-- Password -->
        <div class="field">
          <label for="password">
            Password <span class="req">*</span>
            <a href="#" class="label-link" tabindex="-1">Forgot password?</a>
          </label>
          <div class="input-box">
            <span class="i-icon">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </span>
            <input
              type="password"
              id="password"
              name="password"
              placeholder="Enter your password"
              required
              autocomplete="current-password"
              aria-describedby="passErr"
            >
            <button type="button" class="pass-toggle" id="passToggle" aria-label="Toggle password visibility">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <div class="field-err" id="passErr" role="alert"></div>
        </div>

        <!-- Remember me -->
        <div class="form-options">
          <label class="remember-label" for="remember">
            <input type="checkbox" id="remember" name="remember">
            Remember my email
          </label>
          <a href="#" class="forgot-link">Forgot password?</a>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-login" id="btnLogin">
          <span id="btnText">Sign In to Dashboard</span>
          <div class="spinner" id="btnSpinner"></div>
          <svg id="btnArrow" class="btn-arrow" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="5" y1="12" x2="19" y2="12"/>
            <polyline points="12 5 19 12 12 19"/>
          </svg>
        </button>

      </div><!-- /.form-body -->

      <!-- Divider -->
      <div class="card-divider">or</div>

      <!-- Register CTA -->
      <a href="register.php" class="btn-register">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <line x1="19" y1="8" x2="19" y2="14"/>
          <line x1="22" y1="11" x2="16" y2="11"/>
        </svg>
        Create a New Company Account
      </a>

    </form>

    <!-- Trust Strip -->
    <div class="card-footer">
      <div class="trust-item">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2"/>
          <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
        Encrypted &amp; Secure
      </div>
      <div class="trust-item">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        IRD Compliant
      </div>
      <div class="trust-item">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
        </svg>
        500+ Companies
      </div>
    </div>

  </div><!-- /.login-card -->

  <!-- Back to home -->
  <div class="back-home">
    <a href="home.php">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <line x1="19" y1="12" x2="5" y2="12"/>
        <polyline points="12 19 5 12 12 5"/>
      </svg>
      Back to Home
    </a>
  </div>

</div><!-- /.login-wrapper -->

<script src="assets/js/login.js"></script>
</body>
</html>