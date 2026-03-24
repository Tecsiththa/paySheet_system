<?php
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// Check if user is admin
if (!isAdmin()) {
    redirect('../employee/employee_dashboard.php');
}

$user_id = getCurrentUserId();
$company_id = getCompanyId();
$username = $_SESSION['username'];

// Fetch company details
$query_company = "SELECT * FROM companies WHERE company_id = '$company_id'";
$result_company = mysqli_query($conn, $query_company);
$company = mysqli_fetch_assoc($result_company);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_POST['update_company'])) {
        // Update company information
        $company_name = clean($conn, $_POST['company_name']);
        $company_address = clean($conn, $_POST['company_address']);
        $company_phone = clean($conn, $_POST['company_phone']);
        $company_email = clean($conn, $_POST['company_email']);
        
        $errors = [];
        
        // Validation
        if (empty($company_name)) $errors[] = "Company name is required";
        if (empty($company_email)) $errors[] = "Company email is required";
        if (!filter_var($company_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
        
        // Check if email already exists (for other companies)
        $check_email = mysqli_query($conn, "SELECT company_id FROM companies WHERE company_email = '$company_email' AND company_id != '$company_id'");
        if (mysqli_num_rows($check_email) > 0) {
            $errors[] = "This email is already registered to another company";
        }
        
        if (empty($errors)) {
            $update_query = "UPDATE companies SET 
                            company_name = '$company_name',
                            company_address = '$company_address',
                            company_phone = '$company_phone',
                            company_email = '$company_email'
                            WHERE company_id = '$company_id'";
            
            if (mysqli_query($conn, $update_query)) {
                // Update session company name
                $_SESSION['company_name'] = $company_name;
                setMessage('success', 'Company information updated successfully!');
                redirect('company_settings.php');
            } else {
                $errors[] = "Failed to update company information";
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        // Change admin password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        $errors = [];
        
        // Fetch current user
        $query_user = "SELECT password FROM users WHERE user_id = '$user_id'";
        $result_user = mysqli_query($conn, $query_user);
        $user = mysqli_fetch_assoc($result_user);
        
        // Verify current password using helper function
        if (!verifyPassword($current_password, $user['password'])) {
            $errors[] = "Current password is incorrect";
        }
        
        // Validate new password
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters";
        }
        
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
        
        if (empty($errors)) {
            $hashed_password = hashPassword($new_password);
            $update_password = "UPDATE users SET password = '$hashed_password' WHERE user_id = '$user_id'";
            
            if (mysqli_query($conn, $update_password)) {
                setMessage('success', 'Password changed successfully!');
                redirect('company_settings.php');
            } else {
                $errors[] = "Failed to change password";
            }
        }
    }
}

// Fetch leave type settings
$query_leave_types = "SELECT * FROM leave_types ORDER BY leave_type_id";
$result_leave_types = mysqli_query($conn, $query_leave_types);

// Get total employees
$query_total_employees = "SELECT COUNT(*) as total FROM employees WHERE company_id = '$company_id' AND status = 'active'";
$result_total_employees = mysqli_query($conn, $query_total_employees);
$total_employees = mysqli_fetch_assoc($result_total_employees)['total'];

// Get total paysheets
$query_total_paysheets = "SELECT COUNT(*) as total FROM paysheets p 
                          JOIN employees e ON p.employee_id = e.employee_id 
                          WHERE e.company_id = '$company_id'";
$result_total_paysheets = mysqli_query($conn, $query_total_paysheets);
$total_paysheets = mysqli_fetch_assoc($result_total_paysheets)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Settings - PaySheetPro</title>
    <link rel="stylesheet" href="../accests/css/style.css">
    <link rel="stylesheet" href="../accests/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .settings-grid {
            display: grid;
            gap: 24px;
        }
        
        .settings-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-sm);
        }
        
        .settings-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--border-color);
        }
        
        .settings-icon {
            width: 48px;
            height: 48px;
            background: var(--gradient-primary);
            border-radius: var(--border-radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .settings-title {
            font-size: var(--font-xl);
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .form-grid-full {
            grid-column: 1 / -1;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 16px;
            background: var(--gray-50);
            border-radius: var(--border-radius-md);
            margin-bottom: 12px;
        }
        
        .info-label {
            font-size: var(--font-sm);
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .info-value {
            font-size: var(--font-md);
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .leave-type-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: var(--gray-50);
            border-radius: var(--border-radius-md);
            margin-bottom: 12px;
        }
        
        .leave-type-name {
            font-size: var(--font-md);
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .leave-type-days {
            font-size: var(--font-sm);
            color: var(--text-secondary);
            margin-top: 4px;
        }
        
        .leave-type-badge {
            background: var(--gradient-primary);
            color: white;
            padding: 8px 16px;
            border-radius: var(--border-radius-md);
            font-weight: 600;
        }
        
        .tax-table {
            width: 100%;
            margin-top: 16px;
        }
        
        .tax-table th,
        .tax-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .tax-table th {
            background: var(--gray-50);
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .tax-table td {
            color: var(--text-secondary);
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    
    <!-- ===== HEADER ===== -->
    <header class="dashboard-header">
        <div class="header-left">
            <button class="menu-toggle" id="menuToggle">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="logo">
                <span class="logo-icon">💼</span>
                <span class="logo-text">PaySheet<span class="highlight">Pro</span></span>
            </div>
        </div>
        
        <div class="header-center">
            <div class="company-info">
                <span class="company-label">Company:</span>
                <span class="company-name"><?php echo htmlspecialchars($company['company_name']); ?></span>
            </div>
        </div>
        
        <div class="header-right">
            <div class="user-menu">
                <div class="user-avatar">
                    <span><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                    <span class="user-role">Admin</span>
                </div>
                <a href="../auth/logout.php" class="logout-btn" title="Logout">
                    <span class="icon">🚪</span>
                </a>
            </div>
        </div>
    </header>

    <div class="dashboard-container">
        
        <!-- ===== SIDEBAR ===== -->
        <aside class="sidebar" id="sidebar">
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="employees/view_employees.php" class="nav-link">
                            <span class="nav-icon">👥</span>
                            <span class="nav-text">Employees</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="leaves/leave_requests.php" class="nav-link">
                            <span class="nav-icon">📅</span>
                            <span class="nav-text">Leave Requests</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="paysheet/generate_paysheet.php" class="nav-link">
                            <span class="nav-icon">💰</span>
                            <span class="nav-text">Paysheets</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="loans/view_loans.php" class="nav-link">
                            <span class="nav-icon">💳</span>
                            <span class="nav-text">Loans</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="advances/view_advances.php" class="nav-link">
                            <span class="nav-icon">⚡</span>
                            <span class="nav-text">Salary Advances</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reports/monthly_report.php" class="nav-link">
                            <span class="nav-icon">📈</span>
                            <span class="nav-text">Reports</span>
                        </a>
                    </li>
                    <li class="nav-divider"></li>
                    <li class="nav-item active">
                        <a href="company_settings.php" class="nav-link">
                            <span class="nav-icon">⚙️</span>
                            <span class="nav-text">Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- ===== MAIN CONTENT ===== -->
        <main class="main-content">
            
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Company Settings</h1>
                    <p class="page-subtitle">Manage your company information and system settings</p>
                </div>
            </div>

            <?php displayMessage(); ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p>• <?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="settings-grid">
                
                <!-- Company Information -->
                <div class="settings-card">
                    <div class="settings-header">
                        <div class="settings-icon">🏢</div>
                        <h2 class="settings-title">Company Information</h2>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="company_name" class="form-label">
                                    <span class="label-icon">🏢</span>
                                    Company Name
                                </label>
                                <input type="text" id="company_name" name="company_name" class="form-input" 
                                       value="<?php echo htmlspecialchars($company['company_name']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="company_email" class="form-label">
                                    <span class="label-icon">✉️</span>
                                    Email Address
                                </label>
                                <input type="email" id="company_email" name="company_email" class="form-input" 
                                       value="<?php echo htmlspecialchars($company['company_email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="company_phone" class="form-label">
                                    <span class="label-icon">📞</span>
                                    Phone Number
                                </label>
                                <input type="tel" id="company_phone" name="company_phone" class="form-input" 
                                       value="<?php echo htmlspecialchars($company['company_phone']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">
                                    <span class="label-icon">📅</span>
                                    Registration Date
                                </label>
                                <input type="text" class="form-input" 
                                       value="<?php echo formatDate($company['registration_date']); ?>" disabled>
                            </div>
                            
                            <div class="form-group form-grid-full">
                                <label for="company_address" class="form-label">
                                    <span class="label-icon">📍</span>
                                    Company Address
                                </label>
                                <textarea id="company_address" name="company_address" class="form-input" 
                                          rows="3" required><?php echo htmlspecialchars($company['company_address']); ?></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_company" class="btn-primary" style="margin-top: 20px;">
                            <span>Update Company Information</span>
                        </button>
                    </form>
                </div>

                <!-- System Statistics -->
                <div class="settings-card">
                    <div class="settings-header">
                        <div class="settings-icon">📊</div>
                        <h2 class="settings-title">System Statistics</h2>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Total Active Employees</span>
                        <span class="info-value"><?php echo $total_employees; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Total Paysheets Generated</span>
                        <span class="info-value"><?php echo $total_paysheets; ?></span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Account Status</span>
                        <span class="info-value">
                            <span class="badge badge-green">Active</span>
                        </span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">Current Admin</span>
                        <span class="info-value"><?php echo htmlspecialchars($username); ?></span>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="settings-card">
                    <div class="settings-header">
                        <div class="settings-icon">🔒</div>
                        <h2 class="settings-title">Change Password</h2>
                    </div>
                    
                    <form method="POST" action="">
                        <div class="form-grid">
                            <div class="form-group form-grid-full">
                                <label for="current_password" class="form-label">
                                    <span class="label-icon">🔑</span>
                                    Current Password
                                </label>
                                <input type="password" id="current_password" name="current_password" 
                                       class="form-input" placeholder="Enter current password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password" class="form-label">
                                    <span class="label-icon">🔒</span>
                                    New Password
                                </label>
                                <input type="password" id="new_password" name="new_password" 
                                       class="form-input" placeholder="Enter new password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">
                                    <span class="label-icon">🔒</span>
                                    Confirm New Password
                                </label>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                       class="form-input" placeholder="Confirm new password" required>
                            </div>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn-primary" style="margin-top: 20px;">
                            <span>Change Password</span>
                        </button>
                    </form>
                </div>

                <!-- Leave Types Configuration -->
                <div class="settings-card">
                    <div class="settings-header">
                        <div class="settings-icon">📅</div>
                        <h2 class="settings-title">Leave Types Configuration</h2>
                    </div>
                    
                    <?php while ($leave_type = mysqli_fetch_assoc($result_leave_types)): ?>
                        <div class="leave-type-item">
                            <div>
                                <div class="leave-type-name"><?php echo htmlspecialchars($leave_type['leave_name']); ?></div>
                                <div class="leave-type-days"><?php echo htmlspecialchars($leave_type['description']); ?></div>
                            </div>
                            <div class="leave-type-badge"><?php echo $leave_type['days_per_year']; ?> days/year</div>
                        </div>
                    <?php endwhile; ?>
                    
                    <div class="alert alert-info" style="margin-top: 20px;">
                        <p><strong>Note:</strong> Leave types are system defaults and apply to all employees. Any unused leave days do not carry forward to the next year.</p>
                    </div>
                </div>

                <!-- Tax Configuration -->
                <div class="settings-card">
                    <div class="settings-header">
                        <div class="settings-icon">💰</div>
                        <h2 class="settings-title">Tax & Deduction Rates</h2>
                    </div>
                    
                    <h3 style="margin-bottom: 16px; font-size: var(--font-md); color: var(--text-primary);">
                        Statutory Deductions
                    </h3>
                    
                    <div class="info-row">
                        <span class="info-label">EPF (Employees' Provident Fund)</span>
                        <span class="info-value">12%</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">ETF (Employees' Trust Fund)</span>
                        <span class="info-value">3%</span>
                    </div>
                    
                    <h3 style="margin: 24px 0 16px; font-size: var(--font-md); color: var(--text-primary);">
                        APIT Tax Slabs
                    </h3>
                    
                    <table class="tax-table">
                        <thead>
                            <tr>
                                <th>Monthly Income Range</th>
                                <th>Tax Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Up to LKR 100,000</td>
                                <td><strong>0%</strong></td>
                            </tr>
                            <tr>
                                <td>Next LKR 41,667</td>
                                <td><strong>6%</strong></td>
                            </tr>
                            <tr>
                                <td>Next LKR 41,667</td>
                                <td><strong>12%</strong></td>
                            </tr>
                            <tr>
                                <td>Next LKR 41,667</td>
                                <td><strong>18%</strong></td>
                            </tr>
                            <tr>
                                <td>Next LKR 41,667</td>
                                <td><strong>24%</strong></td>
                            </tr>
                            <tr>
                                <td>Next LKR 41,667</td>
                                <td><strong>30%</strong></td>
                            </tr>
                            <tr>
                                <td>Above that</td>
                                <td><strong>36%</strong></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <h3 style="margin: 24px 0 16px; font-size: var(--font-md); color: var(--text-primary);">
                        Overtime Calculation
                    </h3>
                    
                    <div class="info-row">
                        <span class="info-label">Hourly Rate Formula</span>
                        <span class="info-value">Monthly Salary ÷ 240</span>
                    </div>
                    
                    <div class="info-row">
                        <span class="info-label">OT Rate</span>
                        <span class="info-value">Hourly Rate × 1.5</span>
                    </div>
                </div>

            </div>

        </main>

    </div>

    <script src="../accests/js/main.js"></script>
    <script src="../accests/js/validation.js"></script>
    <script>
        // Sidebar Toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
