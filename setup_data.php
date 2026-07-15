<?php
/**
 * Setup Script - Initialize Demo Data
 * Run this once to set up admin, instructor, and user accounts
 */

ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once('config/db.php');
require_once('config/functions.php');

$message = '';
$type = 'success';

try {
    // Hash password for all demo accounts
    $hashedPassword = hashPassword('password123');
    
    // Check if admin already exists
    $adminCheck = $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'];
    
    if ($adminCheck == 0) {
        // Insert admin
        $query = "INSERT INTO admin (name, email, password, phone) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssss", $name, $email, $password, $phone);
        
        $name = 'Admin User';
        $email = 'admin@gym.com';
        $password = $hashedPassword;
        $phone = '03001234567'; // 11 digits
        
        $stmt->execute();
        $message .= "✓ Admin account created successfully!\n";
    } else {
        $message .= "✓ Admin account already exists\n";
    }
    
    // Check if instructor already exists
    $instructorCheck = $conn->query("SELECT COUNT(*) as count FROM instructors")->fetch_assoc()['count'];
    
    if ($instructorCheck == 0) {
        // Insert instructor
        $query = "INSERT INTO instructors (name, email, password, phone, specialization, certification, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssss", $name, $email, $password, $phone, $spec, $cert, $hireDate, $status);
        
        $name = 'John Doe';
        $email = 'instructor@gym.com';
        $password = $hashedPassword;
        $phone = '03009876543'; // 11 digits
        $spec = 'Strength Training';
        $cert = 'NASM Certified';
        $hireDate = '2023-01-15';
        $status = 'Active';
        
        $stmt->execute();
        $message .= "✓ Instructor account created successfully!\n";
    } else {
        $message .= "✓ Instructor account already exists\n";
    }
    
    // Check if user already exists
    $userCheck = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
    
    if ($userCheck == 0) {
        // Insert user
        $query = "INSERT INTO users (name, email, password, phone, gender, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssss", $name, $email, $password, $phone, $gender, $status);
        
        $name = 'Sample User';
        $email = 'user@gym.com';
        $password = $hashedPassword;
        $phone = '03005432109'; // 11 digits
        $gender = 'Male';
        $status = 'Active';
        
        $stmt->execute();
        $message .= "✓ User account created successfully!\n";
    } else {
        $message .= "✓ User account already exists\n";
    }
    
    $message .= "\n✓ Database setup completed successfully!\n";
    
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
    <title>Database Setup - Gym Management</title>
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
        .setup-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }
        .setup-container h1 {
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
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>🏋️ Database Setup</h1>
        
        <?php
        $alertClass = ($type === 'success') ? 'alert-success' : 'alert-danger';
        echo "<div class='alert $alertClass'>" . htmlspecialchars($message) . "</div>";
        ?>
        
        <?php if ($type === 'success'): ?>
        <div class="credentials">
            <h3>📝 Demo Login Credentials:</h3>
            
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
            <a href="auth/login.php" class="btn btn-primary">Go to Login</a>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
