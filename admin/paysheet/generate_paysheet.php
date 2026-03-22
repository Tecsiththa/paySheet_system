<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();
$username = $_SESSION['username'];
$company_name = $_SESSION['company_name'];

// Get current month and year or from request
$selected_month = isset($_POST['month']) ? clean($conn, $_POST['month']) : date('n');
$selected_year = isset($_POST['year']) ? clean($conn, $_POST['year']) : date('Y');

// Fetch all active employees
$employees_query = "SELECT * FROM employees WHERE company_id = '$company_id' AND status = 'active' ORDER BY employee_name";
$employees_result = mysqli_query($conn, $employees_query);

// Check if paysheets already generated for this month
$check_query = "SELECT COUNT(*) as count FROM paysheets p
                JOIN employees e ON p.employee_id = e.employee_id
                WHERE e.company_id = '$company_id' AND p.month = '$selected_month' AND p.year = '$selected_year'";
$check_result = mysqli_query($conn, $check_query);
$already_generated = mysqli_fetch_assoc($check_result)['count'];

// Handle Generate All Paysheets
if (isset($_POST['generate_all'])) {
    $success_count = 0;
    $error_count = 0;
    
    // Loop through all employees
    mysqli_data_seek($employees_result, 0); // Reset pointer
    while ($employee = mysqli_fetch_assoc($employees_result)) {
        $result = generatePaysheet($conn, $employee['employee_id'], $selected_month, $selected_year);
        if ($result['success']) {
            $success_count++;
        } else {
            $error_count++;
        }
    }
    
    if ($success_count > 0) {
        setMessage('success', "Successfully generated $success_count paysheet(s)!");
    }
    if ($error_count > 0) {
        setMessage('warning', "$error_count paysheet(s) could not be generated (already exists or error)");
    }
    
    redirect('generate_paysheet.php?month=' . $selected_month . '&year=' . $selected_year);
}

