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
$emp_query = "SELECT * FROM employees WHERE user_id = '$user_id'";
$emp_result = mysqli_query($conn, $emp_query);
$employee = mysqli_fetch_assoc($emp_result);
$employee_id = $employee['employee_id'];

// Filters
$month_filter = isset($_GET['month']) ? clean($conn, $_GET['month']) : '';
$year_filter = isset($_GET['year']) ? clean($conn, $_GET['year']) : date('Y');

// Build query
$query = "SELECT * FROM paysheets WHERE employee_id = '$employee_id'";

if (!empty($month_filter)) {
    $query .= " AND month = '$month_filter'";
}

if (!empty($year_filter)) {
    $query .= " AND year = '$year_filter'";
}

$query .= " ORDER BY year DESC, month DESC";

$result = mysqli_query($conn, $query);

// Statistics for selected year
$year_stats_query = "SELECT 
                     COUNT(*) as count,
                     SUM(total_earnings) as total_earnings,
                     SUM(total_deductions) as total_deductions,
                     SUM(net_salary) as total_net
                     FROM paysheets 
                     WHERE employee_id = '$employee_id' AND year = '$year_filter'";
$year_stats_result = mysqli_query($conn, $year_stats_query);
$year_stats = mysqli_fetch_assoc($year_stats_result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Paysheets - PaySheetPro</title>
    <link rel="stylesheet" href="../accests/css/style.css">
    <link rel="stylesheet" href="../accests/css/dashboard.css">
    <link rel="stylesheet" href="../accests/css/paysheet.css">
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
                    <li class="nav-item">
                        <a href="employee_dashboard.php" class="nav-link">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item active">
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
                    <h1 class="page-title">My Paysheets</h1>
                    <p class="page-subtitle">View and download your salary history</p>
                </div>
            </div>

            <?php displayMessage(); ?>

            <!-- Year Statistics -->
            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-icon">📄</span>
                    <div>
                        <div class="stat-value"><?php echo $year_stats['count']; ?></div>
                        <div class="stat-label">Paysheets (<?php echo $year_filter; ?>)</div>
                    </div>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">💰</span>
                    <div>
                        <div class="stat-value"><?php echo formatCurrency($year_stats['total_earnings']); ?></div>
                        <div class="stat-label">Total Earnings (<?php echo $year_filter; ?>)</div>
                    </div>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">✅</span>
                    <div>
                        <div class="stat-value"><?php echo formatCurrency($year_stats['total_net']); ?></div>
                        <div class="stat-label">Total Net Paid (<?php echo $year_filter; ?>)</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-bar">
                <form method="GET" action="" class="filter-form">
                    <select name="month" class="filter-select">
                        <option value="">All Months</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo ($month_filter == $m) ? 'selected' : ''; ?>>
                                <?php echo getMonthName($m); ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <select name="year" class="filter-select">
                        <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($year_filter == $y) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <button type="submit" class="btn-primary btn-small">Filter</button>
                    
                    <?php if (!empty($month_filter)): ?>
                        <a href="view_paysheet.php" class="btn-secondary btn-small">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Paysheets Table -->
            <div class="table-card">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="paysheet-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Basic Salary</th>
                                    <th>Total Earnings</th>
                                    <th>Total Deductions</th>
                                    <th>Net Salary</th>
                                    <th>Generated On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($paysheet = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <span class="month-badge">
                                                <?php echo getMonthName($paysheet['month']) . ' ' . $paysheet['year']; ?>
                                            </span>
                                        </td>
                                        <td class="amount-cell"><?php echo formatCurrency($paysheet['basic_salary']); ?></td>
                                        <td class="amount-cell positive"><?php echo formatCurrency($paysheet['total_earnings']); ?></td>
                                        <td class="amount-cell negative"><?php echo formatCurrency($paysheet['total_deductions']); ?></td>
                                        <td class="amount-cell net-salary"><?php echo formatCurrency($paysheet['net_salary']); ?></td>
                                        <td><?php echo formatDate($paysheet['generated_date']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="download_paysheet.php?id=<?php echo $paysheet['paysheet_id']; ?>" 
                                                   class="btn-action btn-pdf" title="Download PDF" target="_blank">
                                                    📄
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
                        <span class="empty-icon">💰</span>
                        <h3>No Paysheets Found</h3>
                        <p>No paysheets have been generated yet for the selected period</p>
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
</body>
</html>