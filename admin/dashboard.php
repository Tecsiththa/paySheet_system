<?php
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

// Check if user is admin
if (!isAdmin()) {
    redirect('../employee/employee_dashboard.php');
}

$user_id = getCurrentUserId();
$company_id = getCompanyId();
$company_name = $_SESSION['company_name'];
$username = $_SESSION['username'];

// Get current month and year
$current_month = date('n');
$current_year = date('Y');

// ===== STATISTICS QUERIES =====

// Total Employees
$query_total_employees = "SELECT COUNT(*) as total FROM employees WHERE company_id = '$company_id' AND status = 'active'";
$result_total_employees = mysqli_query($conn, $query_total_employees);
$total_employees = mysqli_fetch_assoc($result_total_employees)['total'];

// This Month Paysheets Generated
$query_paysheets = "SELECT COUNT(*) as total FROM paysheets p 
                    JOIN employees e ON p.employee_id = e.employee_id 
                    WHERE e.company_id = '$company_id' AND p.month = '$current_month' AND p.year = '$current_year'";
$result_paysheets = mysqli_query($conn, $query_paysheets);
$paysheets_generated = mysqli_fetch_assoc($result_paysheets)['total'];

// Pending Leave Requests
$query_pending_leaves = "SELECT COUNT(*) as total FROM leave_records lr
                         JOIN employees e ON lr.employee_id = e.employee_id
                         WHERE e.company_id = '$company_id' AND lr.status = 'pending'";
$result_pending_leaves = mysqli_query($conn, $query_pending_leaves);
$pending_leaves = mysqli_fetch_assoc($result_pending_leaves)['total'];

// Active Loans
$query_active_loans = "SELECT COUNT(*) as total FROM loans l
                       JOIN employees e ON l.employee_id = e.employee_id
                       WHERE e.company_id = '$company_id' AND l.status = 'active'";
$result_active_loans = mysqli_query($conn, $query_active_loans);
$active_loans = mysqli_fetch_assoc($result_active_loans)['total'];

// Total Salary This Month
$query_total_salary = "SELECT SUM(net_salary) as total FROM paysheets p
                       JOIN employees e ON p.employee_id = e.employee_id
                       WHERE e.company_id = '$company_id' AND p.month = '$current_month' AND p.year = '$current_year'";
$result_total_salary = mysqli_query($conn, $query_total_salary);
$total_salary = mysqli_fetch_assoc($result_total_salary)['total'] ?? 0;

// Recent Employees (Last 5)
$query_recent_employees = "SELECT employee_id, employee_name, position, department, basic_salary, joining_date 
                          FROM employees 
                          WHERE company_id = '$company_id' AND status = 'active'
                          ORDER BY employee_id DESC LIMIT 5";
$result_recent_employees = mysqli_query($conn, $query_recent_employees);

// Recent Leave Requests (Last 5 Pending)
$query_recent_leaves = "SELECT lr.*, e.employee_name, lt.leave_name 
                        FROM leave_records lr
                        JOIN employees e ON lr.employee_id = e.employee_id
                        JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id
                        WHERE e.company_id = '$company_id' AND lr.status = 'pending'
                        ORDER BY lr.leave_id DESC LIMIT 5";
$result_recent_leaves = mysqli_query($conn, $query_recent_leaves);

// Department-wise Employee Count
$query_departments = "SELECT department, COUNT(*) as count 
                      FROM employees 
                      WHERE company_id = '$company_id' AND status = 'active'
                      GROUP BY department
                      ORDER BY count DESC LIMIT 5";