// Paysheet generation function
function generatePaysheet($conn, $employee_id, $month, $year) {
    // Check if already exists
    $check = mysqli_query($conn, "SELECT paysheet_id FROM paysheets WHERE employee_id = '$employee_id' AND month = '$month' AND year = '$year'");
    if (mysqli_num_rows($check) > 0) {
        return ['success' => false, 'message' => 'Already exists'];
    }
    
    // Get employee details
    $emp_query = "SELECT * FROM employees WHERE employee_id = '$employee_id'";
    $emp_result = mysqli_query($conn, $emp_query);
    $employee = mysqli_fetch_assoc($emp_result);
    $basic_salary = $employee['basic_salary'];
    
    // Get OT for this month
    $ot_query = "SELECT * FROM overtime WHERE employee_id = '$employee_id' AND month = '$month' AND year = '$year'";
    $ot_result = mysqli_query($conn, $ot_query);
    $ot_payment = 0;
    if (mysqli_num_rows($ot_result) > 0) {
        $ot = mysqli_fetch_assoc($ot_result);
        $ot_payment = $ot['ot_payment'];
    }
    
    // Get Allowances
    $allowance_query = "SELECT * FROM allowances WHERE employee_id = '$employee_id' AND month = '$month' AND year = '$year'";
    $allowance_result = mysqli_query($conn, $allowance_query);
    $travel_allowance = 0;
    $food_allowance = 0;
    if (mysqli_num_rows($allowance_result) > 0) {
        $allowance = mysqli_fetch_assoc($allowance_result);
        $travel_allowance = $allowance['travel_allowance'];
        $food_allowance = $allowance['food_allowance'];
    }
    
    // Calculate Total Earnings
    $total_earnings = $basic_salary + $ot_payment + $travel_allowance + $food_allowance;
    
    // Calculate EPF (12%)
    $epf_deduction = $basic_salary * 0.12;
    
    // Calculate ETF (3%)
    $etf_deduction = $basic_salary * 0.03;
    
    // Calculate APIT Tax
    $apit_tax = calculateAPIT($basic_salary);
    
    // Get Loan Deduction
    $loan_deduction = 0;
    $loan_query = "SELECT * FROM loans WHERE employee_id = '$employee_id' AND status = 'active'";
    $loan_result = mysqli_query($conn, $loan_query);
    if (mysqli_num_rows($loan_result) > 0) {
        $loan = mysqli_fetch_assoc($loan_result);
        $loan_deduction = $loan['monthly_installment'];
        
        // Update loan remaining amount
        $new_remaining = $loan['remaining_amount'] - $loan_deduction;
        if ($new_remaining <= 0) {
            mysqli_query($conn, "UPDATE loans SET remaining_amount = 0, status = 'completed' WHERE loan_id = '" . $loan['loan_id'] . "'");
        } else {
            mysqli_query($conn, "UPDATE loans SET remaining_amount = '$new_remaining' WHERE loan_id = '" . $loan['loan_id'] . "'");
        }
        
        // Record payment
        mysqli_query($conn, "INSERT INTO loan_payments (loan_id, employee_id, payment_amount, payment_month, payment_year) 
                            VALUES ('" . $loan['loan_id'] . "', '$employee_id', '$loan_deduction', '$month', '$year')");
    }
    
    // Get Salary Advance
    $advance_deduction = 0;
    $advance_query = "SELECT * FROM salary_advances WHERE employee_id = '$employee_id' AND month = '$month' AND year = '$year' AND status = 'pending'";
    $advance_result = mysqli_query($conn, $advance_query);
    if (mysqli_num_rows($advance_result) > 0) {
        $advance = mysqli_fetch_assoc($advance_result);
        $advance_deduction = $advance['advance_amount'];
        
        // Mark as deducted
        mysqli_query($conn, "UPDATE salary_advances SET status = 'deducted' WHERE advance_id = '" . $advance['advance_id'] . "'");
    }
    
    // Get Unapproved Leave Deduction
    $unapproved_deduction = 0;
    $unapproved_query = "SELECT SUM(deduction_amount) as total FROM unapproved_leaves 
                        WHERE employee_id = '$employee_id' AND month = '$month' AND year = '$year'";
    $unapproved_result = mysqli_query($conn, $unapproved_query);
    if (mysqli_num_rows($unapproved_result) > 0) {
        $unapproved = mysqli_fetch_assoc($unapproved_result);
        $unapproved_deduction = $unapproved['total'] ?? 0;
    }
    
    // Total Deductions
    $total_deductions = $epf_deduction + $etf_deduction + $apit_tax + $loan_deduction + $advance_deduction + $unapproved_deduction;
    
    // Net Salary
    $net_salary = $total_earnings - $total_deductions;
    
    // Insert Paysheet
    $insert_query = "INSERT INTO paysheets 
                    (employee_id, month, year, basic_salary, ot_payment, travel_allowance, food_allowance, 
                     total_earnings, epf_deduction, etf_deduction, apit_tax, loan_deduction, advance_deduction, 
                     unapproved_leave_deduction, total_deductions, net_salary)
                    VALUES 
                    ('$employee_id', '$month', '$year', '$basic_salary', '$ot_payment', '$travel_allowance', '$food_allowance',
                     '$total_earnings', '$epf_deduction', '$etf_deduction', '$apit_tax', '$loan_deduction', '$advance_deduction',
                     '$unapproved_deduction', '$total_deductions', '$net_salary')";
    
    if (mysqli_query($conn, $insert_query)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'message' => 'Database error'];
    }
}

// APIT Tax Calculation Function
function calculateAPIT($monthly_income) {
    $tax = 0;
    
    if ($monthly_income <= 100000) {
        return 0;
    }
    
    $taxable = $monthly_income - 100000;
    
    // Tax slabs
    $slabs = [
        ['limit' => 41667, 'rate' => 0.06],
        ['limit' => 41667, 'rate' => 0.12],
        ['limit' => 41667, 'rate' => 0.18],
        ['limit' => 41667, 'rate' => 0.24],
        ['limit' => 41667, 'rate' => 0.30],
        ['limit' => PHP_INT_MAX, 'rate' => 0.36]
    ];
    
    foreach ($slabs as $slab) {
        if ($taxable > 0) {
            $taxable_in_slab = min($taxable, $slab['limit']);
            $tax += $taxable_in_slab * $slab['rate'];
            $taxable -= $taxable_in_slab;
        } else {
            break;
        }
    }
    
    return round($tax, 2);
}

