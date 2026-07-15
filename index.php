<?php
/**
 * Home Page / Index
 */

ob_start();
session_start();
require_once('config/functions.php');

// Redirect based on user role
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
} elseif (isset($_SESSION['instructor_id'])) {
    header("Location: instructor/dashboard.php");
    exit();
} elseif (isset($_SESSION['user_id'])) {
    header("Location: user/dashboard.php");
    exit();
}

// Not logged in, show welcome page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Rock Ascent - Welcome</title>
    <?php 
    $depth = 0;
    $root = '';
    include('includes/head.php'); 
    ?>
    <style>
        .hero-section {
            padding: 120px 20px;
            text-align: center;
            animation: fadeInUp 0.8s ease-out;
        }
        .hero-section h1 {
            font-size: 4.5rem;
            font-weight: 900;
            margin-bottom: 20px;
        }
        .btn-lg-custom {
            padding: 18px 50px;
            font-size: 18px;
            border-radius: 20px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 20px 40px rgba(236, 72, 153, 0.3);
            transition: all 0.4s;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand font-weight-bold" href="#">🏋️ Rock Ascent</a>
            <div class="ml-auto">
                <a href="auth/login.php" class="btn btn-primary">Login</a>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <h1>🏋️Rock Ascent</h1>
            <p>Your Complete Gym Management Solution</p>
            <p>Manage memberships, workouts, payments, and instructors with a modern, responsive UI.</p>
            <a href="auth/login.php" class="btn btn-light btn-lg-custom">Get Started</a>
        </div>
    </div>

    <div class="container my-5">
        <h2 class="text-center mb-5">Why Choose Project Rock Ascent?</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fas fa-users"></i>
                    <h5>User Management</h5>
                    <p>Easily manage gym members, instructors, and staff</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fas fa-dumbbell"></i>
                    <h5>Workout Plans</h5>
                    <p>Create and assign personalized workout programs</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fas fa-credit-card"></i>
                    <h5>Payment System</h5>
                    <p>Track memberships, subscriptions, and payments</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fas fa-chart-line"></i>
                    <h5>Analytics</h5>
                    <p>Get insights with comprehensive reporting</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fas fa-calendar-check"></i>
                    <h5>Attendance</h5>
                    <p>Track member attendance and engagement</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fas fa-bell"></i>
                    <h5>Notifications</h5>
                    <p>Stay connected with members and instructors</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-light py-5">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Get Started?</h2>
            <a href="auth/login.php" class="btn btn-primary btn-lg-custom">Login Now</a>
            <a href="auth/signup.php" class="btn btn-outline-primary btn-lg-custom ms-2">Sign Up</a>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
    <?php include('includes/scripts.php'); ?>
</body>
</html>
