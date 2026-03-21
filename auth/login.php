<?php
require_once '../config/database.php';

// Already logged in නම් redirect
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('../admin/dashboard.php');
    } else {
        redirect('../employee/employee_dashboard.php');
    }
}

// Form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean($conn, $_POST['username']);
    $password = $_POST['password'];
    $user_type = clean($conn, $_POST['user_type']);
    
    $errors = [];
    
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    
    if (empty($errors)) {
        // User check
        $query = "SELECT u.*, c.company_name, c.company_id 
                  FROM users u 
                  LEFT JOIN companies c ON u.company_id = c.company_id 
                  WHERE u.username = '$username' AND u.user_type = '$user_type' AND u.status = 'active'";
        
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            // Password verify
            if (password_verify($password, $user['password'])) {
                // Session set
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['company_id'] = $user['company_id'];
                $_SESSION['company_name'] = $user['company_name'];
                
                if ($user['user_type'] == 'admin') {
                    redirect('../admin/dashboard.php');
                } else {
                    $_SESSION['employee_id'] = $user['linked_employee_id'];
                    redirect('../employee/employee_dashboard.php');
                }
            } else {
                $errors[] = "Invalid username or password";
            }
        } else {
            $errors[] = "Invalid username or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PaySheetPro</title>
    <link rel="stylesheet" href="../accests/css/style.css">
    <link rel="stylesheet" href="../accests/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Animated Background -->
    <div class="auth-background">
        <div class="gradient-circle circle-1"></div>
        <div class="gradient-circle circle-2"></div>
    </div>

    <div class="auth-container login-container">
        <!-- Left Side - Login Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <div class="form-header">
                    <a href="../index.php" class="back-link-mobile">← Back</a>
                    <div class="logo-login">
                        <span class="logo-icon">💼</span>
                        <span class="logo-text">PaySheet<span class="highlight">Pro</span></span>
                    </div>
                    <h2 class="form-title">Welcome Back</h2>
                    <p class="form-subtitle">Login to access your dashboard</p>
                </div>

                <?php displayMessage(); ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <p>• <?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-form" id="loginForm">
                    <div class="form-group">
                        <label for="user_type" class="form-label">
                            <span class="label-icon">👔</span>
                            Login As
                        </label>
                        <select id="user_type" name="user_type" class="form-input" required>
                            <option value="admin">Company Admin</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="username" class="form-label">
                            <span class="label-icon">👤</span>
                            Username
                        </label>
                        <input type="text" id="username" name="username" class="form-input" 
                               placeholder="Enter your username" required 
                               value="<?php echo isset($username) ? $username : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">
                            <span class="label-icon">🔒</span>
                            Password
                        </label>
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="Enter your password" required>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" class="link-secondary">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn-submit">
                        <span>Login to Dashboard</span>
                        <span class="btn-icon">→</span>
                    </button>

                    <div class="form-footer">
                        <p>Don't have an account? <a href="company_register.php" class="link-primary">Register Company</a></p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side - Info Panel -->
        <div class="auth-info">
            <div class="info-content">
                <a href="../index.php" class="back-link">← Back to Home</a>
                <h1 class="info-title">Manage Everything<br><span class="gradient-text">In One Place</span></h1>
                <p class="info-description">
                    Access your complete payroll management system with automated calculations and detailed reports.
                </p>
                <div class="info-stats">
                    <div class="stat-card">
                        <div class="stat-value">100%</div>
                        <div class="stat-label">Automated</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">24/7</div>
                        <div class="stat-label">Available</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">Secure</div>
                        <div class="stat-label">Protected</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../accests/js/validation.js"></script>
</body>
</html>