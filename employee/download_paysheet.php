<?php
require_once '../config/database.php';

// Check if user is logged in and is employee
if (!isLoggedIn() || isAdmin()) {
    redirect('../auth/login.php');
}

$user_id = getCurrentUserId();

// Fetch employee details
$emp_query = "SELECT * FROM employees WHERE user_id = '$user_id'";
$emp_result = mysqli_query($conn, $emp_query);
$employee = mysqli_fetch_assoc($emp_result);
$employee_id = $employee['employee_id'];

// Get paysheet ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setMessage('error', 'Invalid paysheet ID');
    redirect('view_paysheet.php');
}

$paysheet_id = clean($conn, $_GET['id']);

// Fetch paysheet details (ensure it belongs to this employee)
$query = "SELECT p.*, c.company_name, c.company_address, c.company_phone, c.company_email
          FROM paysheets p
          JOIN employees e ON p.employee_id = e.employee_id
          JOIN companies c ON e.company_id = c.company_id
          WHERE p.paysheet_id = '$paysheet_id' AND p.employee_id = '$employee_id'";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    setMessage('error', 'Paysheet not found or access denied');
    redirect('view_paysheet.php');
}

$paysheet = mysqli_fetch_assoc($result);

// Set headers for PDF download
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paysheet - <?php echo htmlspecialchars($employee['employee_name']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: #fff;
            padding: 20px;
        }

        .pdf-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 2px solid #333;
        }

        .pdf-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-bottom: 4px solid #333;
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .company-details {
            font-size: 12px;
            opacity: 0.9;
        }

        .pdf-title {
            background: #f8f9fa;
            padding: 15px 30px;
            border-bottom: 2px solid #ddd;
        }

        .pdf-title h2 {
            font-size: 22px;
            color: #333;
            margin-bottom: 5px;
        }

        .pdf-title .month {
            font-size: 14px;
            color: #666;
        }

        .pdf-body {
            padding: 30px;
        }

        .info-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #667eea;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }

        .info-label {
            font-weight: 600;
            color: #555;
        }

        .info-value {
            color: #333;
        }

        .amounts-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .amounts-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }

        .amounts-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
        }

        .amounts-table tr:hover {
            background: #f8f9fa;
        }

        .amount-value {
            text-align: right;
            font-weight: 600;
        }

        .earnings-section {
            background: #d1fae5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .deductions-section {
            background: #fee2e2;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .total-row {
            background: #f8f9fa;
            font-weight: bold;
            font-size: 16px;
        }

        .total-row td {
            padding: 15px 12px;
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
        }

        .net-salary-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            text-align: center;
            margin: 30px 0;
            border-radius: 8px;
        }

        .net-salary-label {
            font-size: 18px;
            margin-bottom: 10px;
            display: block;
        }

        .net-salary-value {
            font-size: 36px;
            font-weight: bold;
        }

        .pdf-footer {
            background: #f8f9fa;
            padding: 20px 30px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .signature-section {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
            margin-top: 40px;
            padding-top: 20px;
        }

        .signature-box {
            text-align: center;
            padding-top: 40px;
            border-top: 2px solid #333;
        }

        .no-print {
            text-align: center;
            margin: 20px 0;
        }

        @media print {
            body {
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
            
            .pdf-container {
                border: none;
            }
        }

        .btn-print {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin: 0 10px;
        }

        .btn-print:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Print / Save as PDF</button>
        <button onclick="window.close()" class="btn-print" style="background: #6b7280;">✕ Close</button>
    </div>

    <div class="pdf-container">
        
        <!-- Header -->
        <div class="pdf-header">
            <div class="company-name"><?php echo htmlspecialchars($paysheet['company_name']); ?></div>
            <div class="company-details">
                <?php echo htmlspecialchars($paysheet['company_address']); ?> | 
                <?php echo htmlspecialchars($paysheet['company_phone']); ?> | 
                <?php echo htmlspecialchars($paysheet['company_email']); ?>
            </div>
        </div>

        <!-- Title -->
        <div class="pdf-title">
            <h2>Employee Paysheet</h2>
            <div class="month"><?php echo getMonthName($paysheet['month']) . ' ' . $paysheet['year']; ?></div>
        </div>

        <!-- Body -->
        <div class="pdf-body">
            
            <!-- Employee Information -->
            <div class="info-section">
                <div class="section-title">Employee Information</div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Employee Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($employee['employee_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Employee ID:</span>
                        <span class="info-value"><?php echo $employee['employee_id']; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">NIC:</span>
                        <span class="info-value"><?php echo htmlspecialchars($employee['employee_nic']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Position:</span>
                        <span class="info-value"><?php echo htmlspecialchars($employee['position']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Department:</span>
                        <span class="info-value"><?php echo htmlspecialchars($employee['department']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Paysheet Date:</span>
                        <span class="info-value"><?php echo formatDate($paysheet['generated_date']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Earnings -->
            <div class="earnings-section">
                <div class="section-title">💰 Earnings</div>
                <table class="amounts-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align: right;">Amount (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Basic Salary</td>
                            <td class="amount-value"><?php echo number_format($paysheet['basic_salary'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Overtime Payment</td>
                            <td class="amount-value"><?php echo number_format($paysheet['ot_payment'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Travel Allowance</td>
                            <td class="amount-value"><?php echo number_format($paysheet['travel_allowance'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Food Allowance</td>
                            <td class="amount-value"><?php echo number_format($paysheet['food_allowance'], 2); ?></td>
                        </tr>
                        <tr class="total-row">
                            <td>Total Earnings</td>
                            <td class="amount-value"><?php echo number_format($paysheet['total_earnings'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Deductions -->
            <div class="deductions-section">
                <div class="section-title">➖ Deductions</div>
                <table class="amounts-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th style="text-align: right;">Amount (LKR)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>EPF (12%)</td>
                            <td class="amount-value"><?php echo number_format($paysheet['epf_deduction'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>ETF (3%)</td>
                            <td class="amount-value"><?php echo number_format($paysheet['etf_deduction'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>APIT Tax</td>
                            <td class="amount-value"><?php echo number_format($paysheet['apit_tax'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Loan Deduction</td>
                            <td class="amount-value"><?php echo number_format($paysheet['loan_deduction'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Salary Advance</td>
                            <td class="amount-value"><?php echo number_format($paysheet['advance_deduction'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Unapproved Leave Deduction</td>
                            <td class="amount-value"><?php echo number_format($paysheet['unapproved_leave_deduction'], 2); ?></td>
                        </tr>
                        <tr class="total-row">
                            <td>Total Deductions</td>
                            <td class="amount-value"><?php echo number_format($paysheet['total_deductions'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Net Salary -->
            <div class="net-salary-section">
                <span class="net-salary-label">NET SALARY TO BE PAID</span>
                <div class="net-salary-value">LKR <?php echo number_format($paysheet['net_salary'], 2); ?></div>
            </div>

            <!-- Signatures -->
            <div class="signature-section">
                <div class="signature-box">
                    <strong>Prepared By</strong><br>
                    HR Department
                </div>
                <div class="signature-box">
                    <strong>Employee Signature</strong><br>
                    <?php echo htmlspecialchars($employee['employee_name']); ?>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div class="pdf-footer">
            <p><strong>This is a computer-generated paysheet and does not require a signature.</strong></p>
            <p>Generated on: <?php echo date('d-m-Y H:i:s'); ?></p>
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($paysheet['company_name']); ?>. All rights reserved.</p>
        </div>

    </div>

    <script>
        // Auto print on load (optional - comment out if not needed)
        // window.onload = function() { window.print(); }
    </script>

</body>
</html>