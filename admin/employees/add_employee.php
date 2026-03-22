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
    $employee_address = clean($conn, $_POST['employee_address']);
    $employee_phone = clean($conn, $_POST['employee_phone']);
    $employee_email = clean($conn, $_POST['employee_email']);
    $position = clean($conn, $_POST['position']);
    $department = clean($conn, $_POST['department']);
    $basic_salary = clean($conn, str_replace(',', '', $_POST['basic_salary']));
    $joining_date = clean($conn, $_POST['joining_date']);
    
    $errors = [];
    
    // Validation
    if (empty($employee_name)) $errors[] = "Employee name is required";
    if (empty($employee_nic)) $errors[] = "NIC is required";
    if (empty($employee_email)) $errors[] = "Email is required";
    if (empty($basic_salary) || !is_numeric($basic_salary)) $errors[] = "Valid salary is required";
    
    // Check NIC uniqueness
    $check_nic = mysqli_query($conn, "SELECT employee_id FROM employees WHERE employee_nic = '$employee_nic'");
    if (mysqli_num_rows($check_nic) > 0) {
        $errors[] = "This NIC is already registered";
    }
    
    // Check Email uniqueness
    $check_email = mysqli_query($conn, "SELECT employee_id FROM employees WHERE employee_email = '$employee_email'");
    if (mysqli_num_rows($check_email) > 0) {
        $errors[] = "This email is already registered";
    }
    
    if (empty($errors)) {
        $insert_query = "INSERT INTO employees 
                        (company_id, employee_name, employee_nic, employee_address, employee_phone, 
                         employee_email, position, department, basic_salary, joining_date) 
                        VALUES 
                        ('$company_id', '$employee_name', '$employee_nic', '$employee_address', '$employee_phone',
                         '$employee_email', '$position', '$department', '$basic_salary', '$joining_date')";
        
        if (mysqli_query($conn, $insert_query)) {
            $employee_id = mysqli_insert_id($conn);
            
            // Create leave balance for current year
            $current_year = date('Y');
            $leave_balance_query = "INSERT INTO leave_balance (employee_id, year) 
                                   VALUES ('$employee_id', '$current_year')";
            mysqli_query($conn, $leave_balance_query);
            
            setMessage('success', 'Employee added successfully!');
            redirect('view_employees.php');
        } else {
            $errors[] = "Failed to add employee. Please try again.";
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
                    <p class="page-subtitle">Fill in the employee details below</p>
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

            <div class="employee-form-card">
                <form method="POST" action="" id="employeeForm">
                    
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <span class="section-icon">👤</span>
                            Personal Information
                        </h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="employee_name" class="form-label">
                                    <span class="label-icon">👤</span>
                                    Full Name
                                </label>
                                <input type="text" id="employee_name" name="employee_name" class="form-input" 
                                       placeholder="Enter full name" required 
                                       value="<?php echo isset($employee_name) ? htmlspecialchars($employee_name) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="employee_nic" class="form-label">
                                    <span class="label-icon">🆔</span>
                                    NIC Number
                                </label>
                                <input type="text" id="employee_nic" name="employee_nic" class="form-input" 
                                       placeholder="XXXXXXXXXXV or XXXXXXXXXXXX" required 
                                       value="<?php echo isset($employee_nic) ? htmlspecialchars($employee_nic) : ''; ?>">
                                <small class="form-hint">Old format: 123456789V | New format: 199012345678</small>
                            </div>

                            <div class="form-group">
                                <label for="employee_phone" class="form-label">
                                    <span class="label-icon">📞</span>
                                    Phone Number
                                </label>
                                <input type="tel" id="employee_phone" name="employee_phone" class="form-input" 
                                       placeholder="0XXXXXXXXX" required 
                                       value="<?php echo isset($employee_phone) ? htmlspecialchars($employee_phone) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="employee_email" class="form-label">
                                    <span class="label-icon">✉️</span>
                                    Email Address
                                </label>
                                <input type="email" id="employee_email" name="employee_email" class="form-input" 
                                       placeholder="employee@example.com" required 
                                       value="<?php echo isset($employee_email) ? htmlspecialchars($employee_email) : ''; ?>">
                            </div>

                            <div class="form-group form-group-full">
                                <label for="employee_address" class="form-label">
                                    <span class="label-icon">📍</span>
                                    Residential Address
                                </label>
                                <textarea id="employee_address" name="employee_address" class="form-input" 
                                          rows="3" placeholder="Enter complete address" required><?php echo isset($employee_address) ? htmlspecialchars($employee_address) : ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">
                            <span class="section-icon">💼</span>
                            Employment Details
                        </h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="position" class="form-label">
                                    <span class="label-icon">💼</span>
                                    Position
                                </label>
                                <input type="text" id="position" name="position" class="form-input" 
                                       placeholder="e.g., Software Engineer" required 
                                       value="<?php echo isset($position) ? htmlspecialchars($position) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="department" class="form-label">
                                    <span class="label-icon">🏢</span>
                                    Department
                                </label>
                                <input type="text" id="department" name="department" class="form-input" 
                                       placeholder="e.g., IT Department" required 
                                       value="<?php echo isset($department) ? htmlspecialchars($department) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="basic_salary" class="form-label">
                                    <span class="label-icon">💰</span>
                                    Basic Salary (LKR)
                                </label>
                                <input type="text" id="basic_salary" name="basic_salary" class="form-input format-number" 
                                       placeholder="50000.00" required 
                                       value="<?php echo isset($basic_salary) ? htmlspecialchars($basic_salary) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="joining_date" class="form-label">
                                    <span class="label-icon">📅</span>
                                    Joining Date
                                </label>
                                <input type="date" id="joining_date" name="joining_date" class="form-input" 
                                       required max="<?php echo date('Y-m-d'); ?>"
                                       value="<?php echo isset($joining_date) ? htmlspecialchars($joining_date) : date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary btn-large">
                            <span>Add Employee</span>
                            <span class="btn-icon">✓</span>
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
    <script>
        // Sidebar Toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>