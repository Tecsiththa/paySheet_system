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
$employee_id = $_SESSION['employee_id'];
$emp_query = "SELECT * FROM employees WHERE employee_id = '$employee_id'";
$emp_result = mysqli_query($conn, $emp_query);
$employee = mysqli_fetch_assoc($emp_result);
$employee_id = $employee['employee_id'];

// Fetch current leave balance
$current_year = date('Y');
$leave_balance_query = "SELECT * FROM leave_balance WHERE employee_id = '$employee_id' AND year = '$current_year'";
$leave_balance_result = mysqli_query($conn, $leave_balance_query);
$leave_balance = mysqli_num_rows($leave_balance_result) > 0 ? mysqli_fetch_assoc($leave_balance_result) : null;

// Fetch leave history with filters
$status_filter = isset($_GET['status']) ? clean($conn, $_GET['status']) : '';
$year_filter = isset($_GET['year']) ? clean($conn, $_GET['year']) : $current_year;

$history_query = "SELECT lr.*, lt.leave_name, u.username as approved_by_name
                  FROM leave_records lr
                  JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id
                  LEFT JOIN users u ON lr.approved_by = u.user_id
                  WHERE lr.employee_id = '$employee_id' AND YEAR(lr.start_date) = '$year_filter'";

if (!empty($status_filter)) {
    $history_query .= " AND lr.status = '$status_filter'";
}

$history_query .= " ORDER BY lr.applied_date DESC";

$history_result = mysqli_query($conn, $history_query);

