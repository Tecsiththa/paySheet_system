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
$employees_query = "SELECT employee_id, employee_name, position FROM employees WHERE company_id = '$company_id' AND status = 'active' ORDER BY employee_name";
$employees_result = mysqli_query($conn, $employees_query);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = clean($conn, $_POST['employee_id']);
    $loan_amount = clean($conn, str_replace(',', '', $_POST['loan_amount']));
    $monthly_installment = clean($conn, str_replace(',', '', $_POST['monthly_installment']));
    $start_date = clean($conn, $_POST['start_date']);
    
    $errors = [];
    
    // Validation
    if (empty($employee_id)) $errors[] = "Please select an employee";
    if (empty($loan_amount) || !is_numeric($loan_amount) || $loan_amount <= 0) {
        $errors[] = "Valid loan amount is required";
    }
    if (empty($monthly_installment) || !is_numeric($monthly_installment) || $monthly_installment <= 0) {
        $errors[] = "Valid monthly installment is required";
    }
    if ($monthly_installment > $loan_amount) {
        $errors[] = "Monthly installment cannot be greater than loan amount";
    }
    
    // Check if employee already has an active loan
    $check_loan = mysqli_query($conn, "SELECT loan_id FROM loans WHERE employee_id = '$employee_id' AND status = 'active'");
    if (mysqli_num_rows($check_loan) > 0) {
        $errors[] = "This employee already has an active loan";
    }
    
    if (empty($errors)) {
        $insert_query = "INSERT INTO loans (employee_id, loan_amount, monthly_installment, remaining_amount, start_date, status)
                        VALUES ('$employee_id', '$loan_amount', '$monthly_installment', '$loan_amount', '$start_date', 'active')";
        
        if (mysqli_query($conn, $insert_query)) {
            setMessage('success', 'Loan added successfully!');
            redirect('view_loans.php');
        } else {
            $errors[] = "Failed to add loan. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Loan - PaySheetPro</title>
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
                    <li class="nav-item active">
                        <a href="view_loans.php" class="nav-link">
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
                    <h1 class="page-title">Add Employee Loan</h1>
                    <p class="page-subtitle">Register a new loan for an employee</p>
                </div>
                <div class="page-actions">
                    <a href="view_loans.php" class="btn-secondary">
                        <span>← Back to Loans</span>
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

            <div class="loan-form-card">
                <form method="POST" action="" id="loanForm">
                    
                    <div class="form-section">
                        <h3 class="form-section-title">
                            <span class="section-icon">💳</span>
                            Loan Information
                        </h3>
                        
                        <div class="form-grid">
                            <div class="form-group form-group-full">
                                <label for="employee_id" class="form-label">
                                    <span class="label-icon">👤</span>
                                    Select Employee
                                </label>
                                <select id="employee_id" name="employee_id" class="form-input" required>
                                    <option value="">-- Select Employee --</option>
                                    <?php while ($employee = mysqli_fetch_assoc($employees_result)): ?>
                                        <option value="<?php echo $employee['employee_id']; ?>" 
                                                <?php echo (isset($employee_id) && $employee_id == $employee['employee_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($employee['employee_name']) . ' - ' . htmlspecialchars($employee['position']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="loan_amount" class="form-label">
                                    <span class="label-icon">💰</span>
                                    Loan Amount (LKR)
                                </label>
                                <input type="text" id="loan_amount" name="loan_amount" class="form-input format-number" 
                                       placeholder="100000.00" required 
                                       value="<?php echo isset($loan_amount) ? htmlspecialchars($loan_amount) : ''; ?>">
                                <small class="form-hint">Total amount to be loaned to employee</small>
                            </div>

                            <div class="form-group">
                                <label for="monthly_installment" class="form-label">
                                    <span class="label-icon">📅</span>
                                    Monthly Installment (LKR)
                                </label>
                                <input type="text" id="monthly_installment" name="monthly_installment" class="form-input format-number" 
                                       placeholder="10000.00" required 
                                       value="<?php echo isset($monthly_installment) ? htmlspecialchars($monthly_installment) : ''; ?>">
                                <small class="form-hint">Amount to deduct from salary each month</small>
                            </div>

                            <div class="form-group">
                                <label for="start_date" class="form-label">
                                    <span class="label-icon">📆</span>
                                    Start Date
                                </label>
                                <input type="date" id="start_date" name="start_date" class="form-input" 
                                       required value="<?php echo isset($start_date) ? htmlspecialchars($start_date) : date('Y-m-d'); ?>">
                                <small class="form-hint">When deductions should start</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    <span class="label-icon">🔢</span>
                                    Estimated Months
                                </label>
                                <input type="text" id="estimated_months" class="form-input" readonly 
                                       placeholder="Calculated automatically" style="background: #f3f4f6;">
                                <small class="form-hint">Approximate payback period</small>
                            </div>
                        </div>
                    </div>

                    <div class="loan-calculation-preview" id="loanPreview" style="display: none;">
                        <h4 class="preview-title">Loan Summary</h4>
                        <div class="preview-grid">
                            <div class="preview-item">
                                <span class="preview-label">Total Loan Amount:</span>
                                <span class="preview-value" id="previewAmount">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Monthly Deduction:</span>
                                <span class="preview-value" id="previewInstallment">-</span>
                            </div>
                            <div class="preview-item">
                                <span class="preview-label">Payback Period:</span>
                                <span class="preview-value" id="previewMonths">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary btn-large">
                            <span>Add Loan</span>
                            <span class="btn-icon">✓</span>
                        </button>
                        <a href="view_loans.php" class="btn-secondary btn-large">
                            <span>Cancel</span>
                        </a>
                    </div>
                </form>
            </div>

        </main>

    </div>

    <script src="../../accests/js/main.js"></script>
    <script src="../../accests/js/validation.js"></script>
    <script src="../../accests/js/loan.js"></script>
    <script>
        // Sidebar Toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Calculate estimated months
        function calculateLoan() {
            const loanAmount = parseFloat(document.getElementById('loan_amount').value.replace(/,/g, '')) || 0;
            const monthlyInstallment = parseFloat(document.getElementById('monthly_installment').value.replace(/,/g, '')) || 0;
            
            if (loanAmount > 0 && monthlyInstallment > 0) {
                const months = Math.ceil(loanAmount / monthlyInstallment);
                document.getElementById('estimated_months').value = months + ' months';
                
                // Show preview
                document.getElementById('loanPreview').style.display = 'block';
                document.getElementById('previewAmount').textContent = 'LKR ' + formatNumber(loanAmount.toFixed(2));
                document.getElementById('previewInstallment').textContent = 'LKR ' + formatNumber(monthlyInstallment.toFixed(2));
                document.getElementById('previewMonths').textContent = months + ' months';
            } else {
                document.getElementById('estimated_months').value = '';
                document.getElementById('loanPreview').style.display = 'none';
            }
        }

        document.getElementById('loan_amount').addEventListener('input', calculateLoan);
        document.getElementById('monthly_installment').addEventListener('input', calculateLoan);
    </script>
</body>
</html>