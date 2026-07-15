<?php
session_start();
require_once '../config/db.php';
require_once '../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $role = sanitize($_POST['role'] ?? 'user');
    
    $table = 'users';
    if ($role === 'admin') $table = 'admin';
    if ($role === 'instructor') $table = 'instructors';
    
    // Check if email exists
    $query = "SELECT * FROM $table WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User exists, for a real app we'd send an email with a token. 
        // For this project, we'll store the email and role in session and redirect to reset page.
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_role'] = $role;
        redirect('reset_password.php', 'Email verified. You can now reset your password.', 'success');
    } else {
        $_SESSION['message'] = 'Email address not found in our records.';
        $_SESSION['message_type'] = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Rock Ascent - Forgot Password</title>
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
        .role-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }
        .role-btn {
            flex: 1;
            padding: 10px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            background: rgba(15, 23, 42, 0.85);
            border-radius: 14px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: #9ca3af;
            text-align: center;
            transition: all 0.2s;
        }
        .role-btn.active {
            background: #f59e0b;
            color: white;
            border-color: #f59e0b;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Reset Password</h1>
        <p>Enter your email to reset your password</p>

        <?php
        if (isset($_SESSION['message'])) {
            $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
            $alertClass = ($type === 'success') ? 'alert-success' : 'alert-danger';
            echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                    {$_SESSION['message']}
                    <button type='button' class='close' data-dismiss='alert'>
                        <span>&times;</span>
                    </button>
                  </div>";
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <form method="POST" action="">
            <div class="role-selector">
                <input type="radio" name="role" value="user" id="role_user" checked style="display: none;">
                <label for="role_user" class="role-btn active" onclick="updateRole(this, 'user')">Member</label>

                <input type="radio" name="role" value="instructor" id="role_instructor" style="display: none;">
                <label for="role_instructor" class="role-btn" onclick="updateRole(this, 'instructor')">Instructor</label>

                <input type="radio" name="role" value="admin" id="role_admin" style="display: none;">
                <label for="role_admin" class="role-btn" onclick="updateRole(this, 'admin')">Admin</label>
            </div>

            <div class="form-group">
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email address" required>
            </div>

            <button type="submit" class="btn-login">Verify Email</button>
        </form>

        <div class="signup-link">
            Remember your password? <a href="login.php">Log In</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateRole(element, role) {
            document.querySelectorAll('.role-btn').forEach(btn => btn.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('role_' + role).checked = true;
        }
    </script>
</body>
</html>
