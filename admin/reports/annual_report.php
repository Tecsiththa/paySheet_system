<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();
$username = $_SESSION['username'];
$company_name = $_SESSION['company_name'];

// Get selected year
$selected_year = isset($_GET['year']) ? clean($conn, $_GET['year']) : date('Y');

// Fetch annual statistics
$annual_stats_query = "SELECT 
                       SUM(p.total_earnings) as total_earnings,
                       SUM(p.total_deductions) as total_deductions,
                       SUM(p.net_salary) as total_net,
                       COUNT(DISTINCT p.paysheet_id) as total_paysheets
                       FROM paysheets p
                       JOIN employees e ON p.employee_id = e.employee_id
                       WHERE e.company_id = '$company_id' AND p.year = '$selected_year'";

$annual_stats_result = mysqli_query($conn, $annual_stats_query);
$annual_stats = mysqli_fetch_assoc($annual_stats_result);

// Fetch month-by-month breakdown
$monthly_breakdown_query = "SELECT 
                            p.month,
                            COUNT(DISTINCT p.employee_id) as emp_count,
                            SUM(p.basic_salary) as month_basic,
                            SUM(p.total_earnings) as month_earnings,
                            SUM(p.total_deductions) as month_deductions,
                            SUM(p.net_salary) as month_net
                            FROM paysheets p
                            JOIN employees e ON p.employee_id = e.employee_id
                            WHERE e.company_id = '$company_id' AND p.year = '$selected_year'
                            GROUP BY p.month
                            ORDER BY p.month ASC";

$monthly_breakdown_result = mysqli_query($conn, $monthly_breakdown_query);

// Create month array for chart
$months_data = array_fill(1, 12, 0);
while ($month_data = mysqli_fetch_assoc($monthly_breakdown_result)) {
    $months_data[$month_data['month']] = $month_data['month_net'];
}
mysqli_data_seek($monthly_breakdown_result, 0); // Reset pointer

// Fetch annual department statistics
$dept_annual_query = "SELECT 
                      e.department,
                      COUNT(DISTINCT p.paysheet_id) as paysheet_count,
                      SUM(p.total_earnings) as dept_earnings,
                      SUM(p.total_deductions) as dept_deductions,
                      SUM(p.net_salary) as dept_net
                      FROM paysheets p
                      JOIN employees e ON p.employee_id = e.employee_id
                      WHERE e.company_id = '$company_id' AND p.year = '$selected_year'
                      GROUP BY e.department
                      ORDER BY dept_net DESC";

$dept_annual_result = mysqli_query($conn, $dept_annual_query);

