<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();
$user_id = getCurrentUserId();

// Get leave ID and action
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    setMessage('error', 'Invalid request');
    redirect('leave_requests.php');
}

$leave_id = clean($conn, $_GET['id']);
$action = clean($conn, $_GET['action']);

// Validate action
if (!in_array($action, ['approve', 'reject'])) {
    setMessage('error', 'Invalid action');
    redirect('leave_requests.php');
}

// Fetch leave request details
$query = "SELECT lr.*, e.company_id, e.employee_name 
          FROM leave_records lr
          JOIN employees e ON lr.employee_id = e.employee_id
          WHERE lr.leave_id = '$leave_id' AND e.company_id = '$company_id' AND lr.status = 'pending'";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    setMessage('error', 'Leave request not found or already processed');
    redirect('leave_requests.php');
}

$leave = mysqli_fetch_assoc($result);

// Update leave status
$new_status = ($action == 'approve') ? 'approved' : 'rejected';
$update_query = "UPDATE leave_records SET 
                status = '$new_status',
                approved_by = '$user_id',
                approved_date = NOW()
                WHERE leave_id = '$leave_id'";

if (mysqli_query($conn, $update_query)) {
    
    // If approved, update leave balance
    if ($action == 'approve') {
        $employee_id = $leave['employee_id'];
        $leave_type_id = $leave['leave_type_id'];
        $days_count = $leave['days_count'];
        $year = date('Y', strtotime($leave['start_date']));
        
        // Check if leave balance exists
        $balance_check = mysqli_query($conn, "SELECT * FROM leave_balance WHERE employee_id = '$employee_id' AND year = '$year'");
        
        if (mysqli_num_rows($balance_check) == 0) {
            // Create leave balance for this year
            mysqli_query($conn, "INSERT INTO leave_balance (employee_id, year) VALUES ('$employee_id', '$year')");
        }
        
        // Update appropriate leave balance
        $balance_field = '';
        if ($leave_type_id == 1) { // Annual Leave
            $balance_field = 'annual_leave_remaining';
        } elseif ($leave_type_id == 2) { // Casual Leave
            $balance_field = 'casual_leave_remaining';
        } elseif ($leave_type_id == 3) { // Sick Leave
            $balance_field = 'sick_leave_remaining';
        }
        
        if (!empty($balance_field)) {
            $update_balance = "UPDATE leave_balance SET 
                              $balance_field = $balance_field - $days_count 
                              WHERE employee_id = '$employee_id' AND year = '$year'";
            mysqli_query($conn, $update_balance);
        }
    }
    
    $status_text = ($action == 'approve') ? 'approved' : 'rejected';
    setMessage('success', 'Leave request for ' . $leave['employee_name'] . ' has been ' . $status_text);
} else {
    setMessage('error', 'Failed to process leave request');
}

redirect('leave_requests.php');
?>