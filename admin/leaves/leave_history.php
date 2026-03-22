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
$employee_filter = isset($_GET['employee']) ? clean($conn, $_GET['employee']) : '';
$leave_type_filter = isset($_GET['leave_type']) ? clean($conn, $_GET['leave_type']) : '';
$month_filter = isset($_GET['month']) ? clean($conn, $_GET['month']) : '';
$year_filter = isset($_GET['year']) ? clean($conn, $_GET['year']) : date('Y');

// Build query
$query = "SELECT lr.*, e.employee_name, e.position, e.department, lt.leave_name, u.username as approved_by_name
          FROM leave_records lr
          JOIN employees e ON lr.employee_id = e.employee_id
          JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id
          LEFT JOIN users u ON lr.approved_by = u.user_id
          WHERE e.company_id = '$company_id'";

if (!empty($status_filter)) {
    $query .= " AND lr.status = '$status_filter'";
}

if (!empty($employee_filter)) {
    $query .= " AND lr.employee_id = '$employee_filter'";
}

if (!empty($leave_type_filter)) {
    $query .= " AND lr.leave_type_id = '$leave_type_filter'";
}

if (!empty($month_filter) && !empty($year_filter)) {
    $query .= " AND MONTH(lr.start_date) = '$month_filter' AND YEAR(lr.start_date) = '$year_filter'";
} elseif (!empty($year_filter)) {
    $query .= " AND YEAR(lr.start_date) = '$year_filter'";
}

$query .= " ORDER BY lr.leave_id DESC";

$result = mysqli_query($conn, $query);

// Get all employees for filter
$employees_query = "SELECT employee_id, employee_name FROM employees WHERE company_id = '$company_id' AND status = 'active' ORDER BY employee_name";
$employees_result = mysqli_query($conn, $employees_query);

// Get leave types for filter
$leave_types_query = "SELECT * FROM leave_types ORDER BY leave_type_id";
$leave_types_result = mysqli_query($conn, $leave_types_query);

// Statistics
$total_leaves = mysqli_num_rows($result);
$approved_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_records lr JOIN employees e ON lr.employee_id = e.employee_id WHERE e.company_id = '$company_id' AND lr.status = 'approved' AND YEAR(lr.start_date) = '$year_filter'");
$approved_count = mysqli_fetch_assoc($approved_query)['count'];

$rejected_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_records lr JOIN employees e ON lr.employee_id = e.employee_id WHERE e.company_id = '$company_id' AND lr.status = 'rejected' AND YEAR(lr.start_date) = '$year_filter'");
$rejected_count = mysqli_fetch_assoc($rejected_query)['count'];

$pending_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_records lr JOIN employees e ON lr.employee_id = e.employee_id WHERE e.company_id = '$company_id' AND lr.status = 'pending'");
$pending_count = mysqli_fetch_assoc($pending_query)['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave History - PaySheetPro</title>
    <link rel="stylesheet" href="../../accests/css/style.css">
    <link rel="stylesheet" href="../../accests/css/dashboard.css">
    <link rel="stylesheet" href="../../accests/css/leave.css">
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
                    <li class="nav-item active">
                        <a href="leave_requests.php" class="nav-link">
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
                    <h1 class="page-title">Leave History</h1>
                    <p class="page-subtitle">View all leave records</p>
                </div>
                <div class="page-actions">
                    <a href="leave_requests.php" class="btn-primary">
                        <span>← Pending Requests</span>
                    </a>
                </div>
            </div>

            <?php displayMessage(); ?>

            <!-- Statistics -->
            <div class="leave-stats">
                <div class="leave-stat-card approved">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $approved_count; ?></div>
                        <div class="stat-label">Approved (<?php echo $year_filter; ?>)</div>
                    </div>
                </div>

                <div class="leave-stat-card rejected">
                    <div class="stat-icon">❌</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $rejected_count; ?></div>
                        <div class="stat-label">Rejected (<?php echo $year_filter; ?>)</div>
                    </div>
                </div>

                <div class="leave-stat-card pending">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $pending_count; ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-bar">
                <form method="GET" action="" class="filter-form">
                    <select name="status" class="filter-select">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo ($status_filter == 'approved') ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo ($status_filter == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                    </select>

                    <select name="employee" class="filter-select">
                        <option value="">All Employees</option>
                        <?php while ($emp = mysqli_fetch_assoc($employees_result)): ?>
                            <option value="<?php echo $emp['employee_id']; ?>" 
                                    <?php echo ($employee_filter == $emp['employee_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($emp['employee_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>

                    <select name="leave_type" class="filter-select">
                        <option value="">All Leave Types</option>
                        <?php while ($lt = mysqli_fetch_assoc($leave_types_result)): ?>
                            <option value="<?php echo $lt['leave_type_id']; ?>" 
                                    <?php echo ($leave_type_filter == $lt['leave_type_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($lt['leave_name']); ?>
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
                    
                    <?php if (!empty($status_filter) || !empty($employee_filter) || !empty($leave_type_filter) || !empty($month_filter)): ?>
                        <a href="leave_history.php" class="btn-secondary btn-small">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Leave History Table -->
            <div class="table-card">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="leave-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Applied On</th>
                                    <th>Status</th>
                                    <th>Approved By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($leave = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <div class="employee-info-table">
                                                <div class="employee-avatar-small">
                                                    <?php echo strtoupper(substr($leave['employee_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="employee-name-table">
                                                        <?php echo htmlspecialchars($leave['employee_name']); ?>
                                                    </div>
                                                    <div class="employee-dept-table">
                                                        <?php echo htmlspecialchars($leave['department']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="leave-type-badge-small <?php echo strtolower(str_replace(' ', '-', $leave['leave_name'])); ?>">
                                                <?php echo htmlspecialchars($leave['leave_name']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($leave['start_date']); ?></td>
                                        <td><?php echo formatDate($leave['end_date']); ?></td>
                                        <td class="days-cell"><?php echo $leave['days_count']; ?> days</td>
                                        <td><?php echo formatDate($leave['applied_date']); ?></td>
                                        <td>
                                            <?php if ($leave['status'] == 'approved'): ?>
                                                <span class="status-badge status-approved">✓ Approved</span>
                                            <?php elseif ($leave['status'] == 'rejected'): ?>
                                                <span class="status-badge status-rejected">✕ Rejected</span>
                                            <?php else: ?>
                                                <span class="status-badge status-pending">⏳ Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($leave['approved_by_name']): ?>
                                                <span class="approved-by"><?php echo htmlspecialchars($leave['approved_by_name']); ?></span>
                                                <br>
                                                <span class="approved-date"><?php echo formatDate($leave['approved_date']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon">📅</span>
                        <h3>No Leave Records Found</h3>
                        <p>Try adjusting your filters</p>
                        <a href="leave_history.php" class="btn-secondary">Clear Filters</a>
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