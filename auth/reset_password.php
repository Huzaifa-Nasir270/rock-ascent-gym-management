<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';

// If no active reset session, redirect to forgot password
if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_role'])) {
    redirect('forgot_password.php', 'Please verify your email first.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || strlen($password) < 6) {
        $_SESSION['message'] = 'Password must be at least 6 characters.';
        $_SESSION['message_type'] = 'danger';
    } elseif ($password !== $confirm_password) {
        $_SESSION['message'] = 'Passwords do not match.';
        $_SESSION['message_type'] = 'danger';
    } else {
        $email = $_SESSION['reset_email'];
        $role = $_SESSION['reset_role'];
        $hashedPassword = hashPassword($password);
        
        $table = 'users';
        if ($role === 'admin') $table = 'admin';
        if ($role === 'instructor') $table = 'instructors';
        
        $query = "UPDATE $table SET password = ? WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $hashedPassword, $email);
        
        if ($stmt->execute()) {
            // Success, clear reset session and redirect to login
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_role']);
            redirect('login.php', 'Password successfully reset! Please log in with your new password.', 'success');
        } else {
            $_SESSION['message'] = 'Failed to update password. Please try again.';
            $_SESSION['message_type'] = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Rock Ascent - Create New Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
            color: #e5e7eb;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -2;
            background-image: url('../assets/images/back.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            animation: bgZoom 20s ease-in-out infinite alternate;
        }
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            z-index: -1;
            background:
                radial-gradient(ellipse at 20% 30%, rgba(245,158,11,0.08) 0%, transparent 45%),
                radial-gradient(ellipse at 80% 70%, rgba(236,72,153,0.07) 0%, transparent 45%),
                linear-gradient(160deg, rgba(2,6,23,0.88) 0%, rgba(15,23,42,0.84) 60%, rgba(2,6,23,0.93) 100%);
        }
        @keyframes bgZoom {
            0%   { transform: scale(1); }
            100% { transform: scale(1.06); }
        }
        .login-container {
            background: rgba(8, 13, 30, 0.78);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.10);
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.75),
                        inset 0 1px 0 rgba(255,255,255,0.06);
            padding: 40px 36px;
            max-width: 450px;
            width: 100%;
            margin: auto;
            animation: fadeInUp 0.7s cubic-bezier(0.4,0,0.2,1);
        }
        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(28px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .login-container h1 {
            text-align: center;
            color: #f8fafc;
            margin-bottom: 8px;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 0.08em;
        }
        .login-container p {
            text-align: center;
            color: #9ca3af;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group label {
            font-weight: 700;
            color: #f8fafc;
            margin-bottom: 10px;
        }
        .form-control {
            border: 1px solid rgba(148, 163, 184, 0.35);
            border-radius: 14px;
            padding: 14px 16px;
            background: rgba(15, 23, 42, 0.85);
            color: #f8fafc;
            font-size: 14px;
        }
        .form-control::placeholder {
            color: #9ca3af;
        }
        .form-control:focus {
            border-color: #f59e0b;
            box-shadow: 0 0 0 0.2rem rgba(245, 158, 11, 0.18);
            background: rgba(15, 23, 42, 0.98);
            color: #f8fafc;
        }
        .btn-login {
            background: linear-gradient(135deg, #f59e0b 0%, #ec4899 100%);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 14px;
            font-weight: 700;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 16px 32px rgba(236, 72, 153, 0.2);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(236, 72, 153, 0.3);
            color: white;
            text-decoration: none;
        }
        .signup-link {
            text-align: center;
            margin-top: 22px;
            color: #9ca3af;
            font-size: 14px;
        }
        .signup-link a {
            color: #f59e0b;
            text-decoration: none;
            font-weight: 700;
        }
        .signup-link a:hover {
            text-decoration: underline;
        }
        .alert {
            margin-bottom: 20px;
            border-radius: 14px;
            padding: 18px 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Create New Password</h1>
        <p>Set a new password for <?php echo htmlspecialchars($_SESSION['reset_email']); ?></p>

        <?php
        if (isset($_SESSION['message'])) {
            $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
            $alertClass = ($type === 'success') ? 'alert-success' : 'alert-danger';
            echo "<div class='alert $alertClass'>{$_SESSION['message']}</div>";
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <form method="POST" action="">
            <div class="form-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="New Password (min 6 chars)" required minlength="6">
            </div>
            
            <div class="form-group">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm New Password" required minlength="6">
            </div>

            <button type="submit" class="btn-login">Save New Password</button>
        </form>

        <div class="signup-link">
            <a href="login.php">Cancel</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
