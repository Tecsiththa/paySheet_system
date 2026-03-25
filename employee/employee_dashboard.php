<?php
require_once '../config/database.php';

// Check if user is logged in and is employee
if (!isLoggedIn() || isAdmin()) {
    redirect('../auth/login.php');
}

$user_id = getCurrentUserId();
$username = $_SESSION['username'];
$company_name = $_SESSION['company_name'];

// Fetch employee details
$employee_id = $_SESSION['employee_id'];
$emp_query = "SELECT * FROM employees WHERE employee_id = '$employee_id'";
$emp_result = mysqli_query($conn, $emp_query);

if (mysqli_num_rows($emp_result) == 0) {
    setMessage('error', 'Employee record not found');
    redirect('../auth/logout.php');
}

$employee = mysqli_fetch_assoc($emp_result);
$employee_id = $employee['employee_id'];

// Fetch current month paysheet
$current_month = date('n');
$current_year = date('Y');

$paysheet_query = "SELECT * FROM paysheets WHERE employee_id = '$employee_id' AND month = '$current_month' AND year = '$current_year'";
$paysheet_result = mysqli_query($conn, $paysheet_query);
$current_paysheet = mysqli_num_rows($paysheet_result) > 0 ? mysqli_fetch_assoc($paysheet_result) : null;

// Fetch leave balance
$leave_balance_query = "SELECT * FROM leave_balance WHERE employee_id = '$employee_id' AND year = '$current_year'";
$leave_balance_result = mysqli_query($conn, $leave_balance_query);
$leave_balance = mysqli_num_rows($leave_balance_result) > 0 ? mysqli_fetch_assoc($leave_balance_result) : null;

// Fetch recent leave requests
$recent_leaves_query = "SELECT lr.*, lt.leave_name 
                        FROM leave_records lr
                        JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id
                        WHERE lr.employee_id = '$employee_id'
                        ORDER BY lr.applied_date DESC
                        LIMIT 5";
$recent_leaves_result = mysqli_query($conn, $recent_leaves_query);

// Fetch active loans
$loan_query = "SELECT * FROM loans WHERE employee_id = '$employee_id' AND status = 'active'";
$loan_result = mysqli_query($conn, $loan_query);
$active_loan = mysqli_num_rows($loan_result) > 0 ? mysqli_fetch_assoc($loan_result) : null;

// Fetch pending salary advances
$advance_query = "SELECT * FROM salary_advances WHERE employee_id = '$employee_id' AND status = 'pending'";
$advance_result = mysqli_query($conn, $advance_query);
$pending_advance = mysqli_num_rows($advance_result) > 0 ? mysqli_fetch_assoc($advance_result) : null;

