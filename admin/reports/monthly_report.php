<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();
$username = $_SESSION['username'];
$company_name = $_SESSION['company_name'];

// Get selected month and year
$selected_month = isset($_GET['month']) ? clean($conn, $_GET['month']) : date('n');
$selected_year = isset($_GET['year']) ? clean($conn, $_GET['year']) : date('Y');

// Fetch monthly statistics
$stats_query = "SELECT 
                COUNT(DISTINCT p.employee_id) as total_employees,
                SUM(p.basic_salary) as total_basic,
                SUM(p.ot_payment) as total_ot,
                SUM(p.travel_allowance) as total_travel,
                SUM(p.food_allowance) as total_food,
                SUM(p.total_earnings) as total_earnings,
                SUM(p.epf_deduction) as total_epf,
                SUM(p.etf_deduction) as total_etf,
                SUM(p.apit_tax) as total_tax,
                SUM(p.loan_deduction) as total_loans,
                SUM(p.advance_deduction) as total_advances,
                SUM(p.unapproved_leave_deduction) as total_unapproved,
                SUM(p.total_deductions) as total_deductions,
                SUM(p.net_salary) as total_net
                FROM paysheets p
                JOIN employees e ON p.employee_id = e.employee_id
                WHERE e.company_id = '$company_id' AND p.month = '$selected_month' AND p.year = '$selected_year'";

$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Fetch department-wise breakdown
$dept_query = "SELECT 
               e.department,
               COUNT(DISTINCT p.employee_id) as emp_count,
               SUM(p.basic_salary) as dept_basic,
               SUM(p.total_earnings) as dept_earnings,
               SUM(p.total_deductions) as dept_deductions,
               SUM(p.net_salary) as dept_net
               FROM paysheets p
               JOIN employees e ON p.employee_id = e.employee_id
               WHERE e.company_id = '$company_id' AND p.month = '$selected_month' AND p.year = '$selected_year'
               GROUP BY e.department
               ORDER BY dept_net DESC";

$dept_result = mysqli_query($conn, $dept_query);

// Fetch top 5 earners
$top_earners_query = "SELECT e.employee_name, e.position, p.basic_salary, p.net_salary
                      FROM paysheets p
                      JOIN employees e ON p.employee_id = e.employee_id
                      WHERE e.company_id = '$company_id' AND p.month = '$selected_month' AND p.year = '$selected_year'
                      ORDER BY p.net_salary DESC
                      LIMIT 5";

$top_earners_result = mysqli_query($conn, $top_earners_query);

