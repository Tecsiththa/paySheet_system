<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();
$username = $_SESSION['username'];
$company_name = $_SESSION['company_name'];

// Fetch all active employees
$employees_query = "SELECT employee_id, employee_name, position, basic_salary FROM employees WHERE company_id = '$company_id' AND status = 'active' ORDER BY employee_name";
$employees_result = mysqli_query($conn, $employees_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = clean($conn, $_POST['employee_id']);
    $advance_amount = clean($conn, str_replace(',', '', $_POST['advance_amount']));
    $month = clean($conn, $_POST['month']);
    $year = clean($conn, $_POST['year']);
    
    $errors = [];
    
    // Validation
    if (empty($employee_id)) $errors[] = "Please select an employee";
    if (empty($advance_amount) || !is_numeric($advance_amount) || $advance_amount <= 0) {
        $errors[] = "Valid advance amount is required";
    }
    if (empty($month)) $errors[] = "Please select a month";
    if (empty($year)) $errors[] = "Please select a year";
    
    // Get employee's basic salary for validation
    $emp_query = mysqli_query($conn, "SELECT basic_salary FROM employees WHERE employee_id = '$employee_id'");
    if (mysqli_num_rows($emp_query) > 0) {
        $emp = mysqli_fetch_assoc($emp_query);
        $basic_salary = $emp['basic_salary'];
        
        // Check if advance is more than 50% of salary
        if ($advance_amount > ($basic_salary * 0.5)) {
            $errors[] = "Advance amount cannot exceed 50% of basic salary (LKR " . number_format($basic_salary * 0.5, 2) . ")";
        }
    }
    
    // Check if employee already has a pending advance for this month
    $check_advance = mysqli_query($conn, "SELECT advance_id FROM salary_advances 
                                          WHERE employee_id = '$employee_id' AND month = '$month' AND year = '$year' AND status = 'pending'");
    if (mysqli_num_rows($check_advance) > 0) {
        $errors[] = "This employee already has a pending salary advance for the selected month";
    }
    
    if (empty($errors)) {
        $insert_query = "INSERT INTO salary_advances (employee_id, advance_amount, month, year, status)
                        VALUES ('$employee_id', '$advance_amount', '$month', '$year', 'pending')";
        
        if (mysqli_query($conn, $insert_query)) {
            setMessage('success', 'Salary advance added successfully!');
            redirect('view_advances.php');
        } else {
            $errors[] = "Failed to add salary advance. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Salary Advance - PaySheetPro</title>
    <link rel="stylesheet" href="../../accests/css/style.css">
    <link rel="stylesheet" href="../../accests/css/dashboard.css">
    <link rel="stylesheet" href="../../accests/css/loan.css">
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
                    <li class="nav-item">
                        <a href="../employees/view_employees.php" class="nav-link">
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
                    <li class="nav-item active">
                        <a href="view_advances.php" class="nav-link">
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
                    <h1 class="page-title">Add Salary Advance</h1>
                    <p class="page-subtitle">Register a salary advance for an employee</p>
                </div>
                <div class="page-actions">
                    <a href="view_advances.php" class="btn-secondary">
                        <span>← Back to Advances</span>
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

            <!-- Info Alert -->
            <div class="alert alert-info">
                <strong>Note:</strong> Salary advances will be automatically deducted when generating the paysheet for the selected month. 
                Maximum advance is 50% of employee's basic salary.
            </div>

            <div class="loan-form-card">
                <form method="POST" action="" id="advanceForm">
                    
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <span class="section-icon">⚡</span>
                            Advance Information
                        </h3>
                        
                        <div class="form-grid">
                            <div class="form-group form-group-full">
                                <label for="employee_id" class="form-label">
                                    <span class="label-icon">👤</span>
                                    Select Employee
                                </label>
                                <select id="employee_id" name="employee_id" class="form-input" required onchange="updateSalaryInfo()">
                                    <option value="">-- Select Employee --</option>
                                    <?php while ($employee = mysqli_fetch_assoc($employees_result)): ?>
                                        <option value="<?php echo $employee['employee_id']; ?>" 
                                                data-salary="<?php echo $employee['basic_salary']; ?>"
                                                <?php echo (isset($employee_id) && $employee_id == $employee['employee_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($employee['employee_name']) . ' - ' . htmlspecialchars($employee['position']); ?> 
                                            (Salary: <?php echo formatCurrency($employee['basic_salary']); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="advance_amount" class="form-label">
                                    <span class="label-icon">💰</span>
                                    Advance Amount (LKR)
                                </label>
                                <input type="text" id="advance_amount" name="advance_amount" class="form-input format-number" 
                                       placeholder="25000.00" required 
                                       value="<?php echo isset($advance_amount) ? htmlspecialchars($advance_amount) : ''; ?>">
                                <small class="form-hint" id="max_advance_hint">Maximum 50% of basic salary</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <span class="label-icon">📊</span>
                                    Employee's Basic Salary
                                </label>
                                <input type="text" id="employee_salary" class="form-input" readonly 
                                       placeholder="Select employee first" style="background: #f3f4f6;">
                            </div>

                            <div class="form-group">
                                <label for="month" class="form-label">
                                    <span class="label-icon">📅</span>
                                    Deduction Month
                                </label>
                                <select id="month" name="month" class="form-input" required>
                                    <option value="">-- Select Month --</option>
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo $m; ?>" 
                                                <?php echo (isset($month) && $month == $m) ? 'selected' : (date('n') == $m ? 'selected' : ''); ?>>
                                            <?php echo getMonthName($m); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                                <small class="form-hint">Month when amount will be deducted</small>
                            </div>

                            <div class="form-group">
                                <label for="year" class="form-label">
                                    <span class="label-icon">📅</span>
                                    Deduction Year
                                </label>
                                <select id="year" name="year" class="form-input" required>
                                    <?php for ($y = date('Y'); $y <= date('Y') + 1; $y++): ?>
                                        <option value="<?php echo $y; ?>" 
                                                <?php echo (isset($year) && $year == $y) ? 'selected' : (date('Y') == $y ? 'selected' : ''); ?>>
                                            <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="loan-calculation-preview" id="advancePreview" style="display: none;">
                        <h4 class="preview-title">Advance Summary</h4>
                        <div class="preview-grid">
                            <div class="preview-item">
                                <span class="preview-label">Advance Amount:</span>
                                <span class="preview-value" id="previewAmount">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Deduction Date:</span>
                                <span class="preview-value" id="previewDate">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Percentage of Salary:</span>
                                <span class="preview-value" id="previewPercentage">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary btn-large">
                            <span>Add Salary Advance</span>
                            <span class="btn-icon">✓</span>
                        </button>
                        <a href="view_advances.php" class="btn-secondary btn-large">
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

        // Update salary info when employee is selected
        function updateSalaryInfo() {
            const employeeSelect = document.getElementById('employee_id');
            const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
            const salary = selectedOption.getAttribute('data-salary');
            
            if (salary) {
                const maxAdvance = parseFloat(salary) * 0.5;
                document.getElementById('employee_salary').value = 'LKR ' + formatNumber(parseFloat(salary).toFixed(2));
                document.getElementById('max_advance_hint').textContent = 'Maximum 50% of basic salary (LKR ' + formatNumber(maxAdvance.toFixed(2)) + ')';
                calculateAdvance();
            } else {
                document.getElementById('employee_salary').value = '';
                document.getElementById('max_advance_hint').textContent = 'Maximum 50% of basic salary';
            }
        }

        // Calculate and show preview
        function calculateAdvance() {
            const employeeSelect = document.getElementById('employee_id');
            const selectedOption = employeeSelect.options[employeeSelect.selectedIndex];
            const salary = parseFloat(selectedOption.getAttribute('data-salary')) || 0;
            const advanceAmount = parseFloat(document.getElementById('advance_amount').value.replace(/,/g, '')) || 0;
            const month = document.getElementById('month').value;
            const year = document.getElementById('year').value;
            
            if (advanceAmount > 0 && salary > 0 && month && year) {
                const percentage = (advanceAmount / salary) * 100;
                const monthName = document.getElementById('month').options[document.getElementById('month').selectedIndex].text;
                
                document.getElementById('advancePreview').style.display = 'block';
                document.getElementById('previewAmount').textContent = 'LKR ' + formatNumber(advanceAmount.toFixed(2));
                document.getElementById('previewDate').textContent = monthName + ' ' + year;
                document.getElementById('previewPercentage').textContent = percentage.toFixed(1) + '%';
                
                // Highlight if over 50%
                if (percentage > 50) {
                    document.getElementById('previewPercentage').style.color = '#ef4444';
                    document.getElementById('advance_amount').style.borderColor = '#ef4444';
                } else {
                    document.getElementById('previewPercentage').style.color = '#10b981';
                    document.getElementById('advance_amount').style.borderColor = '#10b981';
                }
            } else {
                document.getElementById('advancePreview').style.display = 'none';
            }
        }

        document.getElementById('advance_amount').addEventListener('input', calculateAdvance);
        document.getElementById('month').addEventListener('change', calculateAdvance);
        document.getElementById('year').addEventListener('change', calculateAdvance);
    </script>
</body>
</html>