// Get paysheet status for each employee
mysqli_data_seek($employees_result, 0); // Reset pointer
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Paysheet - PaySheetPro</title>
    <link rel="stylesheet" href="../../accests/css/style.css">
    <link rel="stylesheet" href="../../accests/css/dashboard.css">
    <link rel="stylesheet" href="../../accests/css/paysheet.css">
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
                    <li class="nav-item active">
                        <a href="generate_paysheet.php" class="nav-link">
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
                    <h1 class="page-title">Generate Paysheets</h1>
                    <p class="page-subtitle">Select month and generate employee paysheets</p>
                </div>
                <div class="page-actions">
                    <a href="paysheet_history.php" class="btn-secondary">
                        <span>View History</span>
                    </a>
                </div>
            </div>

            <?php displayMessage(); ?>

            <!-- Month Selection -->
            <div class="month-selector-card">
                <form method="POST" action="">
                    <div class="month-selector-grid">
                        <div class="form-group">
                            <label for="month" class="form-label">
                                <span class="label-icon">📅</span>
                                Select Month
                            </label>
                            <select name="month" id="month" class="form-input" onchange="this.form.submit()">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?php echo $m; ?>" <?php echo ($selected_month == $m) ? 'selected' : ''; ?>>
                                        <?php echo getMonthName($m); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="year" class="form-label">
                                <span class="label-icon">📅</span>
                                Select Year
                            </label>
                            <select name="year" id="year" class="form-input" onchange="this.form.submit()">
                                <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected' : ''; ?>>
                                        <?php echo $y; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="opacity: 0;">Action</label>
                            <button type="submit" name="generate_all" class="btn-primary btn-generate-all" 
                                    onclick="return confirm('Generate paysheets for all employees for <?php echo getMonthName($selected_month) . ' ' . $selected_year; ?>?')">
                                <span>💰 Generate All Paysheets</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Current Selection Info -->
            <div class="selection-info">
                <h3>Paysheets for: <span class="highlight"><?php echo getMonthName($selected_month) . ' ' . $selected_year; ?></span></h3>
                <?php if ($already_generated > 0): ?>
                    <div class="alert alert-info">
                        <strong>Note:</strong> <?php echo $already_generated; ?> paysheet(s) already generated for this month.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Employees List -->
            <div class="employees-paysheet-grid">
                <?php while ($employee = mysqli_fetch_assoc($employees_result)): 
                    // Check if paysheet exists for this employee
                    $check_emp = mysqli_query($conn, "SELECT * FROM paysheets WHERE employee_id = '" . $employee['employee_id'] . "' AND month = '$selected_month' AND year = '$selected_year'");
                    $paysheet_exists = mysqli_num_rows($check_emp) > 0;
                    $paysheet = $paysheet_exists ? mysqli_fetch_assoc($check_emp) : null;
                ?>
                    <div class="employee-paysheet-card <?php echo $paysheet_exists ? 'generated' : 'pending'; ?>">
                        <div class="employee-paysheet-header">
                            <div class="employee-info-paysheet">
                                <div class="employee-avatar-paysheet">
                                    <?php echo strtoupper(substr($employee['employee_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="employee-name-paysheet">
                                        <?php echo htmlspecialchars($employee['employee_name']); ?>
                                    </div>
                                    <div class="employee-position-paysheet">
                                        <?php echo htmlspecialchars($employee['position']); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($paysheet_exists): ?>
                                <span class="status-badge status-generated">✓ Generated</span>
                            <?php else: ?>
                                <span class="status-badge status-pending">⏳ Pending</span>
                            <?php endif; ?>
                        </div>

                        <div class="employee-paysheet-body">
                            <div class="paysheet-detail">
                                <span class="detail-label">Basic Salary:</span>
                                <span class="detail-value"><?php echo formatCurrency($employee['basic_salary']); ?></span>
                            </div>
                            
                            <?php if ($paysheet_exists): ?>
                                <div class="paysheet-detail">
                                    <span class="detail-label">Net Salary:</span>
                                    <span class="detail-value highlight-amount"><?php echo formatCurrency($paysheet['net_salary']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="employee-paysheet-actions">
                            <?php if ($paysheet_exists): ?>
                                <a href="view_paysheet.php?id=<?php echo $paysheet['paysheet_id']; ?>" class="btn-view">
                                    <span>👁️ View Details</span>
                                </a>
                                <a href="generate_pdf.php?id=<?php echo $paysheet['paysheet_id']; ?>" class="btn-pdf" target="_blank">
                                    <span>📄 Download PDF</span>
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Generate paysheet to view details</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        </main>

    </div>

    <script src="../../accests/js/main.js"></script>
    <script>
        // Sidebar Toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>