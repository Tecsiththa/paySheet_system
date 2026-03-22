<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();
$username = $_SESSION['username'];
$company_name = $_SESSION['company_name'];

// Get paysheet ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('error', 'Invalid paysheet ID');
    redirect('generate_paysheet.php');
}

$paysheet_id = clean($conn, $_GET['id']);

// Fetch paysheet details with employee info
$query = "SELECT p.*, e.employee_name, e.employee_nic, e.position, e.department, e.employee_email
          FROM paysheets p
          JOIN employees e ON p.employee_id = e.employee_id
          WHERE p.paysheet_id = '$paysheet_id' AND e.company_id = '$company_id'";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    setMessage('error', 'Paysheet not found');
    redirect('generate_paysheet.php');
}

$paysheet = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Paysheet - PaySheetPro</title>
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
                    <h1 class="page-title">Paysheet Details</h1>
                    <p class="page-subtitle"><?php echo getMonthName($paysheet['month']) . ' ' . $paysheet['year']; ?></p>
                </div>
                <div class="page-actions">
                    <a href="generate_pdf.php?id=<?php echo $paysheet_id; ?>" class="btn-primary" target="_blank">
                        <span>📄 Download PDF</span>
                    </a>
                    <a href="generate_paysheet.php" class="btn-secondary">
                        <span>← Back</span>
                    </a>
                </div>
            </div>

            <!-- Paysheet View -->
            <div class="paysheet-view-container">
                
                <!-- Employee Info -->
                <div class="paysheet-section">
                    <div class="section-header">
                        <h3 class="section-title">Employee Information</h3>
                    </div>
                    <div class="employee-info-grid">
                        <div class="info-item">
                            <span class="info-label">Employee Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($paysheet['employee_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Employee ID:</span>
                            <span class="info-value"><?php echo $paysheet['employee_id']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">NIC:</span>
                            <span class="info-value"><?php echo htmlspecialchars($paysheet['employee_nic']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Position:</span>
                            <span class="info-value"><?php echo htmlspecialchars($paysheet['position']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Department:</span>
                            <span class="info-value"><?php echo htmlspecialchars($paysheet['department']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Month:</span>
                            <span class="info-value"><?php echo getMonthName($paysheet['month']) . ' ' . $paysheet['year']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Earnings Section -->
                <div class="paysheet-section earnings-section">
                    <div class="section-header">
                        <h3 class="section-title">💰 Earnings</h3>
                    </div>
                    <div class="amount-grid">
                        <div class="amount-item">
                            <span class="amount-label">Basic Salary</span>
                            <span class="amount-value"><?php echo formatCurrency($paysheet['basic_salary']); ?></span>
                        </div>
                        <div class="amount-item">
                            <span class="amount-label">OT Payment</span>
                            <span class="amount-value"><?php echo formatCurrency($paysheet['ot_payment']); ?></span>
                        </div>
                        <div class="amount-item">
                            <span class="amount-label">Travel Allowance</span>
                            <span class="amount-value"><?php echo formatCurrency($paysheet['travel_allowance']); ?></span>
                        </div>
                        <div class="amount-item">
                            <span class="amount-label">Food Allowance</span>
                            <span class="amount-value"><?php echo formatCurrency($paysheet['food_allowance']); ?></span>
                        </div>
                    </div>
                    <div class="amount-total">
                        <span class="total-label">Total Earnings:</span>
                        <span class="total-value earnings-total"><?php echo formatCurrency($paysheet['total_earnings']); ?></span>
                    </div>
                </div>

                <!-- Deductions Section -->
                <div class="paysheet-section deductions-section">
                    <div class="section-header">
                        <h3 class="section-title">➖ Deductions</h3>
                    </div>
                    <div class="amount-grid">
                        <div class="amount-item">
                            <span class="amount-label">EPF (12%)</span>
                            <span class="amount-value negative"><?php echo formatCurrency($paysheet['epf_deduction']); ?></span>
                        </div>
                        <div class="amount-item">
                            <span class="amount-label">ETF (3%)</span>
                            <span class="amount-value negative"><?php echo formatCurrency($paysheet['etf_deduction']); ?></span>
                        </div>
                        <div class="amount-item">
                            <span class="amount-label">APIT Tax</span>
                            <span class="amount-value negative"><?php echo formatCurrency($paysheet['apit_tax']); ?></span>
                        </div>
                        <div class="amount-item">
                            <span class="amount-label">Loan Deduction</span>
                            <span class="amount-value negative"><?php echo formatCurrency($paysheet['loan_deduction']); ?></span>
                        </div>
                        <div class="amount-item">
                            <span class="amount-label">Salary Advance</span>
                            <span class="amount-value negative"><?php echo formatCurrency($paysheet['advance_deduction']); ?></span>
                        </div>
                        <div class="amount-item">
                            <span class="amount-label">Unapproved Leave</span>
                            <span class="amount-value negative"><?php echo formatCurrency($paysheet['unapproved_leave_deduction']); ?></span>
                        </div>
                    </div>
                    <div class="amount-total">
                        <span class="total-label">Total Deductions:</span>
                        <span class="total-value deductions-total negative"><?php echo formatCurrency($paysheet['total_deductions']); ?></span>
                    </div>
                </div>

                <!-- Net Salary -->
                <div class="net-salary-section">
                    <div class="net-salary-content">
                        <span class="net-salary-label">Net Salary</span>
                        <span class="net-salary-value"><?php echo formatCurrency($paysheet['net_salary']); ?></span>
                    </div>
                    <div class="net-salary-note">
                        Amount to be paid to employee
                    </div>
                </div>

                <!-- Generated Date -->
                <div class="paysheet-footer">
                    <p>Generated on: <?php echo formatDate($paysheet['generated_date']); ?></p>
                    <p>Company: <?php echo htmlspecialchars($company_name); ?></p>
                </div>

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