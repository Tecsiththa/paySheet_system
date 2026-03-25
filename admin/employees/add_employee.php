<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();
$username = $_SESSION['username'];
$company_name = $_SESSION['company_name'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_name = clean($conn, $_POST['employee_name']);
    $employee_nic = clean($conn, $_POST['employee_nic']);
    $employee_email = clean($conn, $_POST['employee_email']);
    $employee_phone = clean($conn, $_POST['employee_phone']);
    $employee_address = clean($conn, $_POST['employee_address']);
    $position = clean($conn, $_POST['position']);
    $department = clean($conn, $_POST['department']);
    $basic_salary = clean($conn, $_POST['basic_salary']);
    $joining_date = clean($conn, $_POST['joining_date']);
    
    // NEW FIELDS: Username & Password
    $emp_username = clean($conn, $_POST['emp_username']);
    $emp_password = $_POST['emp_password'];
    
    $errors = [];
    
    // Validation
    if (empty($employee_name)) $errors[] = "Employee name is required";
    if (empty($employee_nic)) $errors[] = "NIC is required";
    if (empty($employee_email)) $errors[] = "Email is required";
    if (empty($position)) $errors[] = "Position is required";
    if (empty($department)) $errors[] = "Department is required";
    if (empty($basic_salary)) $errors[] = "Basic salary is required";
    if (empty($joining_date)) $errors[] = "Date of joining is required";
    
    // NEW VALIDATION: Username & Password
    if (empty($emp_username)) $errors[] = "Username is required";
    if (empty($emp_password)) $errors[] = "Password is required";
    if (strlen($emp_password) < 6) $errors[] = "Password must be at least 6 characters";
    
    // Check if email already exists
    $check_email = mysqli_query($conn, "SELECT employee_id FROM employees WHERE employee_email = '$employee_email' AND company_id = '$company_id'");
    if (mysqli_num_rows($check_email) > 0) {
        $errors[] = "Email already exists";
    }
    
    // Check if NIC already exists
    $check_nic = mysqli_query($conn, "SELECT employee_id FROM employees WHERE employee_nic = '$employee_nic' AND company_id = '$company_id'");
    if (mysqli_num_rows($check_nic) > 0) {
        $errors[] = "NIC already exists";
    }
    
    // NEW CHECK: Username already exists
    $check_username = mysqli_query($conn, "SELECT user_id FROM users WHERE username = '$emp_username'");
    if (mysqli_num_rows($check_username) > 0) {
        $errors[] = "Username already exists. Please choose another username.";
    }
    
    if (empty($errors)) {
        // Insert employee
        $insert_employee = "INSERT INTO employees (
            company_id, employee_name, employee_nic, employee_email, 
            employee_phone, employee_address, position, department, 
            basic_salary, joining_date, status
        ) VALUES (
            '$company_id', '$employee_name', '$employee_nic', '$employee_email',
            '$employee_phone', '$employee_address', '$position', '$department',
            '$basic_salary', '$joining_date', 'active'
        )";
        
        if (mysqli_query($conn, $insert_employee)) {
            $employee_id = mysqli_insert_id($conn);
            
            // NEW: Create user account for employee (PLAIN TEXT PASSWORD)
            $insert_user = "INSERT INTO users (
                company_id, username, password, user_type, linked_employee_id, status
            ) VALUES (
                '$company_id', '$emp_username', '$emp_password', 'employee', '$employee_id', 'active'
            )";
            
            if (mysqli_query($conn, $insert_user)) {
                // Initialize leave balance for current year
                $current_year = date('Y');
                $init_leave = "INSERT INTO leave_balance (employee_id, year, annual_leave_remaining, casual_leave_remaining, sick_leave_remaining)
                              VALUES ('$employee_id', '$current_year', 14, 7, 7)";
                mysqli_query($conn, $init_leave);
                
                setMessage('success', "Employee added successfully! Login credentials - Username: $emp_username, Password: $emp_password");
                redirect('view_employees.php');
            } else {
                // If user creation fails, delete the employee record
                mysqli_query($conn, "DELETE FROM employees WHERE employee_id = '$employee_id'");
                $errors[] = "Failed to create user account. Please try again.";
            }
        } else {
            $errors[] = "Failed to add employee";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee - PaySheetPro</title>
    <link rel="stylesheet" href="../../accests/css/style.css">
    <link rel="stylesheet" href="../../accests/css/dashboard.css">
    <link rel="stylesheet" href="../../accests/css/employee.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    
    <!-- Header -->
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
                <span class="company-name"><?php echo htmlspecialchars($company_name); ?></span>
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
                <a href="../../auth/logout.php" class="logout-btn" title="Logout">
                    <span class="icon">🚪</span>
                </a>
            </div>
        </div>
    </header>

    <div class="dashboard-container">
        
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="../dashboard.php" class="nav-link">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a href="view_employees.php" class="nav-link">
                            <span class="nav-icon">👥</span>
                            <span class="nav-text">Employees</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../leaves/leave_requests.php" class="nav-link">
                            <span class="nav-icon">📅</span>
                            <span class="nav-text">Leave Requests</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../paysheet/generate_paysheet.php" class="nav-link">
                            <span class="nav-icon">💰</span>
                            <span class="nav-text">Paysheets</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../loans/view_loans.php" class="nav-link">
                            <span class="nav-icon">💳</span>
                            <span class="nav-text">Loans</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../advances/view_advances.php" class="nav-link">
                            <span class="nav-icon">⚡</span>
                            <span class="nav-text">Salary Advances</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../reports/monthly_report.php" class="nav-link">
                            <span class="nav-icon">📈</span>
                            <span class="nav-text">Reports</span>
                        </a>
                    </li>
                    <li class="nav-divider"></li>
                    <li class="nav-item">
                        <a href="../company_settings.php" class="nav-link">
                            <span class="nav-icon">⚙️</span>
                            <span class="nav-text">Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            
            <div class="page-header">
                <div>
                    <h1 class="page-title">Add New Employee</h1>
                    <p class="page-subtitle">Fill in the employee details and create login account</p>
                </div>
                <div class="page-actions">
                    <a href="view_employees.php" class="btn-secondary">
                        <span>← Back to Employees</span>
                    </a>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin: 8px 0 0 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form method="POST" action="" id="addEmployeeForm">
                    
                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <span class="section-icon">👤</span>
                            Personal Information
                        </h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="employee_name" class="form-label">
                                    <span class="label-icon">👤</span>
                                    Full Name *
                                </label>
                                <input type="text" id="employee_name" name="employee_name" class="form-input" 
                                       placeholder="Enter full name" required 
                                       value="<?php echo isset($employee_name) ? htmlspecialchars($employee_name) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="employee_nic" class="form-label">
                                    <span class="label-icon">🪪</span>
                                    NIC Number *
                                </label>
                                <input type="text" id="employee_nic" name="employee_nic" class="form-input" 
                                       placeholder="Enter NIC number" required 
                                       value="<?php echo isset($employee_nic) ? htmlspecialchars($employee_nic) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="employee_email" class="form-label">
                                    <span class="label-icon">✉️</span>
                                    Email Address *
                                </label>
                                <input type="email" id="employee_email" name="employee_email" class="form-input" 
                                       placeholder="employee@example.com" required 
                                       value="<?php echo isset($employee_email) ? htmlspecialchars($employee_email) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="employee_phone" class="form-label">
                                    <span class="label-icon">📞</span>
                                    Phone Number
                                </label>
                                <input type="tel" id="employee_phone" name="employee_phone" class="form-input" 
                                       placeholder="0XX XXX XXXX" 
                                       value="<?php echo isset($employee_phone) ? htmlspecialchars($employee_phone) : ''; ?>">
                            </div>

                            <div class="form-group form-group-full">
                                <label for="employee_address" class="form-label">
                                    <span class="label-icon">📍</span>
                                    Address
                                </label>
                                <textarea id="employee_address" name="employee_address" class="form-input" 
                                          rows="3" placeholder="Enter full address"><?php echo isset($employee_address) ? htmlspecialchars($employee_address) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Details Section -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <span class="section-icon">💼</span>
                            Employment Details
                        </h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="position" class="form-label">
                                    <span class="label-icon">💼</span>
                                    Position *
                                </label>
                                <input type="text" id="position" name="position" class="form-input" 
                                       placeholder="e.g., Software Engineer" required 
                                       value="<?php echo isset($position) ? htmlspecialchars($position) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="department" class="form-label">
                                    <span class="label-icon">🏢</span>
                                    Department *
                                </label>
                                <select id="department" name="department" class="form-input" required>
                                    <option value="">Select Department</option>
                                    <option value="IT" <?php echo (isset($department) && $department == 'IT') ? 'selected' : ''; ?>>IT</option>
                                    <option value="HR" <?php echo (isset($department) && $department == 'HR') ? 'selected' : ''; ?>>HR</option>
                                    <option value="Finance" <?php echo (isset($department) && $department == 'Finance') ? 'selected' : ''; ?>>Finance</option>
                                    <option value="Marketing" <?php echo (isset($department) && $department == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                    <option value="Sales" <?php echo (isset($department) && $department == 'Sales') ? 'selected' : ''; ?>>Sales</option>
                                    <option value="Operations" <?php echo (isset($department) && $department == 'Operations') ? 'selected' : ''; ?>>Operations</option>
                                    <option value="Administration" <?php echo (isset($department) && $department == 'Administration') ? 'selected' : ''; ?>>Administration</option>
                                    <option value="Other" <?php echo (isset($department) && $department == 'Other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="basic_salary" class="form-label">
                                    <span class="label-icon">💰</span>
                                    Basic Salary (LKR) *
                                </label>
                                <input type="number" id="basic_salary" name="basic_salary" class="form-input format-number" 
                                       placeholder="e.g., 100000" step="0.01" required 
                                       value="<?php echo isset($basic_salary) ? $basic_salary : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="joining_date" class="form-label">
                                    <span class="label-icon">📅</span>
                                    Date of Joining *
                                </label>
                                <input type="date" id="joining_date" name="joining_date" class="form-input" required 
                                       value="<?php echo isset($joining_date) ? $joining_date : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- NEW SECTION: Login Credentials -->
                    <div class="form-section">
                        <h3 class="section-title">
                            <span class="section-icon">🔐</span>
                            Login Account Details
                        </h3>
                        <p class="section-description">
                            Create a username and password for employee to access their dashboard
                        </p>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="emp_username" class="form-label">
                                    <span class="label-icon">👤</span>
                                    Username *
                                </label>
                                <input type="text" id="emp_username" name="emp_username" class="form-input" 
                                       placeholder="Choose a username" required 
                                       value="<?php echo isset($emp_username) ? htmlspecialchars($emp_username) : ''; ?>">
                                <small style="color: var(--text-secondary); font-size: 12px; margin-top: 4px; display: block;">
                                    Employee will use this to login
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="emp_password" class="form-label">
                                    <span class="label-icon">🔒</span>
                                    Password *
                                </label>
                                <input type="password" id="emp_password" name="emp_password" class="form-input" 
                                       placeholder="Create a password (min. 6 characters)" required>
                                <small style="color: var(--text-secondary); font-size: 12px; margin-top: 4px; display: block;">
                                    Minimum 6 characters required
                                </small>
                            </div>

                            <div class="form-group form-group-full">
                                <div class="alert alert-info" style="margin: 0;">
                                    <strong>📌 Note:</strong> Make sure to save or share these login credentials with the employee. 
                                    They will need these to access their employee dashboard.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Salary Preview (Optional) -->
                    <div class="form-section" id="salaryBreakdownPreview" style="display: none;">
                        <h3 class="section-title">
                            <span class="section-icon">📊</span>
                            Salary Breakdown Preview
                        </h3>
                        
                        <div class="preview-grid">
                            <div class="preview-item">
                                <span class="preview-label">EPF (12%):</span>
                                <span class="preview-value" id="epfPreview">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">ETF (3%):</span>
                                <span class="preview-value" id="etfPreview">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Hourly Rate:</span>
                                <span class="preview-value" id="hourlyRatePreview">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">OT Rate (1.5x):</span>
                                <span class="preview-value" id="otRatePreview">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary btn-large">
                            <span>Add Employee & Create Account</span>
                        </button>
                        <a href="view_employees.php" class="btn-secondary btn-large">
                            <span>Cancel</span>
                        </a>
                    </div>
                </form>
            </div>

        </main>

    </div>

    <script src="../../accests/js/main.js"></script>
    <script src="../../accests/js/validation.js"></script>
    <script src="../../accests/js/employee.js"></script>
    <script>
        // Sidebar Toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Auto-generate username suggestion from employee name
        document.getElementById('employee_name').addEventListener('blur', function() {
            const usernameField = document.getElementById('emp_username');
            if (!usernameField.value) {
                const name = this.value.trim().toLowerCase();
                const firstName = name.split(' ')[0];
                usernameField.value = firstName.replace(/[^a-z0-9]/g, '');
            }
        });

        // Show/hide password toggle
        const passwordInput = document.getElementById('emp_password');
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.textContent = '👁️';
        toggleBtn.style.cssText = 'position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 20px;';
        
        const passwordGroup = passwordInput.parentElement;
        passwordGroup.style.position = 'relative';
        passwordGroup.appendChild(toggleBtn);
        
        toggleBtn.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                this.textContent = '👁️';
            }
        });
    </script>
</body>
</html>