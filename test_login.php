<?php
/**
 * Test Login Script - Debug login issues
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once('config/db.php');
require_once('config/functions.php');

$testResults = [];
$testEmail = $_GET['email'] ?? 'user@gym.com';
$testPassword = $_GET['password'] ?? 'password123';
$testRole = $_GET['role'] ?? 'user';

try {
    // Test 1: Validate inputs
    $testResults['Input Validation'] = [
        'Email' => $testEmail,
        'Password Length' => strlen($testPassword),
        'Role' => $testRole,
        'Email Valid' => isValidEmail($testEmail) ? 'YES' : 'NO',
    ];
    
    // Test 2: Query the user based on role
    if ($testRole === 'user') {
        $query = "SELECT * FROM users WHERE email = ? AND status = 'Active'";
        $table = 'users';
    } elseif ($testRole === 'instructor') {
        $query = "SELECT * FROM instructors WHERE email = ? AND status = 'Active'";
        $table = 'instructors';
    } else {
        $query = "SELECT * FROM admin WHERE email = ?";
        $table = 'admin';
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $testEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    $testResults['Database Query'] = [
        'Table' => $table,
        'Query Status' => 'Executed',
        'Records Found' => $result->num_rows,
        'User Found' => $user ? 'YES' : 'NO',
    ];
    
    if ($user) {
        // Test 3: Check password hash
        $testResults['User Record'] = [
            'ID' => $user[$testRole === 'user' ? 'user_id' : ($testRole === 'instructor' ? 'instructor_id' : 'admin_id')] ?? 'N/A',
            'Name' => $user['name'] ?? 'N/A',
            'Email' => $user['email'] ?? 'N/A',
            'Status' => $user['status'] ?? 'N/A',
            'Password Hash Length' => strlen($user['password'] ?? ''),
            'Hash Prefix' => substr($user['password'] ?? '', 0, 15),
        ];
        
        // Test 4: Verify password
        $passwordCorrect = verifyPassword($testPassword, $user['password']);
        $testResults['Password Verification'] = [
            'Test Password' => $testPassword,
            'Password Matches' => $passwordCorrect ? 'YES ✓' : 'NO ✗',
            'Verification Status' => $passwordCorrect ? 'PASS' : 'FAIL',
        ];
        
        if ($passwordCorrect) {
            $testResults['Login Result'] = [
                'Status' => 'SUCCESS ✓',
                'Message' => 'User can login successfully',
                'Session Variables Ready' => 'YES',
            ];
        } else {
            $testResults['Login Result'] = [
                'Status' => 'FAILED ✗',
                'Message' => 'Password does not match',
                'Possible Causes' => [
                    '1. Password not hashed correctly during signup',
                    '2. Password truncated in database',
                    '3. Different password being used',
                    '4. Encoding issue with special characters',
                ],
            ];
        }
    } else {
        $testResults['Login Result'] = [
            'Status' => 'FAILED ✗',
            'Message' => 'User account not found',
            'Possible Causes' => [
                '1. User not registered',
                '2. Wrong email address',
                '3. User account inactive',
                '4. Email case sensitivity issue',
            ],
        ];
    }
    
} catch (Exception $e) {
    $testResults['Error'] = [
        'Message' => $e->getMessage(),
        'Status' => 'FAILED',
    ];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Test - Gym Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: #f5f5f5;
            padding: 20px;
        }
        .test-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            margin: 20px auto;
            max-width: 800px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .test-form .form-group {
            margin-bottom: 15px;
        }
        .test-section {
            margin-bottom: 25px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .test-section h3 {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .test-row {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        .test-row:last-child {
            border-bottom: none;
        }
        .test-key {
            font-weight: bold;
            color: #333;
            width: 35%;
        }
        .test-value {
            color: #666;
            word-break: break-all;
            width: 65%;
            text-align: right;
        }
        .status-success {
            color: #28a745;
            font-weight: bold;
        }
        .status-fail {
            color: #dc3545;
            font-weight: bold;
        }
        .hash-mono {
            font-family: monospace;
            font-size: 11px;
            background: #f0f0f0;
            padding: 2px 5px;
            border-radius: 3px;
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
        }
        .quick-links {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .quick-links a {
            margin-right: 15px;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🔍 Login Test & Diagnosis</h1>
        
        <div class="test-form">
            <h4>Enter Test Credentials</h4>
            <form method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="email" class="mr-2">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($testEmail); ?>" placeholder="user@gym.com">
                </div>
                <div class="form-group mr-3">
                    <label for="password" class="mr-2">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" value="<?php echo htmlspecialchars($testPassword); ?>" placeholder="password123">
                </div>
                <div class="form-group mr-3">
                    <label for="role" class="mr-2">Role:</label>
                    <select class="form-control" id="role" name="role">
                        <option value="user" <?php echo $testRole === 'user' ? 'selected' : ''; ?>>User</option>
                        <option value="instructor" <?php echo $testRole === 'instructor' ? 'selected' : ''; ?>>Instructor</option>
                        <option value="admin" <?php echo $testRole === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Test</button>
            </form>
        </div>
        
        <div class="quick-links">
            <strong>Quick Tests:</strong><br>
            <a href="?email=user@gym.com&password=password123&role=user" class="btn btn-sm btn-info">Test User</a>
            <a href="?email=instructor@gym.com&password=password123&role=instructor" class="btn btn-sm btn-info">Test Instructor</a>
            <a href="?email=admin@gym.com&password=password123&role=admin" class="btn btn-sm btn-info">Test Admin</a>
        </div>
        
        <?php foreach ($testResults as $section => $data): ?>
            <div class="test-section">
                <h3><?php echo htmlspecialchars($section); ?></h3>
                <?php if (is_array($data)): ?>
                    <?php foreach ($data as $key => $value): ?>
                        <div class="test-row">
                            <span class="test-key"><?php echo htmlspecialchars($key); ?>:</span>
                            <span class="test-value">
                                <?php
                                if (is_bool($value)) {
                                    echo $value ? '<span class="status-success">YES</span>' : '<span class="status-fail">NO</span>';
                                } elseif (is_array($value)) {
                                    foreach ($value as $item) {
                                        echo htmlspecialchars($item) . '<br>';
                                    }
                                } elseif (strlen($value) > 40) {
                                    echo '<span class="hash-mono">' . htmlspecialchars(substr($value, 0, 40)) . '...</span>';
                                } else {
                                    echo htmlspecialchars($value);
                                }
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="alert alert-info mt-4">
            <strong>Troubleshooting:</strong>
            <ul>
                <li>If "Password Matches" shows "NO", run <a href="fix_passwords.php">Password Fix Script</a></li>
                <li>If "User Found" shows "NO", check the email address</li>
                <li>After fixing, try login at <a href="auth/login.php">Login Page</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