// Check if any paysheets exist
$has_data = $stats['total_employees'] > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report - PaySheetPro</title>
    <link rel="stylesheet" href="../../accests/css/style.css">
    <link rel="stylesheet" href="../../accests/css/dashboard.css">
    <link rel="stylesheet" href="../../accests/css/reports.css">
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
                    <li class="nav-item">
                        <a href="../advances/view_advances.php" class="nav-link">
                            <span class="nav-icon">⚡</span>
                            <span class="nav-text">Salary Advances</span>
                        </a>
                    </li>
                    <li class="nav-item active">
                        <a href="monthly_report.php" class="nav-link">
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
                    <h1 class="page-title">Monthly Salary Report</h1>
                    <p class="page-subtitle">Comprehensive salary breakdown and analytics</p>
                </div>
                <div class="page-actions">
                    <a href="annual_report.php" class="btn-secondary">
                        <span>Annual Report</span>
                    </a>
                    <?php if ($has_data): ?>
                        <button onclick="window.print()" class="btn-primary">
                            <span>🖨️ Print Report</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Month Selection -->
            <div class="report-selector">
                <form method="GET" action="" class="selector-form">
                    <div class="selector-group">
                        <label for="month" class="selector-label">Select Month:</label>
                        <select name="month" id="month" class="selector-input" onchange="this.form.submit()">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo ($selected_month == $m) ? 'selected' : ''; ?>>
                                    <?php echo getMonthName($m); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="selector-group">
                        <label for="year" class="selector-label">Select Year:</label>
                        <select name="year" id="year" class="selector-input" onchange="this.form.submit()">
                            <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>
                
                <div class="report-title-section">
                    <h2 class="report-period"><?php echo getMonthName($selected_month) . ' ' . $selected_year; ?></h2>
                    <p class="report-date">Generated on: <?php echo date('d-m-Y H:i'); ?></p>
                </div>
            </div>

            <?php if ($has_data): ?>
                
                <!-- Summary Statistics -->
                <div class="report-stats-grid">
                    <div class="report-stat-card earnings">
                        <div class="stat-icon">💰</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Earnings</div>
                            <div class="stat-value"><?php echo formatCurrency($stats['total_earnings']); ?></div>
                            <div class="stat-detail"><?php echo $stats['total_employees']; ?> employees</div>
                        </div>
                    </div>

                    <div class="report-stat-card deductions">
                        <div class="stat-icon">➖</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Deductions</div>
                            <div class="stat-value"><?php echo formatCurrency($stats['total_deductions']); ?></div>
                            <div class="stat-detail">All statutory & other deductions</div>
                        </div>
                    </div>

                    <div class="report-stat-card net">
                        <div class="stat-icon">✅</div>
                        <div class="stat-content">
                            <div class="stat-label">Net Salary Paid</div>
                            <div class="stat-value"><?php echo formatCurrency($stats['total_net']); ?></div>
                            <div class="stat-detail">Actual amount disbursed</div>
                        </div>
                    </div>
                </div>

                <!-- Earnings Breakdown -->
                <div class="report-section">
                    <h3 class="section-title">
                        <span class="title-icon">💵</span>
                        Earnings Breakdown
                    </h3>
                    <div class="breakdown-grid">
                        <div class="breakdown-item">
                            <span class="breakdown-label">Basic Salary</span>
                            <span class="breakdown-value"><?php echo formatCurrency($stats['total_basic']); ?></span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">OT Payments</span>
                            <span class="breakdown-value"><?php echo formatCurrency($stats['total_ot']); ?></span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">Travel Allowance</span>
                            <span class="breakdown-value"><?php echo formatCurrency($stats['total_travel']); ?></span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">Food Allowance</span>
                            <span class="breakdown-value"><?php echo formatCurrency($stats['total_food']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Deductions Breakdown -->
                <div class="report-section">
                    <h3 class="section-title">
                        <span class="title-icon">📉</span>
                        Deductions Breakdown
                    </h3>
                    <div class="breakdown-grid">
                        <div class="breakdown-item">
                            <span class="breakdown-label">EPF (12%)</span>
                            <span class="breakdown-value"><?php echo formatCurrency($stats['total_epf']); ?></span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">ETF (3%)</span>
                            <span class="breakdown-value"><?php echo formatCurrency($stats['total_etf']); ?></span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">APIT Tax</span>
                            <span class="breakdown-value"><?php echo formatCurrency($stats['total_tax']); ?></span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">Loan Deductions</span>
                            <span class="breakdown-value"><?php echo formatCurrency($stats['total_loans']); ?></span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">Salary Advances</span>
                            <span class="breakdown-value"><?php echo formatCurrency($stats['total_advances']); ?></span>
                        </div>
                        <div class="breakdown-item">
                            <span class="breakdown-label">Unapproved Leave</span>
                            <span class="breakdown-value"><?php echo formatCurrency($stats['total_unapproved']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Department Breakdown -->
                <div class="report-section">
                    <h3 class="section-title">
                        <span class="title-icon">🏢</span>
                        Department-wise Breakdown
                    </h3>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Employees</th>
                                    <th>Basic Salary</th>
                                    <th>Total Earnings</th>
                                    <th>Total Deductions</th>
                                    <th>Net Salary</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($dept = mysqli_fetch_assoc($dept_result)): ?>
                                    <tr>
                                        <td class="dept-name"><?php echo htmlspecialchars($dept['department']); ?></td>
                                        <td class="text-center"><?php echo $dept['emp_count']; ?></td>
                                        <td class="amount"><?php echo formatCurrency($dept['dept_basic']); ?></td>
                                        <td class="amount positive"><?php echo formatCurrency($dept['dept_earnings']); ?></td>
                                        <td class="amount negative"><?php echo formatCurrency($dept['dept_deductions']); ?></td>
                                        <td class="amount net"><?php echo formatCurrency($dept['dept_net']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Earners -->
                <div class="report-section">
                    <h3 class="section-title">
                        <span class="title-icon">🏆</span>
                        Top 5 Earners
                    </h3>
                    <div class="top-earners-grid">
                        <?php 
                        $rank = 1;
                        while ($earner = mysqli_fetch_assoc($top_earners_result)): 
                        ?>
                            <div class="earner-card">
                                <div class="earner-rank">#<?php echo $rank++; ?></div>
                                <div class="earner-info">
                                    <div class="earner-name"><?php echo htmlspecialchars($earner['employee_name']); ?></div>
                                    <div class="earner-position"><?php echo htmlspecialchars($earner['position']); ?></div>
                                </div>
                                <div class="earner-amount"><?php echo formatCurrency($earner['net_salary']); ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

            <?php else: ?>
                
                <div class="empty-state">
                    <span class="empty-icon">📊</span>
                    <h3>No Data Available</h3>
                    <p>No paysheets have been generated for <?php echo getMonthName($selected_month) . ' ' . $selected_year; ?></p>
                    <a href="../paysheet/generate_paysheet.php" class="btn-primary">Generate Paysheets</a>
                </div>

            <?php endif; ?>

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