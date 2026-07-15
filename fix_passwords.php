<?php
/**
 * Password Fix Script - Rehash all demo and user passwords
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once('config/db.php');
require_once('config/functions.php');

$message = '';
$type = 'success';
$updates = [
    'admin' => 0,
    'instructors' => 0,
    'users' => 0
];

try {
    $testPassword = 'password123';
    $hashedPassword = hashPassword($testPassword);
    
    // Fix Admin accounts
    $stmt = $conn->prepare("UPDATE admin SET password = ?");
    $stmt->bind_param("s", $hashedPassword);
    $stmt->execute();
    $updates['admin'] = $stmt->affected_rows;
    
    // Fix Instructor accounts
    $stmt = $conn->prepare("UPDATE instructors SET password = ?");
    $stmt->bind_param("s", $hashedPassword);
    $stmt->execute();
    $updates['instructors'] = $stmt->affected_rows;
    
    // Fix all User accounts
    $stmt = $conn->prepare("UPDATE users SET password = ?");
    $stmt->bind_param("s", $hashedPassword);
    $stmt->execute();
    $updates['users'] = $stmt->affected_rows;
    
    $message = "✓ Passwords have been reset successfully!\n\n";
    $message .= "Updated Records:\n";
    $message .= "- Admin accounts: " . $updates['admin'] . "\n";
    $message .= "- Instructor accounts: " . $updates['instructors'] . "\n";
    $message .= "- User accounts: " . $updates['users'] . "\n\n";
    $message .= "All accounts now use password: password123\n";
    $message .= "Passwords are hashed with bcrypt\n";
    
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
    <title>Password Fix - Gym Management</title>
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
        .fix-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .fix-container h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: bold;
        }
        .alert {
            white-space: pre-wrap;
            font-family: monospace;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .credentials {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        .credentials h3 {
            color: #333;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .credential-item {
            margin-bottom: 10px;
            padding: 10px;
            background: white;
            border-radius: 3px;
            border-left: 3px solid #667eea;
        }
        .credential-item strong {
            color: #667eea;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            text-align: center;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="fix-container">
        <h1>🔧 Password Reset</h1>
        
        <?php
        $alertClass = ($type === 'success') ? 'alert-success' : 'alert-danger';
        echo "<div class='alert $alertClass'>" . htmlspecialchars($message) . "</div>";
        ?>
        
        <?php if ($type === 'success'): ?>
        <div class="credentials">
            <h3>✓ Login with Updated Credentials:</h3>
            
            <div class="credential-item">
                <strong>👤 Member (User)</strong><br>
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
        
        <div class="btn-back">
            <a href="auth/login.php" class="btn btn-primary btn-lg">Go to Login →</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
