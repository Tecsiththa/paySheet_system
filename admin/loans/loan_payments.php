<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();
$username = $_SESSION['username'];
$company_name = $_SESSION['company_name'];

// Get loan ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('error', 'Invalid loan ID');
    redirect('view_loans.php');
}

$loan_id = clean($conn, $_GET['id']);

// Fetch loan details
$loan_query = "SELECT l.*, e.employee_name, e.position, e.department
               FROM loans l
               JOIN employees e ON l.employee_id = e.employee_id
               WHERE l.loan_id = '$loan_id' AND e.company_id = '$company_id'";

$loan_result = mysqli_query($conn, $loan_query);

if (mysqli_num_rows($loan_result) == 0) {
    setMessage('error', 'Loan not found');
    redirect('view_loans.php');
}

$loan = mysqli_fetch_assoc($loan_result);

// Fetch payment history
$payments_query = "SELECT * FROM loan_payments WHERE loan_id = '$loan_id' ORDER BY payment_date DESC";
$payments_result = mysqli_query($conn, $payments_query);

// Calculate statistics
$paid_amount = $loan['loan_amount'] - $loan['remaining_amount'];
$progress_percentage = ($loan['loan_amount'] > 0) ? ($paid_amount / $loan['loan_amount']) * 100 : 0;
$total_payments = mysqli_num_rows($payments_result);
$remaining_months = ($loan['monthly_installment'] > 0) ? ceil($loan['remaining_amount'] / $loan['monthly_installment']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Payment History - PaySheetPro</title>
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
                    <h1 class="page-title">Loan Payment History</h1>
                    <p class="page-subtitle"><?php echo htmlspecialchars($loan['employee_name']); ?></p>
                </div>
                <div class="page-actions">
                    <a href="view_loans.php" class="btn-secondary">
                        <span>← Back to Loans</span>
                    </a>
                </div>
            </div>

            <!-- Loan Summary Card -->
            <div class="loan-summary-card">
                <div class="summary-header">
                    <h3 class="summary-title">Loan Summary</h3>
                    <?php if ($loan['status'] == 'active'): ?>
                        <span class="status-badge status-active">Active</span>
                    <?php else: ?>
                        <span class="status-badge status-completed">Completed</span>
                    <?php endif; ?>
                </div>

                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">Employee</div>
                        <div class="summary-value"><?php echo htmlspecialchars($loan['employee_name']); ?></div>
                        <div class="summary-meta"><?php echo htmlspecialchars($loan['position']) . ' - ' . htmlspecialchars($loan['department']); ?></div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Total Loan Amount</div>
                        <div class="summary-value"><?php echo formatCurrency($loan['loan_amount']); ?></div>
                        <div class="summary-meta">Original loan amount</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Monthly Installment</div>
                        <div class="summary-value"><?php echo formatCurrency($loan['monthly_installment']); ?></div>
                        <div class="summary-meta">Deducted from salary</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Amount Paid</div>
                        <div class="summary-value positive"><?php echo formatCurrency($paid_amount); ?></div>
                        <div class="summary-meta"><?php echo $total_payments; ?> payments made</div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Remaining Balance</div>
                        <div class="summary-value negative"><?php echo formatCurrency($loan['remaining_amount']); ?></div>
                        <div class="summary-meta">
                            <?php if ($loan['status'] == 'active'): ?>
                                Est. <?php echo $remaining_months; ?> months remaining
                            <?php else: ?>
                                Fully paid
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-label">Start Date</div>
                        <div class="summary-value"><?php echo formatDate($loan['start_date']); ?></div>
                        <div class="summary-meta">Loan initiated</div>
                    </div>
                </div>

                <div class="loan-progress-section">
                    <div class="progress-header">
                        <span class="progress-label">Repayment Progress</span>
                        <span class="progress-percentage"><?php echo round($progress_percentage); ?>%</span>
                    </div>
                    <div class="progress-bar-large">
                        <div class="progress-fill-large" style="width: <?php echo $progress_percentage; ?>%;"></div>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="table-card">
                <div class="card-header">
                    <h3 class="card-title">Payment History</h3>
                </div>
                
                <?php if (mysqli_num_rows($payments_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="payment-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Payment Date</th>
                                    <th>Month</th>
                                    <th>Amount Paid</th>
                                    <th>Balance After Payment</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $count = 1;
                                $running_balance = $loan['loan_amount'];
                                mysqli_data_seek($payments_result, 0); // Reset pointer
                                $payments_array = [];
                                while ($payment = mysqli_fetch_assoc($payments_result)) {
                                    $payments_array[] = $payment;
                                }
                                $payments_array = array_reverse($payments_array); // Reverse to show oldest first
                                
                                foreach ($payments_array as $payment):
                                    $running_balance -= $payment['payment_amount'];
                                ?>
                                    <tr>
                                        <td><?php echo $count++; ?></td>
                                        <td><?php echo formatDate($payment['payment_date']); ?></td>
                                        <td>
                                            <span class="month-badge">
                                                <?php echo getMonthName($payment['payment_month']) . ' ' . $payment['payment_year']; ?>
                                            </span>
                                        </td>
                                        <td class="amount-cell positive"><?php echo formatCurrency($payment['payment_amount']); ?></td>
                                        <td class="amount-cell"><?php echo formatCurrency(max(0, $running_balance)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon">📋</span>
                        <h3>No Payments Yet</h3>
                        <p>Payments will appear here once paysheet generation includes this loan deduction</p>
                    </div>
                <?php endif; ?>
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