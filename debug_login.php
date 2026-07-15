<?php
/**
 * Complete Login Debugger
 * Shows all details about authentication
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once('config/db.php');
require_once('config/functions.php');

$debug = [];

try {
    // Check 1: Database connection
    $debug['Database'] = [
        'Connected' => 'YES',
        'Host' => DB_HOST,
        'Database' => DB_NAME,
    ];
    
    // Check 2: Test password hashing
    $testPass = 'password123';
    $hashedPass = hashPassword($testPass);
    $verifies = verifyPassword($testPass, $hashedPass);
    
    $debug['Password Hashing'] = [
        'Test Password' => $testPass,
        'Hashing Works' => $verifies ? 'YES ✓' : 'NO ✗',
        'Hash Length' => strlen($hashedPass),
        'Hash Type' => substr($hashedPass, 0, 4) === '$2y$' ? 'bcrypt (Correct)' : 'Unknown',
    ];
    
    // Check 3: Count users in database
    $userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    $adminCount = $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'];
    $instructorCount = $conn->query("SELECT COUNT(*) as count FROM instructors")->fetch_assoc()['count'];
    
    $debug['Database Records'] = [
        'Total Users' => $userCount,
        'Total Admins' => $adminCount,
        'Total Instructors' => $instructorCount,
    ];
    
    // Check 4: Check specific demo accounts
    $demoAccounts = [
        ['email' => 'user@gym.com', 'table' => 'users', 'role' => 'User'],
        ['email' => 'admin@gym.com', 'table' => 'admin', 'role' => 'Admin'],
        ['email' => 'instructor@gym.com', 'table' => 'instructors', 'role' => 'Instructor'],
    ];
    
    foreach ($demoAccounts as $account) {
        $table = $account['table'];
        $role = $account['role'];
        $email = $account['email'];
        
        if ($table === 'users' || $table === 'instructors') {
            $result = $conn->query("SELECT user_id, name, email, password, status FROM $table WHERE email = '$email' LIMIT 1");
        } else {
            $result = $conn->query("SELECT admin_id, name, email, password FROM $table WHERE email = '$email' LIMIT 1");
        }
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $passwordHash = $row['password'];
            $canVerify = verifyPassword($testPass, $passwordHash);
            $hashPrefix = substr($passwordHash, 0, 20);
            
            $debug[$role . ' Account (' . $email . ')'] = [
                'Email' => $email,
                'Found' => 'YES ✓',
                'Name' => $row['name'],
                'Status' => $row['status'] ?? 'N/A',
                'Password Hash Prefix' => $hashPrefix . '...',
                'Hash is bcrypt' => substr($passwordHash, 0, 4) === '$2y$' ? 'YES ✓' : 'NO ✗',
                'Can Verify password123' => $canVerify ? 'YES ✓' : 'NO ✗ (FIX NEEDED)',
                'Hash Length' => strlen($passwordHash),
            ];
        } else {
            $debug[$role . ' Account (' . $email . ')'] = [
                'Found' => 'NO ✗ (ACCOUNT NOT FOUND)',
            ];
        }
    }
    
    // Check 5: Test actual login flow
    $testEmail = 'user@gym.com';
    $testPassword = 'password123';
    
    $query = "SELECT * FROM users WHERE email = ? AND status = 'Active'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $testEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        $passwordMatch = verifyPassword($testPassword, $user['password']);
        $debug['Login Simulation (user@gym.com / password123)'] = [
            'User Found' => 'YES ✓',
            'Status' => $user['status'],
            'Password Matches' => $passwordMatch ? 'YES ✓' : 'NO ✗',
            'Login Would' => $passwordMatch ? 'SUCCEED ✓' : 'FAIL ✗',
        ];
    } else {
        $debug['Login Simulation (user@gym.com / password123)'] = [
            'User Found' => 'NO ✗',
            'Status' => 'User not found or inactive',
        ];
    }
    
    // Check 6: Session test
    session_start();
    $_SESSION['test'] = 'working';
    $debug['Session'] = [
        'Session Started' => 'YES ✓',
        'Can Write Variables' => isset($_SESSION['test']) ? 'YES ✓' : 'NO ✗',
    ];
    
} catch (Exception $e) {
    $debug['Error'] = [
        'Message' => $e->getMessage(),
        'Code' => $e->getCode(),
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Debugger - Gym Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: #f5f5f5;
            padding: 20px;
        }
        .debug-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin: 20px auto;
            max-width: 900px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }
        .debug-section {
            margin-bottom: 25px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: #f9f9f9;
            border-left: 4px solid #667eea;
        }
        .debug-section h3 {
            color: #333;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 16px;
        }
        .debug-row {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .debug-row:last-child {
            border-bottom: none;
        }
        .debug-key {
            font-weight: 600;
            color: #333;
            width: 40%;
        }
        .debug-value {
            color: #666;
            width: 60%;
            text-align: right;
        }
        .status-pass {
            color: #28a745;
            font-weight: bold;
        }
        .status-fail {
            color: #dc3545;
            font-weight: bold;
        }
        .status-info {
            color: #17a2b8;
        }
        .mono {
            font-family: monospace;
            font-size: 12px;
            background: #f0f0f0;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .action-buttons {
            margin-top: 30px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="debug-container">
        <h1>🔍 Login Debugger - Complete Analysis</h1>
        
        <?php foreach ($debug as $section => $data): ?>
            <div class="debug-section">
                <h3><?php echo htmlspecialchars($section); ?></h3>
                <?php foreach ($data as $key => $value): ?>
                    <div class="debug-row">
                        <span class="debug-key"><?php echo htmlspecialchars($key); ?>:</span>
                        <span class="debug-value">
                            <?php
                            if (strpos($value, '✓') !== false) {
                                echo '<span class="status-pass">' . htmlspecialchars($value) . '</span>';
                            } elseif (strpos($value, '✗') !== false) {
                                echo '<span class="status-fail">' . htmlspecialchars($value) . '</span>';
                            } else {
                                echo '<span class="mono">' . htmlspecialchars($value) . '</span>';
                            }
                            ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        
        <?php
        // Determine if there's an issue
        $hasIssue = false;
        foreach ($debug as $section => $data) {
            foreach ($data as $key => $value) {
                if (strpos($value, '✗') !== false) {
                    $hasIssue = true;
                    break;
                }
            }
        }
        ?>
        
        <?php if ($hasIssue): ?>
            <div class="alert alert-warning">
                <strong>⚠️ Issues Found!</strong>
                <p>Based on the debug results above, there are issues preventing login. Look for any rows marked with ✗</p>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                <strong>✓ Everything Looks Good!</strong>
                <p>All systems are configured correctly. If you still can't login, try:</p>
                <ul>
                    <li>Clear browser cache and cookies</li>
                    <li>Try a different browser</li>
                    <li>Try in incognito/private mode</li>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="auth/login.php" class="btn btn-primary">Go to Login</a>
            <a href="fix_passwords.php" class="btn btn-warning">Reset Passwords</a>
            <a href="test_login.php?email=user@gym.com&password=password123&role=user" class="btn btn-info">Test User Login</a>
        </div>
    </div>
</body>
</html>
