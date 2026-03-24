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

// Form submit කරනවද check
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = clean($conn, $_POST['company_name']);
    $company_address = clean($conn, $_POST['company_address']);
    $company_phone = clean($conn, $_POST['company_phone']);
    $company_email = clean($conn, $_POST['company_email']);
    $username = clean($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validation
    if (empty($company_name)) $errors[] = "Company name is required";
    if (empty($company_email)) $errors[] = "Company email is required";
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    
    // Email already exists check
    $check_email = mysqli_query($conn, "SELECT * FROM companies WHERE company_email = '$company_email'");
    if (mysqli_num_rows($check_email) > 0) {
        $errors[] = "This email is already registered";
    }
    
    // Username already exists check
    $check_username = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check_username) > 0) {
        $errors[] = "This username is already taken";
    }
    
    if (empty($errors)) {
        // Company insert
        $insert_company = "INSERT INTO companies (company_name, company_address, company_phone, company_email) 
                          VALUES ('$company_name', '$company_address', '$company_phone', '$company_email')";
        
        if (mysqli_query($conn, $insert_company)) {
            $company_id = mysqli_insert_id($conn);
            
            // ⚠️ PLAIN TEXT PASSWORD - NO HASHING
            // REMOVED: $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Admin user insert with PLAIN TEXT password
            $insert_user = "INSERT INTO users (company_id, username, password, user_type) 
                           VALUES ('$company_id', '$username', '$password', 'admin')";
            
            if (mysqli_query($conn, $insert_user)) {
                setMessage('success', 'Company registered successfully! Please login.');
                redirect('login.php');
            }
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Registration - PaySheetPro</title>
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

    <div class="auth-container">
        <!-- Left Side - Info Panel -->
        <div class="auth-info">
            <div class="info-content">
                <a href="../index.php" class="back-link">← Back to Home</a>
                <h1 class="info-title">Welcome to<br><span class="gradient-text">PaySheetPro</span></h1>
                <p class="info-description">
                    Start managing your employee paysheets with our powerful automated system.
                </p>
                <div class="info-features">
                    <div class="info-feature">
                        <span class="feature-icon">✓</span>
                        <span>Automated Calculations</span>
                    </div>
                    <div class="info-feature">
                        <span class="feature-icon">✓</span>
                        <span>Leave Management</span>
                    </div>
                    <div class="info-feature">
                        <span class="feature-icon">✓</span>
                        <span>PDF Paysheets</span>
                    </div>
                    <div class="info-feature">
                        <span class="feature-icon">✓</span>
                        <span>Secure & Reliable</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Registration Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <div class="form-header">
                    <h2 class="form-title">Create Account</h2>
                    <p class="form-subtitle">Register your company to get started</p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <p>• <?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="auth-form" id="registerForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="company_name" class="form-label">
                                <span class="label-icon">🏢</span>
                                Company Name
                            </label>
                            <input type="text" id="company_name" name="company_name" class="form-input" 
                                   placeholder="Enter company name" required 
                                   value="<?php echo isset($company_name) ? $company_name : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="company_address" class="form-label">
                                <span class="label-icon">📍</span>
                                Company Address
                            </label>
                            <textarea id="company_address" name="company_address" class="form-input" 
                                      rows="3" placeholder="Enter company address" required><?php echo isset($company_address) ? $company_address : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label for="company_phone" class="form-label">
                                <span class="label-icon">📞</span>
                                Phone Number
                            </label>
                            <input type="tel" id="company_phone" name="company_phone" class="form-input" 
                                   placeholder="0XX XXX XXXX" required 
                                   value="<?php echo isset($company_phone) ? $company_phone : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="company_email" class="form-label">
                                <span class="label-icon">✉️</span>
                                Email Address
                            </label>
                            <input type="email" id="company_email" name="company_email" class="form-input" 
                                   placeholder="company@example.com" required 
                                   value="<?php echo isset($company_email) ? $company_email : ''; ?>">
                        </div>
                    </div>

                    <div class="form-divider">
                        <span>Admin Account Details</span>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <span class="label-icon">👤</span>
                                Username
                            </label>
                            <input type="text" id="username" name="username" class="form-input" 
                                   placeholder="Choose a username" required 
                                   value="<?php echo isset($username) ? $username : ''; ?>">
                        </div>
                    </div>

                    <div class="form-row form-row-2">
                        <div class="form-group">
                            <label for="password" class="form-label">
                                <span class="label-icon">🔒</span>
                                Password
                            </label>
                            <input type="password" id="password" name="password" class="form-input" 
                                   placeholder="Min. 6 characters" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <span class="label-icon">🔒</span>
                                Confirm Password
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                                   placeholder="Re-enter password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <span>Create Account</span>
                        <span class="btn-icon">→</span>
                    </button>

                    <div class="form-footer">
                        <p>Already have an account? <a href="login.php" class="link-primary">Login here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../accests/js/validation.js"></script>
</body>
</html>