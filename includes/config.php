<?php
// ============================================================
// Database Configuration
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'paysheet_db');

define('SITE_NAME', 'PaySheet Pro');
define('SITE_URL', 'http://localhost/paysheet-system');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connect to DB
function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die(json_encode(['error' => 'DB Connection failed: ' . $conn->connect_error]));
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// Auth check
function requireLogin() {
    if (!isset($_SESSION['company_id'])) {
        header('Location: ' . SITE_URL . '/index.php?page=login');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['company_id']);
}

function getCurrentCompany() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $id = (int)$_SESSION['company_id'];
    $res = $db->query("SELECT * FROM companies WHERE id = $id");
    return $res->fetch_assoc();
}

// Sanitize input
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Format currency
function formatLKR($amount) {
    return 'LKR ' . number_format((float)$amount, 2);
}

// Calculate APIT Tax (monthly)
function calculateAPIT($monthlyIncome) {
    $tax = 0;
    $brackets = [
        [100000, 0.00],
        [41667,  0.06],
        [41667,  0.12],
        [41667,  0.18],
        [41667,  0.24],
        [41667,  0.30],
    ];
    $remaining = $monthlyIncome;
    foreach ($brackets as $bracket) {
        if ($remaining <= 0) break;
        $taxable = min($remaining, $bracket[0]);
        $tax += $taxable * $bracket[1];
        $remaining -= $taxable;
    }
    if ($remaining > 0) {
        $tax += $remaining * 0.36;
    }
    return round($tax, 2);
}

// Days in month
function daysInMonth($month, $year) {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

// Daily salary
function dailySalary($basicSalary, $month, $year) {
    return $basicSalary / daysInMonth($month, $year);
}
?>