$result_departments = mysqli_query($conn, $query_departments);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PaySheetPro</title>
    <link rel="stylesheet" href="../accests/css/style.css">
    <link rel="stylesheet" href="../accests/css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    
    <!-- ===== HEADER ===== -->
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
            <div class="header-icons">
                <button class="icon-btn" title="Notifications">
                    <span class="icon">🔔</span>
                    <?php if ($pending_leaves > 0): ?>
                        <span class="badge"><?php echo $pending_leaves; ?></span>
                    <?php endif; ?>
                </button>
            </div>
            <div class="user-menu">
                <div class="user-avatar">
                    <span><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                </div>
                <div class="user-details">
                    <span class="user-name"><?php echo htmlspecialchars($username); ?></span>
                    <span class="user-role">Admin</span>
                </div>
                <a href="../auth/logout.php" class="logout-btn" title="Logout">
                    <span class="icon">🚪</span>
                </a>
            </div>
        </div>
    </header>

    <div class="dashboard-container">
        
        <!-- ===== SIDEBAR ===== -->
        <aside class="sidebar" id="sidebar">
            <nav class="sidebar-nav">
                <ul class="nav-list">
                    <li class="nav-item active">
                        <a href="dashboard.php" class="nav-link">
                            <span class="nav-icon">📊</span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="employees/view_employees.php" class="nav-link">
                            <span class="nav-icon">👥</span>
                            <span class="nav-text">Employees</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="leaves/leave_requests.php" class="nav-link">
                            <span class="nav-icon">📅</span>
                            <span class="nav-text">Leave Requests</span>
                            <?php if ($pending_leaves > 0): ?>
                                <span class="nav-badge"><?php echo $pending_leaves; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="paysheet/generate_paysheet.php" class="nav-link">
                            <span class="nav-icon">💰</span>
                            <span class="nav-text">Paysheets</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="loans/view_loans.php" class="nav-link">
                            <span class="nav-icon">💳</span>
                            <span class="nav-text">Loans</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="advances/view_advances.php" class="nav-link">
                            <span class="nav-icon">⚡</span>
                            <span class="nav-text">Salary Advances</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="reports/monthly_report.php" class="nav-link">
                            <span class="nav-icon">📈</span>
                            <span class="nav-text">Reports</span>
                        </a>
                    </li>
                    <li class="nav-divider"></li>
                    <li class="nav-item">
                        <a href="company_settings.php" class="nav-link">
                            <span class="nav-icon">⚙️</span>
                            <span class="nav-text">Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- ===== MAIN CONTENT ===== -->
        <main class="main-content">
            
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($username); ?>! Here's what's happening today.</p>
                </div>
                <div class="page-actions">
                    <span class="current-date">📅 <?php echo date('l, F d, Y'); ?></span>
                </div>
            </div>

            <?php displayMessage(); ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card card-blue">
                    <div class="stat-icon">👥</div>
                    <div class="stat-details">
                        <div class="stat-label">Total Employees</div>
                        <div class="stat-value"><?php echo $total_employees; ?></div>
                    </div>
                    <a href="employees/view_employees.php" class="stat-link">View All →</a>
                </div>

                <div class="stat-card card-green">
                    <div class="stat-icon">📄</div>
                    <div class="stat-details">
                        <div class="stat-label">Paysheets (This Month)</div>
                        <div class="stat-value"><?php echo $paysheets_generated; ?>/<?php echo $total_employees; ?></div>
                    </div>
                    <a href="paysheet/paysheet_history.php" class="stat-link">View All →</a>
                </div>

                <div class="stat-card card-orange">
                    <div class="stat-icon">📅</div>
                    <div class="stat-details">
                        <div class="stat-label">Pending Leave Requests</div>
                        <div class="stat-value"><?php echo $pending_leaves; ?></div>
                    </div>
                    <a href="leaves/leave_requests.php" class="stat-link">Review →</a>
                </div>

                <div class="stat-card card-purple">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <div class="stat-label">Total Salary (This Month)</div>
                        <div class="stat-value"><?php echo formatCurrency($total_salary); ?></div>
                    </div>
                    <a href="reports/monthly_report.php" class="stat-link">View Report →</a>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="dashboard-grid">
                
                <!-- Recent Employees -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Employees</h3>
                        <a href="employees/add_employee.php" class="btn-small btn-primary">+ Add Employee</a>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($result_recent_employees) > 0): ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Department</th>
                                            <th>Salary</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($employee = mysqli_fetch_assoc($result_recent_employees)): ?>
                                            <tr>
                                                <td>
                                                    <div class="employee-name">
                                                        <span class="employee-avatar"><?php echo strtoupper(substr($employee['employee_name'], 0, 1)); ?></span>
                                                        <span><?php echo htmlspecialchars($employee['employee_name']); ?></span>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                                <td><span class="badge badge-blue"><?php echo htmlspecialchars($employee['department']); ?></span></td>
                                                <td class="text-bold"><?php echo formatCurrency($employee['basic_salary']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <span class="empty-icon">👥</span>
                                <p>No employees yet</p>
                                <a href="employees/add_employee.php" class="btn-primary">Add First Employee</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Leave Requests -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3 class="card-title">Pending Leave Requests</h3>
                        <a href="leaves/leave_requests.php" class="btn-small btn-secondary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($result_recent_leaves) > 0): ?>
                            <div class="leave-list">
                                <?php while ($leave = mysqli_fetch_assoc($result_recent_leaves)): ?>
                                    <div class="leave-item">
                                        <div class="leave-info">
                                            <div class="leave-employee"><?php echo htmlspecialchars($leave['employee_name']); ?></div>
                                            <div class="leave-details">
                                                <span class="leave-type badge badge-purple"><?php echo htmlspecialchars($leave['leave_name']); ?></span>
                                                <span class="leave-dates"><?php echo formatDate($leave['start_date']); ?> - <?php echo formatDate($leave['end_date']); ?></span>
                                                <span class="leave-days"><?php echo $leave['days_count']; ?> days</span>
                                            </div>
                                        </div>
                                        <div class="leave-actions">
                                            <a href="leaves/approve_leave.php?id=<?php echo $leave['leave_id']; ?>&action=approve" 
                                               class="btn-icon btn-success" title="Approve">✓</a>
                                            <a href="leaves/approve_leave.php?id=<?php echo $leave['leave_id']; ?>&action=reject" 
                                               class="btn-icon btn-danger" title="Reject">✕</a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <span class="empty-icon">✅</span>
                                <p>No pending leave requests</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Department Stats -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title">Department Distribution</h3>
                </div>
                <div class="card-body">
                    <div class="department-grid">
                        <?php 
                        $colors = ['#6366f1', '#ec4899', '#06b6d4', '#10b981', '#f59e0b'];
                        $index = 0;
                        while ($dept = mysqli_fetch_assoc($result_departments)): 
                        ?>
                            <div class="department-card" style="border-left-color: <?php echo $colors[$index % 5]; ?>;">
                                <div class="dept-name"><?php echo htmlspecialchars($dept['department']); ?></div>
                                <div class="dept-count"><?php echo $dept['count']; ?> Employees</div>
                                <div class="dept-bar">
                                    <div class="dept-bar-fill" style="width: <?php echo ($dept['count'] / $total_employees) * 100; ?>%; background: <?php echo $colors[$index % 5]; ?>;"></div>
                                </div>
                            </div>
                        <?php 
                            $index++;
                        endwhile; 
                        ?>
                    </div>
                </div>
            </div>

        </main>

    </div>

    <script src="../accests/js/main.js"></script>
    <script>
        // Sidebar Toggle for Mobile
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    </script>
</body>
</html>