<?php
/**
 * Diagnostic Script - Test Password Hashing and Verification
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once('config/db.php');
require_once('config/functions.php');

$diagnostics = [];

try {
    // Test 1: Check hashPassword function
    $testPassword = 'password123';
    $hash1 = hashPassword($testPassword);
    $hash2 = hashPassword($testPassword);
    
    $diagnostics['Hash Test 1'] = [
        'Password' => $testPassword,
        'Hash 1' => $hash1,
        'Hash 2' => $hash2,
        'Status' => 'Generated successfully'
    ];
    
    // Test 2: Verify password
    $verify1 = verifyPassword($testPassword, $hash1);
    $verify2 = verifyPassword($testPassword, $hash1);
    $verifyWrong = verifyPassword('wrongpassword', $hash1);
    
    $diagnostics['Verification Test'] = [
        'Verify Correct Password (1)' => $verify1 ? 'PASS' : 'FAIL',
        'Verify Correct Password (2)' => $verify2 ? 'PASS' : 'FAIL',
        'Verify Wrong Password' => $verifyWrong ? 'FAIL (should not verify)' : 'PASS (correctly rejected)',
    ];
    
    // Test 3: Check stored users
    $users = $conn->query("SELECT user_id, name, email, phone, status, password FROM users LIMIT 5")->fetch_all(MYSQLI_ASSOC);
    $userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    
    $diagnostics['Database Users'] = [
        'Total Users' => $userCount,
        'Sample Users' => count($users),
    ];
    
    foreach ($users as $i => $user) {
        $testVerify = verifyPassword($testPassword, $user['password']);
        $diagnostics["User " . ($i + 1)] = [
            'Name' => $user['name'],
            'Email' => $user['email'],
            'Phone' => $user['phone'],
            'Status' => $user['status'],
            'Hash Length' => strlen($user['password']),
            'Hash Prefix' => substr($user['password'], 0, 10),
            'Can Verify with password123' => $testVerify ? 'YES' : 'NO',
        ];
    }
    
    // Test 4: Check admin accounts
    $admins = $conn->query("SELECT admin_id, name, email, phone, password FROM admin LIMIT 5")->fetch_all(MYSQLI_ASSOC);
    $adminCount = $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'];
    
    $diagnostics['Database Admins'] = [
        'Total Admins' => $adminCount,
        'Sample Admins' => count($admins),
    ];
    
    foreach ($admins as $i => $admin) {
        $testVerify = verifyPassword($testPassword, $admin['password']);
        $diagnostics["Admin " . ($i + 1)] = [
            'Name' => $admin['name'],
            'Email' => $admin['email'],
            'Hash Length' => strlen($admin['password']),
            'Hash Prefix' => substr($admin['password'], 0, 10),
            'Can Verify with password123' => $testVerify ? 'YES' : 'NO',
        ];
    }
    
} catch (Exception $e) {
    $diagnostics['Error'] = $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic - Gym Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: #f5f5f5;
            padding: 20px;
        }
        .diagnostic-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .diagnostic-section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .diagnostic-section h3 {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .diagnostic-row {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .diagnostic-row:last-child {
            border-bottom: none;
        }
        .diagnostic-key {
            font-weight: bold;
            color: #333;
            width: 30%;
            display: inline-block;
        }
        .diagnostic-value {
            color: #666;
            word-break: break-all;
        }
        .status-pass {
            color: #28a745;
            font-weight: bold;
        }
        .status-fail {
            color: #dc3545;
            font-weight: bold;
        }
        .hash-truncate {
            font-family: monospace;
            font-size: 11px;
            background: #f0f0f0;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="diagnostic-container">
        <h1>🔧 Gym Management - Diagnostic Report</h1>
        <p style="color: #666;">Password Hashing and Database Analysis</p>
        
        <?php foreach ($diagnostics as $section => $data): ?>
            <div class="diagnostic-section">
                <h3><?php echo htmlspecialchars($section); ?></h3>
                <?php if (is_array($data)): ?>
                    <?php foreach ($data as $key => $value): ?>
                        <div class="diagnostic-row">
                            <span class="diagnostic-key"><?php echo htmlspecialchars($key); ?>:</span>
                            <span class="diagnostic-value">
                                <?php
                                if (is_bool($value)) {
                                    echo $value ? '<span class="status-pass">YES</span>' : '<span class="status-fail">NO</span>';
                                } elseif (is_array($value)) {
                                    echo htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT));
                                } elseif (strlen($value) > 50) {
                                    echo '<span class="hash-truncate">' . htmlspecialchars(substr($value, 0, 50)) . '...</span>';
                                } else {
                                    echo htmlspecialchars($value);
                                }
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="diagnostic-row">
                        <span class="diagnostic-value"><?php echo htmlspecialchars($data); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="alert alert-info mt-4">
            <strong>Next Steps:</strong>
            <ul>
                <li>If all hashing tests show "PASS", passwords are being hashed correctly</li>
                <li>If users show "Can Verify: NO", their passwords need to be re-hashed</li>
                <li>Check user status - if any are "Inactive", try activating them</li>
                <li>After reviewing, you can go back to <a href="auth/login.php">Login</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