// Total leave days used this year
$total_leaves_used = 0;
if ($leave_balance) {
    $initial_annual = 14;
    $initial_casual = 7;
    $initial_sick = 7;
    $total_leaves_used = ($initial_annual - $leave_balance['annual_leave_remaining']) + 
                        ($initial_casual - $leave_balance['casual_leave_remaining']) + 
                        ($initial_sick - $leave_balance['sick_leave_remaining']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - PaySheetPro</title>
    <link rel="stylesheet" href="../accests/css/style.css">
    <link rel="stylesheet" href="../accests/css/dashboard.css">
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
                    <span><?php echo strtoupper(substr($employee['employee_name'], 0, 1)); ?></span>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($employee['employee_name']); ?></span>
                    <span class="user-role">Employee</span>
                </div>
                <a href="../auth/logout.php" class="logout-btn" title="Logout">
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
                    <li class="nav-item active">
                        <a href="employee_dashboard.php" class="nav-link">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="view_paysheet.php" class="nav-link">
                            <span class="nav-icon">💰</span>
                            <span class="nav-text">My Paysheets</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="request_leave.php" class="nav-link">
                            <span class="nav-icon">📅</span>
                            <span class="nav-text">Request Leave</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="leave_balance.php" class="nav-link">
                            <span class="nav-icon">📋</span>
                            <span class="nav-text">Leave Balance</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            
            <div class="page-header">
                <div>
                    <h1 class="page-title">Welcome, <?php echo htmlspecialchars($employee['employee_name']); ?>!</h1>
                    <p class="page-subtitle"><?php echo htmlspecialchars($employee['position']) . ' - ' . htmlspecialchars($employee['department']); ?></p>
                </div>
            </div>

            <?php displayMessage(); ?>

            <!-- Employee Info Card -->
            <div class="employee-profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($employee['employee_name'], 0, 1)); ?>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($employee['employee_name']); ?></h2>
                        <p class="profile-position"><?php echo htmlspecialchars($employee['position']); ?></p>
                        <p class="profile-department"><?php echo htmlspecialchars($employee['department']); ?></p>
                    </div>
                </div>
                <div class="profile-details">
                    <div class="detail-item">
                        <span class="detail-label">Employee ID:</span>
                        <span class="detail-value"><?php echo $employee['employee_id']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">NIC:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($employee['employee_nic']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($employee['employee_email']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($employee['employee_phone']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Joining Date:</span>
                        <span class="detail-value"><?php echo formatDate($employee['joining_date']); ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Basic Salary:</span>
                        <span class="detail-value"><?php echo formatCurrency($employee['basic_salary']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Current Month Paysheet -->
            <?php if ($current_paysheet): ?>
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo getMonthName($current_month) . ' ' . $current_year; ?> Paysheet</h3>
                        <a href="download_paysheet.php?id=<?php echo $current_paysheet['paysheet_id']; ?>" class="btn-secondary btn-small" target="_blank">
                            📄 Download PDF
                        </a>
                    </div>
                    <div class="paysheet-summary">
                        <div class="summary-item earnings">
                            <span class="summary-label">Total Earnings</span>
                            <span class="summary-value"><?php echo formatCurrency($current_paysheet['total_earnings']); ?></span>
                        </div>
                        <div class="summary-item deductions">
                            <span class="summary-label">Total Deductions</span>
                            <span class="summary-value"><?php echo formatCurrency($current_paysheet['total_deductions']); ?></span>
                        </div>
                        <div class="summary-item net">
                            <span class="summary-label">Net Salary</span>
                            <span class="summary-value"><?php echo formatCurrency($current_paysheet['net_salary']); ?></span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="dashboard-card">
                    <div class="empty-state">
                        <span class="empty-icon">💰</span>
                        <h3>No Paysheet Yet</h3>
                        <p>Your paysheet for <?php echo getMonthName($current_month) . ' ' . $current_year; ?> has not been generated yet.</p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Statistics Grid -->
            <div class="dashboard-grid">
                
                <!-- Leave Balance -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Leave Balance (<?php echo $current_year; ?>)</h3>
                    </div>
                    <?php if ($leave_balance): ?>
                        <div class="leave-balance-grid">
                            <div class="balance-item">
                                <div class="balance-type">Annual Leave</div>
                                <div class="balance-count"><?php echo $leave_balance['annual_leave_remaining']; ?></div>
                                <div class="balance-label">days remaining</div>
                            </div>
                            <div class="balance-item">
                                <div class="balance-type">Casual Leave</div>
                                <div class="balance-count"><?php echo $leave_balance['casual_leave_remaining']; ?></div>
                                <div class="balance-label">days remaining</div>
                            </div>
                            <div class="balance-item">
                                <div class="balance-type">Sick Leave</div>
                                <div class="balance-count"><?php echo $leave_balance['sick_leave_remaining']; ?></div>
                                <div class="balance-label">days remaining</div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="request_leave.php" class="btn-primary btn-small">Request Leave</a>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Leave balance not initialized</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Leave Requests -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Leave Requests</h3>
                    </div>
                    <?php if (mysqli_num_rows($recent_leaves_result) > 0): ?>
                        <div class="leave-list">
                            <?php while ($leave = mysqli_fetch_assoc($recent_leaves_result)): ?>
                                <div class="leave-item">
                                    <div class="leave-type-badge <?php echo strtolower(str_replace(' ', '-', $leave['leave_name'])); ?>">
                                        <?php echo htmlspecialchars($leave['leave_name']); ?>
                                    </div>
                                    <div class="leave-dates">
                                        <?php echo formatDate($leave['start_date']) . ' - ' . formatDate($leave['end_date']); ?>
                                    </div>
                                    <div class="leave-status">
                                        <?php if ($leave['status'] == 'pending'): ?>
                                            <span class="status-badge status-pending">Pending</span>
                                        <?php elseif ($leave['status'] == 'approved'): ?>
                                            <span class="status-badge status-approved">Approved</span>
                                        <?php else: ?>
                                            <span class="status-badge status-rejected">Rejected</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        <div class="card-footer">
                            <a href="leave_balance.php" class="btn-secondary btn-small">View All</a>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <span class="empty-icon">📅</span>
                            <p>No leave requests yet</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Financial Info Grid -->
            <div class="dashboard-grid">
                
                <!-- Active Loan -->
                <?php if ($active_loan): ?>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Active Loan</h3>
                        </div>
                        <div class="loan-summary">
                            <div class="loan-detail">
                                <span class="loan-label">Loan Amount:</span>
                                <span class="loan-value"><?php echo formatCurrency($active_loan['loan_amount']); ?></span>
                            </div>
                            <div class="loan-detail">
                                <span class="loan-label">Monthly Installment:</span>
                                <span class="loan-value"><?php echo formatCurrency($active_loan['monthly_installment']); ?></span>
                            </div>
                            <div class="loan-detail">
                                <span class="loan-label">Remaining:</span>
                                <span class="loan-value highlight"><?php echo formatCurrency($active_loan['remaining_amount']); ?></span>
                            </div>
                            <?php 
                            $paid = $active_loan['loan_amount'] - $active_loan['remaining_amount'];
                            $percentage = ($active_loan['loan_amount'] > 0) ? ($paid / $active_loan['loan_amount']) * 100 : 0;
                            ?>
                            <div class="progress-bar-container">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <span class="progress-text"><?php echo round($percentage); ?>% Paid</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Pending Advance -->
                <?php if ($pending_advance): ?>
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3 class="card-title">Pending Salary Advance</h3>
                        </div>
                        <div class="advance-summary">
                            <div class="advance-amount">
                                <?php echo formatCurrency($pending_advance['advance_amount']); ?>
                            </div>
                            <div class="advance-info">
                                Will be deducted in <?php echo getMonthName($pending_advance['month']) . ' ' . $pending_advance['year']; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </main>

    </div>

    <script src="../accests/js/main.js"></script>
    <script>
        // Sidebar Toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
    </script>

    <style>
        .employee-profile-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: var(--border-radius-lg);
            padding: 32px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-lg);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 24px;
            margin-bottom: 24px;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }

        .profile-info h2 {
            margin: 0 0 8px 0;
            font-size: 28px;
        }

        .profile-position {
            font-size: 16px;
            opacity: 0.9;
            margin: 4px 0;
        }

        .profile-department {
            font-size: 14px;
            opacity: 0.8;
            margin: 4px 0;
        }

        .profile-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: var(--border-radius-md);
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .detail-label {
            font-size: 12px;
            opacity: 0.8;
        }

        .detail-value {
            font-size: 16px;
            font-weight: 600;
        }

        .paysheet-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 20px 0;
        }

        .summary-item {
            text-align: center;
            padding: 24px;
            background: var(--gray-50);
            border-radius: var(--border-radius-md);
            border-left: 4px solid;
        }

        .summary-item.earnings {
            border-left-color: #10b981;
        }

        .summary-item.deductions {
            border-left-color: #ef4444;
        }

        .summary-item.net {
            border-left-color: #6366f1;
        }

        .summary-label {
            display: block;
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .summary-value {
            display: block;
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .leave-balance-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            padding: 20px 0;
        }

        .balance-item {
            text-align: center;
            padding: 20px;
            background: var(--gray-50);
            border-radius: var(--border-radius-md);
        }

        .balance-type {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .balance-count {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .balance-label {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .leave-list {
            padding: 12px 0;
        }

        .leave-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: var(--gray-50);
            border-radius: var(--border-radius-md);
            margin-bottom: 8px;
        }

        .leave-type-badge {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .leave-type-badge.annual-leave {
            background: rgba(59, 130, 246, 0.15);
            color: #3b82f6;
        }

        .leave-type-badge.casual-leave {
            background: rgba(139, 92, 246, 0.15);
            color: #8b5cf6;
        }

        .leave-type-badge.sick-leave {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }

        .leave-dates {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .loan-summary,
        .advance-summary {
            padding: 20px 0;
        }

        .loan-detail {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .loan-label {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .loan-value {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .loan-value.highlight {
            color: #ef4444;
            font-size: 18px;
        }

        .progress-bar-container {
            margin-top: 16px;
        }

        .progress-bar {
            height: 12px;
            background: var(--gray-200);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            transition: width 0.5s ease;
        }

        .progress-text {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .advance-amount {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 12px;
        }

        .advance-info {
            text-align: center;
            font-size: 14px;
            color: var(--text-secondary);
        }

        @media (max-width: 768px) {
            .profile-details {
                grid-template-columns: 1fr;
            }

            .paysheet-summary,
            .leave-balance-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>