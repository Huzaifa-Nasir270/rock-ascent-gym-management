<?php
/**
 * Clean Reset - Delete and Recreate Demo Accounts
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once('config/db.php');
require_once('config/functions.php');

$message = '';
$type = 'success';
$results = [];

try {
    // Step 1: Delete existing demo accounts
    $conn->query("DELETE FROM users WHERE email IN ('user@gym.com', 'demo@gym.com')");
    $results['Delete Users'] = 'Old demo accounts removed';
    
    $conn->query("DELETE FROM instructors WHERE email IN ('instructor@gym.com', 'demo_instructor@gym.com')");
    $results['Delete Instructors'] = 'Old demo accounts removed';
    
    $conn->query("DELETE FROM admin WHERE email IN ('admin@gym.com', 'demo_admin@gym.com')");
    $results['Delete Admins'] = 'Old demo accounts removed';
    
    // Step 2: Hash the password
    $password = 'password123';
    $hashedPassword = hashPassword($password);
    
    $results['Password Hashing'] = 'Password hashed successfully';
    
    // Step 3: Create new User account
    $userStmt = $conn->prepare("INSERT INTO users (name, email, password, phone, gender, status) VALUES (?, ?, ?, ?, ?, 'Active')");
    if (!$userStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $userName = 'Sample User';
    $userEmail = 'user@gym.com';
    $userPhone = '03005432109';
    $userGender = 'Male';
    
    $userStmt->bind_param("sssss", $userName, $userEmail, $hashedPassword, $userPhone, $userGender);
    if (!$userStmt->execute()) {
        throw new Exception("User insert failed: " . $userStmt->error);
    }
    $results['Create User'] = '✓ user@gym.com created';
    
    // Step 4: Create new Instructor account
    $instrStmt = $conn->prepare("INSERT INTO instructors (name, email, password, phone, specialization, certification, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')");
    if (!$instrStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $instrName = 'John Doe';
    $instrEmail = 'instructor@gym.com';
    $instrPhone = '03009876543';
    $instrSpec = 'Strength Training';
    $instrCert = 'NASM Certified';
    $instrHireDate = '2023-01-15';
    
    $instrStmt->bind_param("sssssss", $instrName, $instrEmail, $hashedPassword, $instrPhone, $instrSpec, $instrCert, $instrHireDate);
    if (!$instrStmt->execute()) {
        throw new Exception("Instructor insert failed: " . $instrStmt->error);
    }
    $results['Create Instructor'] = '✓ instructor@gym.com created';
    
    // Step 5: Create new Admin account
    $adminStmt = $conn->prepare("INSERT INTO admin (name, email, password, phone) VALUES (?, ?, ?, ?)");
    if (!$adminStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $adminName = 'Admin User';
    $adminEmail = 'admin@gym.com';
    $adminPhone = '03001234567';
    
    $adminStmt->bind_param("ssss", $adminName, $adminEmail, $hashedPassword, $adminPhone);
    if (!$adminStmt->execute()) {
        throw new Exception("Admin insert failed: " . $adminStmt->error);
    }
    $results['Create Admin'] = '✓ admin@gym.com created';
    
    // Step 6: Verify accounts were created
    $userCheck = $conn->query("SELECT COUNT(*) as count FROM users WHERE email = 'user@gym.com'")->fetch_assoc()['count'];
    $instrCheck = $conn->query("SELECT COUNT(*) as count FROM instructors WHERE email = 'instructor@gym.com'")->fetch_assoc()['count'];
    $adminCheck = $conn->query("SELECT COUNT(*) as count FROM admin WHERE email = 'admin@gym.com'")->fetch_assoc()['count'];
    
    $results['Verification'] = [
        'Users Created' => $userCheck > 0 ? '✓ YES' : '✗ NO',
        'Instructors Created' => $instrCheck > 0 ? '✓ YES' : '✗ NO',
        'Admins Created' => $adminCheck > 0 ? '✓ YES' : '✗ NO',
    ];
    
    $message = "✓ SUCCESS! All demo accounts have been recreated with proper password hashing.\n\n";
    $message .= "You can now login with:\n";
    $message .= "- user@gym.com / password123\n";
    $message .= "- instructor@gym.com / password123\n";
    $message .= "- admin@gym.com / password123\n";
    
} catch (Exception $e) {
    $message = "✗ Error: " . $e->getMessage();
    $type = 'danger';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clean Reset - Gym Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .reset-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 700px;
            width: 100%;
        }
        .reset-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 32px;
            font-weight: bold;
        }
        .message-box {
            white-space: pre-wrap;
            font-family: monospace;
            margin-bottom: 25px;
            line-height: 1.8;
            padding: 15px;
            border-radius: 5px;
        }
        .results-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .result-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .result-item:last-child {
            border-bottom: none;
        }
        .result-label {
            font-weight: bold;
            color: #333;
        }
        .result-value {
            color: #666;
            margin-left: 10px;
        }
        .credentials {
            background: #e7f3ff;
            border: 2px solid #667eea;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .credentials h3 {
            color: #667eea;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .credential-item {
            background: white;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 3px;
            border-left: 4px solid #667eea;
        }
        .credential-item code {
            background: #f0f0f0;
            padding: 2px 5px;
            border-radius: 3px;
        }
        .btn-group-custom {
            display: flex;
            gap: 10px;
            margin-top: 25px;
            flex-wrap: wrap;
        }
        .btn-lg-custom {
            flex: 1;
            min-width: 120px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h1>🔄 Clean Reset</h1>
        
        <?php
        $alertClass = ($type === 'success') ? 'alert-success' : 'alert-danger';
        echo "<div class='alert $alertClass'>";
        echo "<pre style='margin: 0;'>" . htmlspecialchars($message) . "</pre>";
        echo "</div>";
        ?>
        
        <?php if (!empty($results)): ?>
        <div class="results-box">
            <strong>Detailed Results:</strong>
            <?php foreach ($results as $key => $value): ?>
                <div class="result-item">
                    <span class="result-label"><?php echo htmlspecialchars($key); ?>:</span>
                    <span class="result-value">
                        <?php
                        if (is_array($value)) {
                            foreach ($value as $k => $v) {
                                echo htmlspecialchars($k) . ' - ' . htmlspecialchars($v) . '<br>';
                            }
                        } else {
                            echo htmlspecialchars($value);
                        }
                        ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($type === 'success'): ?>
        <div class="credentials">
            <h3>✓ Demo Login Credentials:</h3>
            
            <div class="credential-item">
                <strong>👤 Member</strong><br>
                Email: <code>user@gym.com</code><br>
                Password: <code>password123</code>
            </div>
            
            <div class="credential-item">
                <strong>👨‍🏫 Instructor</strong><br>
                Email: <code>instructor@gym.com</code><br>
                Password: <code>password123</code>
            </div>
            
            <div class="credential-item">
                <strong>👨‍💼 Admin</strong><br>
                Email: <code>admin@gym.com</code><br>
                Password: <code>password123</code>
            </div>
        </div>
        
        <div class="btn-group-custom">
            <a href="auth/login.php" class="btn btn-primary btn-lg-custom">Go to Login</a>
            <a href="debug_login.php" class="btn btn-info btn-lg-custom">Run Debug</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
