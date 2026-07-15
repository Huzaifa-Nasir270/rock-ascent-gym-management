    <title>Project Rock Ascent - Sign Up</title>
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
        .signup-container {
            max-width: 500px;
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
            font-size: 32px;
            font-weight: 900;
            text-align: center;
            background: linear-gradient(135deg, #f59e0b 0%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
            letter-spacing: -1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .sub-text {
            text-align: center;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 35px;
        }
        .form-group label {
            color: #e2e8f0;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-left: 5px;
            margin-bottom: 10px;
            display: block;
        }
        .form-control {
            background: rgba(15, 23, 42, 0.6) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 18px !important;
            padding: 15px 22px !important;
            color: white !important;
            font-size: 15px !important;
            margin-bottom: 20px;
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
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 18px;
        }
        .btn-signup {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #f59e0b 0%, #ec4899 100%) !important;
            color: white !important;
            border-radius: 20px !important;
            font-weight: 800 !important;
            font-size: 18px !important;
            text-transform: uppercase !important;
            letter-spacing: 2px !important;
            border: none !important;
            transition: all 0.4s !important;
            margin-top: 10px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(236, 72, 153, 0.3) !important;
        }
        .btn-signup:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 45px rgba(236, 72, 153, 0.4) !important;
            filter: brightness(1.1);
        }
        .login-link {
            text-align: center;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 500;
        }
        .login-link a {
            color: #f59e0b;
            font-weight: 800;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="logo-text">
            <i class="fas fa-dumbbell" style="color: #f59e0b;"></i> Project Rock Ascent
        </div>
        <p class="sub-text">Create your account and start managing every gym detail</p>

        <?php
        session_start();
        if (isset($_SESSION['message'])) {
            $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
            $alertClass = ($type === 'success') ? 'alert-success' : 'alert-danger';
            echo "<div class='alert $alertClass alert-dismissible fade show' role='alert' style='border-radius: 20px; margin-bottom: 25px;'>
                    {$_SESSION['message']}
                    <button type='button' class='close' data-dismiss='alert'>
                        <span>&times;</span>
                    </button>
                  </div>";
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <form method="POST" action="signup_process.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter 11 digit phone number" pattern="[0-9]{11}" required>
            </div>

            <div class="form-group">
                <label for="gender">Gender</label>
                <select class="form-control" id="gender" name="gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password (min 6 characters)" minlength="6" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password" minlength="6" required>
            </div>

            <button type="submit" class="btn btn-signup">Create Account</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
