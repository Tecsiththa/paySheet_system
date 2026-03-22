<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();
$username = $_SESSION['username'];
$company_name = $_SESSION['company_name'];

// Get employee ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('error', 'Invalid employee ID');
    redirect('view_employees.php');
}

$employee_id = clean($conn, $_GET['id']);

// Fetch employee details
$query = "SELECT * FROM employees WHERE employee_id = '$employee_id' AND company_id = '$company_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    setMessage('error', 'Employee not found');
    redirect('view_employees.php');
}

$employee = mysqli_fetch_assoc($result);

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
    $status = clean($conn, $_POST['status']);
    
    $errors = [];
    
    // Validation
    if (empty($employee_name)) $errors[] = "Employee name is required";
    if (empty($employee_nic)) $errors[] = "NIC is required";
    if (empty($employee_email)) $errors[] = "Email is required";
    if (empty($basic_salary) || !is_numeric($basic_salary)) $errors[] = "Valid salary is required";
    
    // Check NIC uniqueness (excluding current employee)
    $check_nic = mysqli_query($conn, "SELECT employee_id FROM employees WHERE employee_nic = '$employee_nic' AND employee_id != '$employee_id'");
    if (mysqli_num_rows($check_nic) > 0) {
        $errors[] = "This NIC is already registered";
    }
    
    // Check Email uniqueness (excluding current employee)
    $check_email = mysqli_query($conn, "SELECT employee_id FROM employees WHERE employee_email = '$employee_email' AND employee_id != '$employee_id'");
    if (mysqli_num_rows($check_email) > 0) {
        $errors[] = "This email is already registered";
    }
    
    if (empty($errors)) {
        $update_query = "UPDATE employees SET 
                        employee_name = '$employee_name',
                        employee_nic = '$employee_nic',
                        employee_address = '$employee_address',
                        employee_phone = '$employee_phone',
                        employee_email = '$employee_email',
                        position = '$position',
                        department = '$department',
                        basic_salary = '$basic_salary',
                        joining_date = '$joining_date',
                        status = '$status'
                        WHERE employee_id = '$employee_id'";
        
        if (mysqli_query($conn, $update_query)) {
            setMessage('success', 'Employee updated successfully!');
            redirect('view_employees.php');
        } else {
            $errors[] = "Failed to update employee. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee - PaySheetPro</title>
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
                    <h1 class="page-title">Edit Employee</h1>
                    <p class="page-subtitle">Update employee information</p>
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
                                       value="<?php echo htmlspecialchars($employee['employee_name']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="employee_nic" class="form-label">
                                    <span class="label-icon">🆔</span>
                                    NIC Number
                                </label>
                                <input type="text" id="employee_nic" name="employee_nic" class="form-input" 
                                       placeholder="XXXXXXXXXXV or XXXXXXXXXXXX" required 
                                       value="<?php echo htmlspecialchars($employee['employee_nic']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="employee_phone" class="form-label">
                                    <span class="label-icon">📞</span>
                                    Phone Number
                                </label>
                                <input type="tel" id="employee_phone" name="employee_phone" class="form-input" 
                                       placeholder="0XXXXXXXXX" required 
                                       value="<?php echo htmlspecialchars($employee['employee_phone']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="employee_email" class="form-label">
                                    <span class="label-icon">✉️</span>
                                    Email Address
                                </label>
                                <input type="email" id="employee_email" name="employee_email" class="form-input" 
                                       placeholder="employee@example.com" required 
                                       value="<?php echo htmlspecialchars($employee['employee_email']); ?>">
                            </div>

                            <div class="form-group form-group-full">
                                <label for="employee_address" class="form-label">
                                    <span class="label-icon">📍</span>
                                    Residential Address
                                </label>
                                <textarea id="employee_address" name="employee_address" class="form-input" 
                                          rows="3" placeholder="Enter complete address" required><?php echo htmlspecialchars($employee['employee_address']); ?></textarea>
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
                                       value="<?php echo htmlspecialchars($employee['position']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="department" class="form-label">
                                    <span class="label-icon">🏢</span>
                                    Department
                                </label>
                                <input type="text" id="department" name="department" class="form-input" 
                                       placeholder="e.g., IT Department" required 
                                       value="<?php echo htmlspecialchars($employee['department']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="basic_salary" class="form-label">
                                    <span class="label-icon">💰</span>
                                    Basic Salary (LKR)
                                </label>
                                <input type="text" id="basic_salary" name="basic_salary" class="form-input format-number" 
                                       placeholder="50000.00" required 
                                       value="<?php echo number_format($employee['basic_salary'], 2); ?>">
                            </div>

                            <div class="form-group">
                                <label for="joining_date" class="form-label">
                                    <span class="label-icon">📅</span>
                                    Joining Date
                                </label>
                                <input type="date" id="joining_date" name="joining_date" class="form-input" 
                                       required value="<?php echo $employee['joining_date']; ?>">
                            </div>

                            <div class="form-group">
                                <label for="status" class="form-label">
                                    <span class="label-icon">🔄</span>
                                    Status
                                </label>
                                <select id="status" name="status" class="form-input" required>
                                    <option value="active" <?php echo ($employee['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($employee['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary btn-large">
                            <span>Update Employee</span>
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