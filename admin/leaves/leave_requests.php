<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();
$username = $_SESSION['username'];
$company_name = $_SESSION['company_name'];

// Fetch pending leave requests
$query = "SELECT lr.*, e.employee_name, e.position, e.department, lt.leave_name, lt.days_per_year
          FROM leave_records lr
          JOIN employees e ON lr.employee_id = e.employee_id
          JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id
          WHERE e.company_id = '$company_id' AND lr.status = 'pending'
          ORDER BY lr.leave_id DESC";

$result = mysqli_query($conn, $query);

// Count pending requests
$pending_count = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests - PaySheetPro</title>
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
                            <?php if ($pending_count > 0): ?>
                                <span class="nav-badge"><?php echo $pending_count; ?></span>
                            <?php endif; ?>
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
                    <h1 class="page-title">Pending Leave Requests</h1>
                    <p class="page-subtitle">Review and approve employee leave applications</p>
                </div>
                <div class="page-actions">
                    <a href="leave_history.php" class="btn-secondary">
                        <span>View History</span>
                    </a>
                </div>
            </div>

            <?php displayMessage(); ?>

            <!-- Stats -->
            <div class="leave-stats">
                <div class="leave-stat-card pending">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-details">
                        <div class="stat-value"><?php echo $pending_count; ?></div>
                        <div class="stat-label">Pending Requests</div>
                    </div>
                </div>
            </div>

            <!-- Leave Requests -->
            <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="leave-requests-grid">
                    <?php while ($leave = mysqli_fetch_assoc($result)): 
                        // Calculate remaining days for this leave type
                        $year = date('Y', strtotime($leave['start_date']));
                        $employee_id = $leave['employee_id'];
                        $leave_type_id = $leave['leave_type_id'];
                        
                        $balance_query = "SELECT * FROM leave_balance WHERE employee_id = '$employee_id' AND year = '$year'";
                        $balance_result = mysqli_query($conn, $balance_query);
                        $balance = mysqli_fetch_assoc($balance_result);
                        
                        // Determine remaining days based on leave type
                        $remaining_days = 0;
                        if ($leave_type_id == 1) { // Annual Leave
                            $remaining_days = $balance['annual_leave_remaining'] ?? 14;
                        } elseif ($leave_type_id == 2) { // Casual Leave
                            $remaining_days = $balance['casual_leave_remaining'] ?? 7;
                        } elseif ($leave_type_id == 3) { // Sick Leave
                            $remaining_days = $balance['sick_leave_remaining'] ?? 7;
                        }
                    ?>
                        <div class="leave-request-card">
                            <div class="leave-card-header">
                                <div class="employee-info-leave">
                                    <div class="employee-avatar-leave">
                                        <?php echo strtoupper(substr($leave['employee_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="employee-name-leave">
                                            <?php echo htmlspecialchars($leave['employee_name']); ?>
                                        </div>
                                        <div class="employee-position-leave">
                                            <?php echo htmlspecialchars($leave['position']); ?> • 
                                            <?php echo htmlspecialchars($leave['department']); ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="leave-type-badge <?php echo strtolower(str_replace(' ', '-', $leave['leave_name'])); ?>">
                                    <?php echo htmlspecialchars($leave['leave_name']); ?>
                                </span>
                            </div>

                            <div class="leave-card-body">
                                <div class="leave-details-grid">
                                    <div class="leave-detail-item">
                                        <span class="detail-icon">📅</span>
                                        <div>
                                            <div class="detail-label">Start Date</div>
                                            <div class="detail-value"><?php echo formatDate($leave['start_date']); ?></div>
                                        </div>
                                    </div>

                                    <div class="leave-detail-item">
                                        <span class="detail-icon">📅</span>
                                        <div>
                                            <div class="detail-label">End Date</div>
                                            <div class="detail-value"><?php echo formatDate($leave['end_date']); ?></div>
                                        </div>
                                    </div>

                                    <div class="leave-detail-item">
                                        <span class="detail-icon">🕒</span>
                                        <div>
                                            <div class="detail-label">Duration</div>
                                            <div class="detail-value"><?php echo $leave['days_count']; ?> days</div>
                                        </div>
                                    </div>

                                    <div class="leave-detail-item">
                                        <span class="detail-icon">📊</span>
                                        <div>
                                            <div class="detail-label">Remaining</div>
                                            <div class="detail-value"><?php echo $remaining_days; ?> days</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="leave-reason">
                                    <div class="reason-label">Reason:</div>
                                    <div class="reason-text"><?php echo nl2br(htmlspecialchars($leave['reason'])); ?></div>
                                </div>

                                <div class="leave-meta">
                                    <span class="meta-item">
                                        <span class="meta-icon">🕐</span>
                                        Applied on <?php echo formatDate($leave['applied_date']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="leave-card-actions">
                                <?php if ($leave['days_count'] > $remaining_days): ?>
                                    <div class="alert alert-warning" style="margin-bottom: 12px; padding: 12px;">
                                        <strong>Warning:</strong> Employee has only <?php echo $remaining_days; ?> days remaining. 
                                        Approving will result in negative balance.
                                    </div>
                                <?php endif; ?>
                                
                                <a href="approve_leave.php?id=<?php echo $leave['leave_id']; ?>&action=approve" 
                                   class="btn-approve" 
                                   onclick="return confirm('Are you sure you want to approve this leave request?')">
                                    <span>✓ Approve</span>
                                </a>
                                <a href="approve_leave.php?id=<?php echo $leave['leave_id']; ?>&action=reject" 
                                   class="btn-reject"
                                   onclick="return confirm('Are you sure you want to reject this leave request?')">
                                    <span>✕ Reject</span>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <span class="empty-icon">✅</span>
                    <h3>No Pending Leave Requests</h3>
                    <p>All leave requests have been processed</p>
                    <a href="leave_history.php" class="btn-secondary">View Leave History</a>
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