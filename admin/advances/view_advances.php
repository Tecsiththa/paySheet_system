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
$month_filter = isset($_GET['month']) ? clean($conn, $_GET['month']) : '';
$year_filter = isset($_GET['year']) ? clean($conn, $_GET['year']) : date('Y');

// Build query
$query = "SELECT sa.*, e.employee_name, e.position, e.department, e.basic_salary
          FROM salary_advances sa
          JOIN employees e ON sa.employee_id = e.employee_id
          WHERE e.company_id = '$company_id'";

if (!empty($status_filter)) {
    $query .= " AND sa.status = '$status_filter'";
}

if (!empty($month_filter)) {
    $query .= " AND sa.month = '$month_filter'";
}

if (!empty($year_filter)) {
    $query .= " AND sa.year = '$year_filter'";
}

$query .= " ORDER BY sa.year DESC, sa.month DESC, sa.advance_id DESC";

$result = mysqli_query($conn, $query);

// Statistics
$pending_query = mysqli_query($conn, "SELECT COUNT(*) as count, SUM(advance_amount) as total FROM salary_advances sa 
                                      JOIN employees e ON sa.employee_id = e.employee_id 
                                      WHERE e.company_id = '$company_id' AND sa.status = 'pending'");
$pending_stats = mysqli_fetch_assoc($pending_query);
$pending_count = $pending_stats['count'];
$pending_total = $pending_stats['total'] ?? 0;

$deducted_query = mysqli_query($conn, "SELECT COUNT(*) as count, SUM(advance_amount) as total FROM salary_advances sa 
                                       JOIN employees e ON sa.employee_id = e.employee_id 
                                       WHERE e.company_id = '$company_id' AND sa.status = 'deducted' AND sa.year = '$year_filter'");
$deducted_stats = mysqli_fetch_assoc($deducted_query);
$deducted_count = $deducted_stats['count'];
$deducted_total = $deducted_stats['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Advances - PaySheetPro</title>
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
                    <h1 class="page-title">Salary Advances</h1>
                    <p class="page-subtitle">Manage employee salary advances</p>
                </div>
                <div class="page-actions">
                    <a href="add_advance.php" class="btn-primary">
                        <span>+ Add Salary Advance</span>
                    </a>
                </div>
            </div>

            <?php displayMessage(); ?>

            <!-- Statistics -->
            <div class="loan-stats">
                <div class="loan-stat-card active">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $pending_count; ?></div>
                        <div class="stat-label">Pending Advances</div>
                    </div>
                </div>

                <div class="loan-stat-card outstanding">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo formatCurrency($pending_total); ?></div>
                        <div class="stat-label">Pending Amount</div>
                    </div>
                </div>

                <div class="loan-stat-card completed">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo formatCurrency($deducted_total); ?></div>
                        <div class="stat-label">Deducted (<?php echo $year_filter; ?>)</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-bar">
                <form method="GET" action="" class="filter-form">
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="deducted" <?php echo ($status_filter == 'deducted') ? 'selected' : ''; ?>>Deducted</option>
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
                        <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($year_filter == $y) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <button type="submit" class="btn-primary btn-small">Filter</button>
                    
                    <?php if (!empty($status_filter) || !empty($month_filter)): ?>
                        <a href="view_advances.php" class="btn-secondary btn-small">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Advances Table -->
            <div class="table-card">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="loan-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Basic Salary</th>
                                    <th>Advance Amount</th>
                                    <th>Percentage</th>
                                    <th>Deduction Month</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($advance = mysqli_fetch_assoc($result)): 
                                    $percentage = ($advance['basic_salary'] > 0) ? ($advance['advance_amount'] / $advance['basic_salary']) * 100 : 0;
                                ?>
                                    <tr>
                                        <td>
                                            <div class="employee-info-table">
                                                <div class="employee-avatar-small">
                                                    <?php echo strtoupper(substr($advance['employee_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="employee-name-table">
                                                        <?php echo htmlspecialchars($advance['employee_name']); ?>
                                                    </div>
                                                    <div class="employee-dept-table">
                                                        <?php echo htmlspecialchars($advance['department']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="amount-cell"><?php echo formatCurrency($advance['basic_salary']); ?></td>
                                        <td class="amount-cell negative"><?php echo formatCurrency($advance['advance_amount']); ?></td>
                                        <td>
                                            <span class="percentage-badge" style="background: <?php echo ($percentage > 50) ? 'rgba(239, 68, 68, 0.15)' : 'rgba(16, 185, 129, 0.15)'; ?>; color: <?php echo ($percentage > 50) ? '#ef4444' : '#10b981'; ?>;">
                                                <?php echo round($percentage); ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <span class="month-badge">
                                                <?php echo getMonthName($advance['month']) . ' ' . $advance['year']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($advance['request_date']); ?></td>
                                        <td>
                                            <?php if ($advance['status'] == 'pending'): ?>
                                                <span class="status-badge status-pending">⏳ Pending</span>
                                            <?php else: ?>
                                                <span class="status-badge status-completed">✓ Deducted</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon">⚡</span>
                        <h3>No Salary Advances Found</h3>
                        <p>No salary advances have been registered yet</p>
                        <a href="add_advance.php" class="btn-primary">Add First Advance</a>
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