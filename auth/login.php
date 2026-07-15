<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Rock Ascent - Login</title>
    <?php 
    $depth = 1;
    $root = '../';
    include('../includes/head.php'); 
    ?>
    <style>
        body {
            display: grid;
            place-items: center;
            padding: 20px;
            overflow-y: auto !important;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            background: rgba(8, 13, 30, 0.6);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border-radius: 40px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 50px 100px rgba(0, 0, 0, 0.8);
            padding: 50px 40px;
            margin: 20px auto;
            animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .logo-text {
            font-size: 36px;
            font-weight: 900;
            text-align: center;
            background: linear-gradient(135deg, #f59e0b 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
            letter-spacing: -1.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .sub-text {
            text-align: center;
            color: #94a3b8;
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 40px;
        }
        .role-switcher {
            display: flex;
            background: rgba(15, 23, 42, 0.6);
            padding: 8px;
            border-radius: 20px;
            margin-bottom: 35px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .role-btn {
            flex: 1;
            padding: 14px;
            text-align: center;
            color: #94a3b8;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.4s;
            border-radius: 16px;
            user-select: none;
        }
        .role-btn.active {
            background: linear-gradient(135deg, #f59e0b 0%, #ec4899 100%);
            color: white;
            box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);
        }
        .form-control {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 18px !important;
            padding: 18px 25px !important;
            color: white !important;
            font-size: 15px !important;
            margin-bottom: 25px;
            height: auto !important;
            transition: all 0.3s !important;
        }
        .form-control::placeholder {
            color: #64748b !important;
        }
        .form-control:focus {
            border-color: rgba(245, 158, 11, 0.5) !important;
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1) !important;
            background: rgba(15, 23, 42, 0.8) !important;
        }
        .password-group {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 25px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            cursor: pointer;
            font-size: 18px;
            transition: color 0.3s;
        }
        .toggle-password:hover {
            color: #f59e0b;
        }
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            font-size: 14px;
            font-weight: 500;
        }
        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #94a3b8;
            cursor: pointer;
        }
        .checkbox-container a {
            color: #f59e0b;
            font-weight: 700;
        }
        .forgot-link {
            color: #f59e0b;
            font-weight: 700;
            text-decoration: none;
        }
        .btn-login {
            width: 100%;
            padding: 18px;
            background: #374151 !important;
            color: white !important;
            border-radius: 20px !important;
            font-weight: 800 !important;
            font-size: 18px !important;
            text-transform: uppercase !important;
            letter-spacing: 2px !important;
            border: none !important;
            transition: all 0.4s !important;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
        }
        .btn-login:hover:not(:disabled) {
            transform: translateY(-5px);
            background: #4b5563 !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4) !important;
        }
        .btn-login:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .footer-text {
            text-align: center;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
        }
        .footer-text a {
            color: #f59e0b;
            font-weight: 800;
            text-decoration: none;
        }
        .alert {
            border-radius: 20px;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-text">
            <i class="fas fa-dumbbell" style="color: #f59e0b;"></i> Project Rock Ascent
        </div>
        <p class="sub-text">Log in to access your account</p>

        <?php
        session_start();
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

        <form method="POST" action="login_process.php" id="loginForm">
            <div class="role-switcher">
                <input type="radio" name="role" value="user" id="role_user" checked style="display: none;">
                <div class="role-btn active" onclick="updateRole(this, 'user')">Member</div>

                <input type="radio" name="role" value="instructor" id="role_instructor" style="display: none;">
                <div class="role-btn" onclick="updateRole(this, 'instructor')">Instructor</div>

                <input type="radio" name="role" value="admin" id="role_admin" style="display: none;">
                <div class="role-btn" onclick="updateRole(this, 'admin')">Admin</div>
            </div>

            <div class="form-group">
                <input type="email" class="form-control" id="email" name="email" placeholder="Username or Email" required>
            </div>

            <div class="form-group password-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <i class="fas fa-eye-slash toggle-password" onclick="togglePassword()"></i>
            </div>

            <div class="form-options">
                <label class="checkbox-container">
                    <input type="checkbox" name="remember" id="termsCheck">
                    <span>I agree to <a href="#" data-toggle="modal" data-target="#termsModal">Terms</a> & Remember me</span>
                </label>
                <a href="forgot_password.php" class="forgot-link">Forgot?</a>
            </div>

            <button type="submit" class="btn btn-login" id="loginBtn">LOG IN</button>
        </form>

        <div class="footer-text">
            Don't have an account? <a href="signup.php">Create one here</a>
        </div>
    </div>

    <!-- Terms Modal (kept from before but styled) -->
    <div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="background: #0f172a; border-radius: 30px; border: 1px solid rgba(255,255,255,0.1);">
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title font-weight-bold" style="color: #f59e0b;">Terms and Conditions</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 pt-0" style="color: #94a3b8; font-size: 14px; line-height: 1.6;">
                    <p>Welcome to Project Rock Ascent. By using our gym management system, you agree to the following terms:</p>
                    <ul>
                        <li>Your account information will be kept confidential.</li>
                        <li>You will not share your password with third parties.</li>
                        <li>The system is for authorized users only (Members, Instructors, Admins).</li>
                        <li>We use cookies to remember your session if "Remember me" is checked.</li>
                    </ul>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-primary btn-block" data-dismiss="modal" style="border-radius: 15px;">I Understand</button>
                </div>
            </div>
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

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.toggle-password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>
