<?php
/**
 * Password Migration Script
 * This script migrates any plain text passwords in the database to hashed passwords
 * Run this once, then delete the file for security
 */

require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    die("Access Denied. Admin login required.");
}

$migration_results = [];
$errors = [];

try {
    // Get all users with passwords
    $query = "SELECT user_id, username, password FROM users";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception("Database query failed: " . mysqli_error($conn));
    }
    
    $updated_count = 0;
    $already_hashed_count = 0;
    
    while ($user = mysqli_fetch_assoc($result)) {
        $password = $user['password'];
        
        // Check if password is already hashed (hashed passwords start with $2y$ or $2a$)
        if (password_needs_rehash($password, PASSWORD_DEFAULT) === false && 
            (strpos($password, '$2y$') === 0 || strpos($password, '$2a$') === 0)) {
            // Already hashed
            $already_hashed_count++;
        } else {
            // Plain text password - hash it
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $update_query = "UPDATE users SET password = '$hashed_password' WHERE user_id = '{$user['user_id']}'";
            
            if (mysqli_query($conn, $update_query)) {
                $updated_count++;
                $migration_results[] = "✓ User '{$user['username']}' - Password hashed successfully";
            } else {
                $errors[] = "✗ User '{$user['username']}' - Failed to hash password";
            }
        }
    }
} catch (Exception $e) {
    $errors[] = "Error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Migration - PaySheetPro</title>
    <link rel="stylesheet" href="../accests/css/style.css">
    <link rel="stylesheet" href="../accests/css/dashboard.css">
    <style>
        .migration-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .migration-card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .migration-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .migration-header h1 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }
        
        .status-box {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .status-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .status-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .status-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        
        .results-list {
            list-style: none;
            padding: 0;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .results-list li {
            padding: 8px 0;
            font-size: 13px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .results-list li:last-child {
            border-bottom: none;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            text-align: center;
        }
        
        .summary p {
            margin: 5px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="migration-container">
        <div class="migration-card">
            <div class="migration-header">
                <h1>🔐 Password Migration</h1>
                <p>Securing passwords in the database</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="status-box status-error">
                    ⚠️ Errors Encountered
                </div>
                <ul class="results-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <?php if (!empty($migration_results) || $already_hashed_count > 0): ?>
                <div class="status-box status-success">
                    ✓ Migration Completed Successfully
                </div>
                
                <ul class="results-list">
                    <?php foreach ($migration_results as $result): ?>
                        <li><?php echo htmlspecialchars($result); ?></li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="summary">
                    <p><strong>Migration Summary:</strong></p>
                    <p>Passwords Hashed: <strong><?php echo $updated_count; ?></strong></p>
                    <p>Already Hashed: <strong><?php echo $already_hashed_count; ?></strong></p>
                    <p>Total Users Processed: <strong><?php echo $updated_count + $already_hashed_count; ?></strong></p>
                </div>
            <?php else: ?>
                <div class="status-box status-info">
                    ℹ️ No plain text passwords found or all passwords are already hashed.
                </div>
            <?php endif; ?>
            
            <div class="action-buttons">
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                <button onclick="deleteFile()" class="btn btn-secondary">Delete This File</button>
            </div>
        </div>
    </div>
    
    <script>
        function deleteFile() {
            if (confirm('Are you sure you want to delete this migration script?\n\nThis action cannot be undone.')) {
                fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'delete_file=true'
                }).then(response => {
                    alert('Migration file has been deleted for security.');
                    window.location.href = 'dashboard.php';
                });
            }
        }
    </script>
</body>
</html>

<?php
// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $file_path = __FILE__;
    if (file_exists($file_path)) {
        unlink($file_path);
        echo "File deleted successfully";
    }
}
?>
