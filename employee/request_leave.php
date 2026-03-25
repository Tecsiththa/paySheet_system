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
$company_id = $employee['company_id'];

// Fetch leave types
$leave_types_query = "SELECT * FROM leave_types ORDER BY leave_type_id";
$leave_types_result = mysqli_query($conn, $leave_types_query);

// Fetch current leave balance
$current_year = date('Y');
$leave_balance_query = "SELECT * FROM leave_balance WHERE employee_id = '$employee_id' AND year = '$current_year'";
$leave_balance_result = mysqli_query($conn, $leave_balance_query);
$leave_balance = mysqli_num_rows($leave_balance_result) > 0 ? mysqli_fetch_assoc($leave_balance_result) : null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leave_type_id = clean($conn, $_POST['leave_type_id']);
    $start_date = clean($conn, $_POST['start_date']);
    $end_date = clean($conn, $_POST['end_date']);
    $reason = clean($conn, $_POST['reason']);
    
    $errors = [];
    
    // Validation
    if (empty($leave_type_id)) $errors[] = "Please select a leave type";
    if (empty($start_date)) $errors[] = "Start date is required";
    if (empty($end_date)) $errors[] = "End date is required";
    if ($end_date < $start_date) $errors[] = "End date cannot be before start date";
    
    if (empty($errors)) {
        // Calculate days
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        $days_count = $interval->days + 1;
        
        // Insert leave request
        $insert = "INSERT INTO leave_records (employee_id, leave_type_id, start_date, end_date, days_count, reason, status)
                   VALUES ('$employee_id', '$leave_type_id', '$start_date', '$end_date', '$days_count', '$reason', 'pending')";
        
        if (mysqli_query($conn, $insert)) {
            setMessage('success', 'Leave request submitted successfully! Waiting for approval.');
            redirect('leave_balance.php');
        } else {
            $errors[] = "Failed to submit leave request. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Leave - PaySheetPro</title>
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
                    <li class="nav-item active">
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
                    <h1 class="page-title">Request Leave</h1>
                    <p class="page-subtitle">Submit a new leave request</p>
                </div>
                <div class="page-actions">
                    <a href="leave_balance.php" class="btn-secondary">
                        <span>View Leave History</span>
                    </a>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <strong>Please fix the following errors:</strong>
                    <ul style="margin: 8px 0 0 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Current Leave Balance -->
            <?php if ($leave_balance): ?>
                <div class="leave-balance-card">
                    <h3 class="balance-title">Your Current Leave Balance (<?php echo $current_year; ?>)</h3>
                    <div class="balance-grid">
                        <div class="balance-item annual">
                            <div class="balance-icon">📅</div>
                            <div class="balance-info">
                                <div class="balance-count"><?php echo $leave_balance['annual_leave_remaining']; ?></div>
                                <div class="balance-label">Annual Leave</div>
                            </div>
                        </div>
                        <div class="balance-item casual">
                            <div class="balance-icon">🏖️</div>
                            <div class="balance-info">
                                <div class="balance-count"><?php echo $leave_balance['casual_leave_remaining']; ?></div>
                                <div class="balance-label">Casual Leave</div>
                            </div>
                        </div>
                        <div class="balance-item sick">
                            <div class="balance-icon">🏥</div>
                            <div class="balance-info">
                                <div class="balance-count"><?php echo $leave_balance['sick_leave_remaining']; ?></div>
                                <div class="balance-label">Sick Leave</div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Leave Request Form -->
            <div class="form-card">
                <form method="POST" action="" id="leaveRequestForm">
                    
                    <div class="form-grid">
                        <div class="form-group form-group-full">
                            <label for="leave_type_id" class="form-label">
                                <span class="label-icon">📋</span>
                                Leave Type
                            </label>
                            <select id="leave_type_id" name="leave_type_id" class="form-input" required>
                                <option value="">-- Select Leave Type --</option>
                                <?php while ($type = mysqli_fetch_assoc($leave_types_result)): ?>
                                    <option value="<?php echo $type['leave_type_id']; ?>">
                                        <?php echo htmlspecialchars($type['leave_name']) . ' (' . $type['days_per_year'] . ' days)'; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_date" class="form-label">
                                <span class="label-icon">📆</span>
                                Start Date
                            </label>
                            <input type="date" id="start_date" name="start_date" class="form-input" 
                                   required min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo isset($start_date) ? $start_date : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="end_date" class="form-label">
                                <span class="label-icon">📆</span>
                                End Date
                            </label>
                            <input type="date" id="end_date" name="end_date" class="form-input" 
                                   required min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo isset($end_date) ? $end_date : ''; ?>">
                        </div>

                        <div class="form-group form-group-full">
                            <label for="days_display" class="form-label">
                                <span class="label-icon">🔢</span>
                                Number of Days
                            </label>
                            <input type="text" id="days_display" class="form-input" readonly 
                                   placeholder="Select dates to calculate" style="background: #f3f4f6;">
                        </div>

                        <div class="form-group form-group-full">
                            <label for="reason" class="form-label">
                                <span class="label-icon">✍️</span>
                                Reason for Leave
                            </label>
                            <textarea id="reason" name="reason" class="form-input" rows="4" 
                                      placeholder="Enter reason for leave request"><?php echo isset($reason) ? htmlspecialchars($reason) : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary btn-large">
                            <span>Submit Leave Request</span>
                            <span class="btn-icon">✓</span>
                        </button>
                        <a href="employee_dashboard.php" class="btn-secondary btn-large">
                            <span>Cancel</span>
                        </a>
                    </div>
                </form>
            </div>

        </main>

    </div>

    <script src="../accests/js/main.js"></script>
    <script>
        // Sidebar Toggle
        document.getElementById('menuToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Calculate days
        function calculateDays() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                
                if (end >= start) {
                    document.getElementById('days_display').value = diffDays + ' day(s)';
                } else {
                    document.getElementById('days_display').value = 'Invalid date range';
                }
            }
        }

        document.getElementById('start_date').addEventListener('change', calculateDays);
        document.getElementById('end_date').addEventListener('change', calculateDays);
    </script>

    <style>
        .leave-balance-card {
            background: white;
            border-radius: var(--border-radius-lg);
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
        }

        .balance-title {
            font-size: var(--font-lg);
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 20px;
        }

        .balance-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .balance-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            border-radius: var(--border-radius-md);
            border-left: 4px solid;
        }

        .balance-item.annual {
            background: rgba(59, 130, 246, 0.05);
            border-left-color: #3b82f6;
        }

        .balance-item.casual {
            background: rgba(139, 92, 246, 0.05);
            border-left-color: #8b5cf6;
        }

        .balance-item.sick {
            background: rgba(239, 68, 68, 0.05);
            border-left-color: #ef4444;
        }

        .balance-icon {
            font-size: 32px;
        }

        .balance-count {
            font-size: 28px;
            font-weight: 700;
            color: var(--text-primary);
        }

        .balance-label {
            font-size: 14px;
            color: var(--text-secondary);
        }

        @media (max-width: 768px) {
            .balance-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</body>
</html>