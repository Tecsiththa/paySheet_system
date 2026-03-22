<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();
$username = $_SESSION['username'];
$company_name = $_SESSION['company_name'];

// Search and Filter
$search = isset($_GET['search']) ? clean($conn, $_GET['search']) : '';
$department_filter = isset($_GET['department']) ? clean($conn, $_GET['department']) : '';

// Build query
$query = "SELECT * FROM employees WHERE company_id = '$company_id'";

if (!empty($search)) {
    $query .= " AND (employee_name LIKE '%$search%' OR employee_nic LIKE '%$search%' OR employee_email LIKE '%$search%')";
}

if (!empty($department_filter)) {
    $query .= " AND department = '$department_filter'";
}

$query .= " ORDER BY employee_id DESC";

$result = mysqli_query($conn, $query);

// Get all departments for filter
$dept_query = "SELECT DISTINCT department FROM employees WHERE company_id = '$company_id' ORDER BY department";
$dept_result = mysqli_query($conn, $dept_query);

// Count total employees
$count_query = "SELECT COUNT(*) as total FROM employees WHERE company_id = '$company_id' AND status = 'active'";
$count_result = mysqli_query($conn, $count_query);
$total_employees = mysqli_fetch_assoc($count_result)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Employees - PaySheetPro</title>
    <link rel="stylesheet" href="../../accests/css/style.css">
    <link rel="stylesheet" href="../../accests/css/dashboard.css">
    <link rel="stylesheet" href="../../accests/css/employee.css">
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
                    <li class="nav-item active">
                        <a href="view_employees.php" class="nav-link">
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
                    <h1 class="page-title">Employees</h1>
                    <p class="page-subtitle">Manage your company employees</p>
                </div>
                <div class="page-actions">
                    <a href="add_employee.php" class="btn-primary">
                        <span>+ Add Employee</span>
                    </a>
                </div>
            </div>

            <?php displayMessage(); ?>

            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-icon">👥</span>
                    <div>
                        <div class="stat-value"><?php echo $total_employees; ?></div>
                        <div class="stat-label">Total Employees</div>
                    </div>
                </div>
                <div class="stat-item">
                    <span class="stat-icon">📊</span>
                    <div>
                        <div class="stat-value"><?php echo mysqli_num_rows($result); ?></div>
                        <div class="stat-label">Showing Results</div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="filter-bar">
                <form method="GET" action="" class="filter-form">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="search" class="search-input" 
                               placeholder="Search by name, NIC, or email..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <select name="department" class="filter-select">
                        <option value="">All Departments</option>
                        <?php while ($dept = mysqli_fetch_assoc($dept_result)): ?>
                            <option value="<?php echo htmlspecialchars($dept['department']); ?>" 
                                    <?php echo ($department_filter == $dept['department']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['department']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    
                    <button type="submit" class="btn-primary btn-small">Filter</button>
                    
                    <?php if (!empty($search) || !empty($department_filter)): ?>
                        <a href="view_employees.php" class="btn-secondary btn-small">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Employees Table -->
            <div class="table-card">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="employee-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>NIC</th>
                                    <th>Contact</th>
                                    <th>Position</th>
                                    <th>Department</th>
                                    <th>Salary</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($employee = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td>
                                            <div class="employee-info">
                                                <div class="employee-avatar">
                                                    <?php echo strtoupper(substr($employee['employee_name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="employee-name">
                                                        <?php echo htmlspecialchars($employee['employee_name']); ?>
                                                    </div>
                                                    <div class="employee-meta">
                                                        ID: <?php echo $employee['employee_id']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($employee['employee_nic']); ?></td>
                                        <td>
                                            <div class="contact-info">
                                                <div>📞 <?php echo htmlspecialchars($employee['employee_phone']); ?></div>
                                                <div>✉️ <?php echo htmlspecialchars($employee['employee_email']); ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                        <td>
                                            <span class="badge badge-blue">
                                                <?php echo htmlspecialchars($employee['department']); ?>
                                            </span>
                                        </td>
                                        <td class="salary-cell">
                                            <?php echo formatCurrency($employee['basic_salary']); ?>
                                        </td>
                                        <td>
                                            <?php if ($employee['status'] == 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="edit_employee.php?id=<?php echo $employee['employee_id']; ?>" 
                                                   class="btn-action btn-edit" title="Edit">
                                                    ✏️
                                                </a>
                                                <a href="delete_employee.php?id=<?php echo $employee['employee_id']; ?>" 
                                                   class="btn-action btn-delete" 
                                                   onclick="return confirm('Are you sure you want to delete this employee?')" 
                                                   title="Delete">
                                                    🗑️
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
                        <span class="empty-icon">👥</span>
                        <p>No employees found</p>
                        <?php if (!empty($search) || !empty($department_filter)): ?>
                            <p class="empty-subtitle">Try adjusting your search or filters</p>
                            <a href="view_employees.php" class="btn-secondary">Clear Filters</a>
                        <?php else: ?>
                            <a href="add_employee.php" class="btn-primary">Add First Employee</a>
                        <?php endif; ?>
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