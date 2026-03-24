<?php
/**
 * Database Configuration File
 * Paysheet Management System
 * 
 *
 */

// Database connection parameters
define('DB_HOST', 'localhost');      // Database server (XAMPP default: localhost)
define('DB_USER', 'root');           // Database username (XAMPP default: root)
define('DB_PASS', '');               // Database password (XAMPP default: empty)
define('DB_NAME', 'paysheet_db');    // Database name

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Set character set to UTF-8 (Sinhala/Tamil support )
mysqli_set_charset($conn, "utf8mb4");

// Session start කරන්න (Login system )
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Helper function - Prevent SQL Injection
 *  $safe_value = clean($conn, $_POST['input']);
 */
function clean($conn, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

/**
 * Helper function - Redirect
 *  redirect('admin/dashboard.php');
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Helper function - Check if user is logged in
 *  if (!isLoggedIn()) { redirect('../auth/login.php'); }
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Helper function - Check if user is admin
 *  if (!isAdmin()) { redirect('../employee/employee_dashboard.php'); }
 */
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Helper function - Get current user ID
 *  $user_id = getCurrentUserId();
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Helper function - Get current company ID
 *  $company_id = getCompanyId();
 */
function getCompanyId() {
    return isset($_SESSION['company_id']) ? $_SESSION['company_id'] : null;
}

/**
 * Helper function - Show success message
 *  setMessage('success', 'Employee added successfully!');
 */
function setMessage($type, $message) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type; // success, error, warning, info
}

/**
 * Helper function - Display and clear message
 *  displayMessage();
 */
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $message = $_SESSION['message'];
        
        echo '<div class="alert alert-' . $type . '">';
        echo htmlspecialchars($message);
        echo '</div>';
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

/**
 * Helper function - Format currency (LKR)
 *  echo formatCurrency(50000); // Output: LKR 50,000.00
 */
function formatCurrency($amount) {
    return 'LKR ' . number_format($amount, 2);
}

/**
 * Helper function - Format date
 *  echo formatDate('2024-03-15'); // Output: 15-03-2024
 */
function formatDate($date) {
    return date('d-m-Y', strtotime($date));
}

/**
 * Helper function - Get month name
 *  echo getMonthName(3); // Output: March
 */
function getMonthName($month) {
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    return $months[$month] ?? '';
}



?>
