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
$employee_filter = isset($_GET['employee']) ? clean($conn, $_GET['employee']) : '';
$month_filter = isset($_GET['month']) ? clean($conn, $_GET['month']) : '';
$year_filter = isset($_GET['year']) ? clean($conn, $_GET['year']) : date('Y');

// Build query
$query = "SELECT p.*, e.employee_name, e.position, e.department
          FROM paysheets p
          JOIN employees e ON p.employee_id = e.employee_id
          WHERE e.company_id = '$company_id'";

if (!empty($employee_filter)) {
    $query .= " AND p.employee_id = '$employee_filter'";
}

if (!empty($month_filter)) {
    $query .= " AND p.month = '$month_filter'";
}

if (!empty($year_filter)) {
    $query .= " AND p.year = '$year_filter'";
}

$query .= " ORDER BY p.year DESC, p.month DESC, e.employee_name ASC";

$result = mysqli_query($conn, $query);

// Get all employees for filter
$employees_query = "SELECT employee_id, employee_name FROM employees WHERE company_id = '$company_id' AND status = 'active' ORDER BY employee_name";
$employees_result = mysqli_query($conn, $employees_query);

// Statistics for selected year
$total_query = "SELECT COUNT(*) as count, SUM(net_salary) as total FROM paysheets p
                JOIN employees e ON p.employee_id = e.employee_id
                WHERE e.company_id = '$company_id' AND p.year = '$year_filter'";
$total_result = mysqli_query($conn, $total_query);
$totals = mysqli_fetch_assoc($total_result);

$total_paysheets = $totals['count'];
$total_amount = $totals['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paysheet History - PaySheetPro</title>
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
                    <h1 class="page-title">Paysheet History</h1>
                    <p class="page-subtitle">View all generated paysheets</p>
                </div>
                <div class="page-actions">
                    <a href="generate_paysheet.php" class="btn-primary">
                        <span>← Generate Paysheets</span>
                    </a>
                </div>
            </div>

            <?php displayMessage(); ?>

            <!-- Statistics -->
            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-icon">📄</span>
                    <div>
                        <div class="stat-value"><?php echo $total_paysheets; ?></div>
                        <div class="stat-label">Total Paysheets (<?php echo $year_filter; ?>)</div>
                    </div>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">💰</span>
                    <div>
                        <div class="stat-value"><?php echo formatCurrency($total_amount); ?></div>
                        <div class="stat-label">Total Amount Paid (<?php echo $year_filter; ?>)</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-bar">
                <form method="GET" action="" class="filter-form">
                    <select name="employee" class="filter-select">
                        <option value="">All Employees</option>
                        <?php while ($emp = mysqli_fetch_assoc($employees_result)): ?>
                            <option value="<?php echo $emp['employee_id']; ?>" 
                                    <?php echo ($employee_filter == $emp['employee_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['employee_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

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
                    
                    <?php if (!empty($employee_filter) || !empty($month_filter)): ?>
                        <a href="paysheet_history.php" class="btn-secondary btn-small">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Paysheet History Table -->
            <div class="table-card">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="paysheet-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
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
                                            <div class="employee-info-table">
                                                <div class="employee-avatar-small">
                                                    <?php echo strtoupper(substr($paysheet['employee_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="employee-name-table">
                                                        <?php echo htmlspecialchars($paysheet['employee_name']); ?>
                                                    </div>
                                                    <div class="employee-dept-table">
                                                        <?php echo htmlspecialchars($paysheet['department']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
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
                                                <a href="view_paysheet.php?id=<?php echo $paysheet['paysheet_id']; ?>" 
                                                   class="btn-action btn-view" title="View">
                                                    👁️
                                                </a>
                                                <a href="generate_pdf.php?id=<?php echo $paysheet['paysheet_id']; ?>" 
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
                        <span class="empty-icon">📄</span>
                        <h3>No Paysheets Found</h3>
                        <p>Try adjusting your filters or generate new paysheets</p>
                        <a href="generate_paysheet.php" class="btn-primary">Generate Paysheets</a>
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