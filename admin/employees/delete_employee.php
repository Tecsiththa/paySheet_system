<?php
require_once '../../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../../auth/login.php');
}

$company_id = getCompanyId();

// Get employee ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('error', 'Invalid employee ID');
    redirect('view_employees.php');
}

$employee_id = clean($conn, $_GET['id']);

// Check if employee belongs to this company
$check_query = "SELECT employee_id, employee_name FROM employees WHERE employee_id = '$employee_id' AND company_id = '$company_id'";
$check_result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($check_result) == 0) {
    setMessage('error', 'Employee not found');
    redirect('view_employees.php');
}

$employee = mysqli_fetch_assoc($check_result);

// Delete employee (CASCADE will delete related records)
$delete_query = "DELETE FROM employees WHERE employee_id = '$employee_id'";

if (mysqli_query($conn, $delete_query)) {
    setMessage('success', 'Employee "' . $employee['employee_name'] . '" deleted successfully!');
} else {
    setMessage('error', 'Failed to delete employee. Please try again.');
}

redirect('view_employees.php');
?>