<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();
$username = $_SESSION['username'];
$company_name = $_SESSION['company_name'];

// Filters
$status_filter = isset($_GET['status']) ? clean($conn, $_GET['status']) : '';

// Build query
$query = "SELECT l.*, e.employee_name, e.position, e.department
          FROM loans l
          JOIN employees e ON l.employee_id = e.employee_id
          WHERE e.company_id = '$company_id'";

if (!empty($status_filter)) {
    $query .= " AND l.status = '$status_filter'";
}

$query .= " ORDER BY l.loan_id DESC";

$result = mysqli_query($conn, $query);

// Statistics
$active_query = mysqli_query($conn, "SELECT COUNT(*) as count, SUM(remaining_amount) as total FROM loans l 
                                     JOIN employees e ON l.employee_id = e.employee_id 
                                     WHERE e.company_id = '$company_id' AND l.status = 'active'");
$active_stats = mysqli_fetch_assoc($active_query);
$active_loans = $active_stats['count'];
$total_outstanding = $active_stats['total'] ?? 0;

$completed_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM loans l 
                                        JOIN employees e ON l.employee_id = e.employee_id 
                                        WHERE e.company_id = '$company_id' AND l.status = 'completed'");
$completed_loans = mysqli_fetch_assoc($completed_query)['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Loans - PaySheetPro</title>
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
                    <h1 class="page-title">Employee Loans</h1>
                    <p class="page-subtitle">Manage employee loans and installments</p>
                </div>
                <div class="page-actions">
                    <a href="add_loan.php" class="btn-primary">
                        <span>+ Add New Loan</span>
                    </a>
                </div>
            </div>

            <?php displayMessage(); ?>

            <!-- Statistics -->
            <div class="loan-stats">
                <div class="loan-stat-card active">
                    <div class="stat-icon">💳</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $active_loans; ?></div>
                        <div class="stat-label">Active Loans</div>
                    </div>
                </div>

                <div class="loan-stat-card outstanding">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo formatCurrency($total_outstanding); ?></div>
                        <div class="stat-label">Total Outstanding</div>
                    </div>
                </div>

                <div class="loan-stat-card completed">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $completed_loans; ?></div>
                        <div class="stat-label">Completed Loans</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-bar">
                <form method="GET" action="" class="filter-form">
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="active" <?php echo ($status_filter == 'active') ? 'selected' : ''; ?>>Active</option>
                        <option value="completed" <?php echo ($status_filter == 'completed') ? 'selected' : ''; ?>>Completed</option>
                    </select>

                    <button type="submit" class="btn-primary btn-small">Filter</button>
                    
                    <?php if (!empty($status_filter)): ?>
                        <a href="view_loans.php" class="btn-secondary btn-small">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Loans Table -->
            <div class="table-card">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="loan-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Loan Amount</th>
                                    <th>Monthly Installment</th>
                                    <th>Paid Amount</th>
                                    <th>Remaining</th>
                                    <th>Progress</th>
                                    <th>Start Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($loan = mysqli_fetch_assoc($result)): 
                                    $paid_amount = $loan['loan_amount'] - $loan['remaining_amount'];
                                    $progress_percentage = ($loan['loan_amount'] > 0) ? ($paid_amount / $loan['loan_amount']) * 100 : 0;
                                ?>
                                    <tr>
                                        <td>
                                            <div class="employee-info-table">
                                                <div class="employee-avatar-small">
                                                    <?php echo strtoupper(substr($loan['employee_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="employee-name-table">
                                                        <?php echo htmlspecialchars($loan['employee_name']); ?>
                                                    </div>
                                                    <div class="employee-dept-table">
                                                        <?php echo htmlspecialchars($loan['department']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="amount-cell"><?php echo formatCurrency($loan['loan_amount']); ?></td>
                                        <td class="amount-cell"><?php echo formatCurrency($loan['monthly_installment']); ?></td>
                                        <td class="amount-cell positive"><?php echo formatCurrency($paid_amount); ?></td>
                                        <td class="amount-cell negative"><?php echo formatCurrency($loan['remaining_amount']); ?></td>
                                        <td>
                                            <div class="progress-container">
                                                <div class="progress-bar">
                                                    <div class="progress-fill" style="width: <?php echo $progress_percentage; ?>%;"></div>
                                                </div>
                                                <span class="progress-text"><?php echo round($progress_percentage); ?>%</span>
                                            </div>
                                        </td>
                                        <td><?php echo formatDate($loan['start_date']); ?></td>
                                        <td>
                                            <?php if ($loan['status'] == 'active'): ?>
                                                <span class="status-badge status-active">Active</span>
                                            <?php else: ?>
                                                <span class="status-badge status-completed">Completed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="loan_payments.php?id=<?php echo $loan['loan_id']; ?>" 
                                                   class="btn-action btn-view" title="View Payments">
                                                    📋
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon">💳</span>
                        <h3>No Loans Found</h3>
                        <p>No employee loans have been registered yet</p>
                        <a href="add_loan.php" class="btn-primary">Add First Loan</a>
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