// Check if any data exists
$has_data = $annual_stats['total_paysheets'] > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annual Report - PaySheetPro</title>
    <link rel="stylesheet" href="../../accests/css/style.css">
    <link rel="stylesheet" href="../../accests/css/dashboard.css">
    <link rel="stylesheet" href="../../accests/css/reports.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <h1 class="page-title">Annual Salary Report</h1>
                    <p class="page-subtitle">Year-end salary summary and trends</p>
                </div>
                <div class="page-actions">
                    <a href="monthly_report.php" class="btn-secondary">
                        <span>Monthly Report</span>
                    </a>
                    <?php if ($has_data): ?>
                        <button onclick="window.print()" class="btn-primary">
                            <span>🖨️ Print Report</span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Year Selection -->
            <div class="report-selector">
                <form method="GET" action="" class="selector-form">
                    <div class="selector-group">
                        <label for="year" class="selector-label">Select Year:</label>
                        <select name="year" id="year" class="selector-input" onchange="this.form.submit()">
                            <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>
                
                <div class="report-title-section">
                    <h2 class="report-period">Financial Year <?php echo $selected_year; ?></h2>
                    <p class="report-date">Generated on: <?php echo date('d-m-Y H:i'); ?></p>
                </div>
            </div>

            <?php if ($has_data): ?>
                
                <!-- Annual Summary -->
                <div class="report-stats-grid">
                    <div class="report-stat-card earnings">
                        <div class="stat-icon">💰</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Annual Earnings</div>
                            <div class="stat-value"><?php echo formatCurrency($annual_stats['total_earnings']); ?></div>
                            <div class="stat-detail"><?php echo $annual_stats['total_paysheets']; ?> paysheets</div>
                        </div>
                    </div>

                    <div class="report-stat-card deductions">
                        <div class="stat-icon">➖</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Annual Deductions</div>
                            <div class="stat-value"><?php echo formatCurrency($annual_stats['total_deductions']); ?></div>
                            <div class="stat-detail">All deductions combined</div>
                        </div>
                    </div>

                    <div class="report-stat-card net">
                        <div class="stat-icon">✅</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Net Paid</div>
                            <div class="stat-value"><?php echo formatCurrency($annual_stats['total_net']); ?></div>
                            <div class="stat-detail">Total disbursed for the year</div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Trend Chart -->
                <div class="report-section">
                    <h3 class="section-title">
                        <span class="title-icon">📈</span>
                        Monthly Salary Trend
                    </h3>
                    <div class="chart-container">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>

                <!-- Month-by-Month Breakdown -->
                <div class="report-section">
                    <h3 class="section-title">
                        <span class="title-icon">📅</span>
                        Month-by-Month Breakdown
                    </h3>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Employees</th>
                                    <th>Basic Salary</th>
                                    <th>Total Earnings</th>
                                    <th>Total Deductions</th>
                                    <th>Net Salary</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($month = mysqli_fetch_assoc($monthly_breakdown_result)): ?>
                                    <tr>
                                        <td class="month-name"><?php echo getMonthName($month['month']); ?></td>
                                        <td class="text-center"><?php echo $month['emp_count']; ?></td>
                                        <td class="amount"><?php echo formatCurrency($month['month_basic']); ?></td>
                                        <td class="amount positive"><?php echo formatCurrency($month['month_earnings']); ?></td>
                                        <td class="amount negative"><?php echo formatCurrency($month['month_deductions']); ?></td>
                                        <td class="amount net"><?php echo formatCurrency($month['month_net']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Annual Department Summary -->
                <div class="report-section">
                    <h3 class="section-title">
                        <span class="title-icon">🏢</span>
                        Annual Department Summary
                    </h3>
                    <div class="table-responsive">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Total Paysheets</th>
                                    <th>Total Earnings</th>
                                    <th>Total Deductions</th>
                                    <th>Net Salary</th>
                                    <th>% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($dept = mysqli_fetch_assoc($dept_annual_result)): 
                                    $percentage = ($annual_stats['total_net'] > 0) ? ($dept['dept_net'] / $annual_stats['total_net']) * 100 : 0;
                                ?>
                                    <tr>
                                        <td class="dept-name"><?php echo htmlspecialchars($dept['department']); ?></td>
                                        <td class="text-center"><?php echo $dept['paysheet_count']; ?></td>
                                        <td class="amount positive"><?php echo formatCurrency($dept['dept_earnings']); ?></td>
                                        <td class="amount negative"><?php echo formatCurrency($dept['dept_deductions']); ?></td>
                                        <td class="amount net"><?php echo formatCurrency($dept['dept_net']); ?></td>
                                        <td class="text-center"><span class="percentage-badge"><?php echo round($percentage, 1); ?>%</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php else: ?>
                
                <div class="empty-state">
                    <span class="empty-icon">📊</span>
                    <h3>No Data Available</h3>
                    <p>No paysheets have been generated for the year <?php echo $selected_year; ?></p>
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

        <?php if ($has_data): ?>
        // Monthly Trend Chart
        const ctx = document.getElementById('monthlyTrendChart').getContext('2d');
        const monthlyTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Net Salary Paid (LKR)',
                    data: <?php echo json_encode(array_values($months_data)); ?>,
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'LKR ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'LKR ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>