// Statistics
$approved_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_records WHERE employee_id = '$employee_id' AND status = 'approved' AND YEAR(start_date) = '$year_filter'"))['count'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_records WHERE employee_id = '$employee_id' AND status = 'pending' AND YEAR(start_date) = '$year_filter'"))['count'];
$rejected_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_records WHERE employee_id = '$employee_id' AND status = 'rejected' AND YEAR(start_date) = '$year_filter'"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Balance - PaySheetPro</title>
    <link rel="stylesheet" href="../accests/css/style.css">
    <link rel="stylesheet" href="../accests/css/dashboard.css">
    <link rel="stylesheet" href="../accests/css/leave.css">
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
                    <li class="nav-item">
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
                    <li class="nav-item active">
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
                    <h1 class="page-title">Leave Balance & History</h1>
                    <p class="page-subtitle">View your leave balance and request history</p>
                </div>
                <div class="page-actions">
                    <a href="request_leave.php" class="btn-primary">
                        <span>+ Request Leave</span>
                    </a>
                </div>
            </div>

            <?php displayMessage(); ?>

            <!-- Leave Balance Cards -->
            <?php if ($leave_balance): ?>
                <div class="leave-balance-cards">
                    <div class="balance-card annual">
                        <div class="card-icon">📅</div>
                        <div class="card-content">
                            <div class="card-label">Annual Leave</div>
                            <div class="card-value"><?php echo $leave_balance['annual_leave_remaining']; ?> <span class="card-unit">days</span></div>
                            <div class="card-subtitle">Out of 14 days</div>
                        </div>
                    </div>

                    <div class="balance-card casual">
                        <div class="card-icon">🏖️</div>
                        <div class="card-content">
                            <div class="card-label">Casual Leave</div>
                            <div class="card-value"><?php echo $leave_balance['casual_leave_remaining']; ?> <span class="card-unit">days</span></div>
                            <div class="card-subtitle">Out of 7 days</div>
                        </div>
                    </div>

                    <div class="balance-card sick">
                        <div class="card-icon">🏥</div>
                        <div class="card-content">
                            <div class="card-label">Sick Leave</div>
                            <div class="card-value"><?php echo $leave_balance['sick_leave_remaining']; ?> <span class="card-unit">days</span></div>
                            <div class="card-subtitle">Out of 7 days</div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    Leave balance not initialized for <?php echo $current_year; ?>. Please contact your administrator.
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="leave-stats">
                <div class="stat-card approved">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $approved_count; ?></div>
                        <div class="stat-label">Approved</div>
                    </div>
                </div>

                <div class="stat-card pending">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $pending_count; ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>

                <div class="stat-card rejected">
                    <div class="stat-icon">❌</div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo $rejected_count; ?></div>
                        <div class="stat-label">Rejected</div>
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

                    <select name="year" class="filter-select">
                        <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($year_filter == $y) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <button type="submit" class="btn-primary btn-small">Filter</button>
                    
                    <?php if (!empty($status_filter)): ?>
                        <a href="leave_balance.php" class="btn-secondary btn-small">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Leave History Table -->
            <div class="table-card">
                <div class="card-header">
                    <h3 class="card-title">Leave History (<?php echo $year_filter; ?>)</h3>
                </div>
                
                <?php if (mysqli_num_rows($history_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="leave-history-table">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Applied Date</th>
                                    <th>Status</th>
                                    <th>Approved By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($record = mysqli_fetch_assoc($history_result)): ?>
                                    <tr>
                                        <td>
                                            <span class="leave-type-badge <?php echo strtolower(str_replace(' ', '-', $record['leave_name'])); ?>">
                                                <?php echo htmlspecialchars($record['leave_name']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($record['start_date']); ?></td>
                                        <td><?php echo formatDate($record['end_date']); ?></td>
                                        <td class="text-center"><?php echo $record['days_count']; ?></td>
                                        <td><?php echo htmlspecialchars($record['reason']); ?></td>
                                        <td><?php echo formatDate($record['applied_date']); ?></td>
                                        <td>
                                            <?php if ($record['status'] == 'pending'): ?>
                                                <span class="status-badge status-pending">⏳ Pending</span>
                                            <?php elseif ($record['status'] == 'approved'): ?>
                                                <span class="status-badge status-approved">✓ Approved</span>
                                            <?php else: ?>
                                                <span class="status-badge status-rejected">✗ Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            if ($record['status'] == 'approved' || $record['status'] == 'rejected') {
                                                echo htmlspecialchars($record['approved_by_name']);
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <span class="empty-icon">📅</span>
                        <h3>No Leave Records</h3>
                        <p>You haven't requested any leave in <?php echo $year_filter; ?></p>
                        <a href="request_leave.php" class="btn-primary">Request Leave</a>
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

    <style>
        .leave-balance-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }

        .balance-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 28px;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 20px;
            border-left: 5px solid;
            transition: all var(--transition-normal);
        }

        .balance-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-xl);
        }

        .balance-card.annual {
            border-left-color: #3b82f6;
        }

        .balance-card.casual {
            border-left-color: #8b5cf6;
        }

        .balance-card.sick {
            border-left-color: #ef4444;
        }

        .card-icon {
            font-size: 48px;
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--border-radius-md);
        }

        .balance-card.annual .card-icon {
            background: rgba(59, 130, 246, 0.1);
        }

        .balance-card.casual .card-icon {
            background: rgba(139, 92, 246, 0.1);
        }

        .balance-card.sick .card-icon {
            background: rgba(239, 68, 68, 0.1);
        }

        .card-content {
            flex: 1;
        }

        .card-label {
            font-size: 14px;
            color: var(--text-secondary);
            margin-bottom: 8px;
            font-weight: 500;
        }

        .card-value {
            font-size: 36px;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1;
        }

        .card-unit {
            font-size: 16px;
            font-weight: 400;
        }

        .card-subtitle {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .leave-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 16px;
            border-left: 4px solid;
        }

        .stat-card.approved {
            border-left-color: #10b981;
        }

        .stat-card.pending {
            border-left-color: #f59e0b;
        }

        .stat-card.rejected {
            border-left-color: #ef4444;
        }

        .stat-icon {
            font-size: 32px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }

        .leave-history-table {
            width: 100%;
            border-collapse: collapse;
        }

        .leave-history-table thead {
            background: var(--gray-50);
        }

        .leave-history-table th {
            padding: 16px;
            text-align: left;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            border-bottom: 2px solid var(--border-color);
        }

        .leave-history-table td {
            padding: 16px;
            font-size: 14px;
            color: var(--text-secondary);
            border-bottom: 1px solid var(--border-color);
        }

        .leave-history-table tbody tr:hover {
            background: var(--gray-50);
        }

        @media (max-width: 1024px) {
            .leave-balance-cards,
            .leave-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>