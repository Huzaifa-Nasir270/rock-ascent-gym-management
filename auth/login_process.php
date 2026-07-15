<?php
/**
 * Login Processing
 * Handles login for Admin, Instructor, and Users
 */

// Enable output buffering to prevent header errors
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once('../config/functions.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = sanitize($_POST['role'] ?? 'user');
        $remember = isset($_POST['remember']);

        // Mandatory Terms and Conditions / Remember Me check
        if (!$remember) {
            redirect('login.php', 'You must agree to the Terms & Conditions and Remember me to login.', 'danger');
        }

        // Validation
        if (empty($email) || empty($password) || empty($role)) {
            redirect('login.php', 'Please fill all fields', 'danger');
        }

        if (!isValidEmail($email)) {
            redirect('login.php', 'Invalid email format', 'danger');
        }

        // Check login based on role
        if ($role === 'admin') {
            // Admin login
            $query = "SELECT * FROM admin WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();

            if ($admin && verifyPasswordWithLegacy($password, $admin['password'])) {
                if (!password_verify($password, $admin['password'])) {
                    upgradePasswordHash('admin', 'admin_id', $admin['admin_id'], $password);
                }
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];

                // Remember Me Cookie
                setcookie('remember_user', $email, time() + (86400 * 30), "/"); // 30 days

                redirect('../admin/dashboard.php', 'Welcome Admin!', 'success');
            } else {
                redirect('login.php', 'Invalid admin credentials', 'danger');
            }
        } elseif ($role === 'instructor') {
            // Instructor login
            $query = "SELECT * FROM instructors WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $instructor = $result->fetch_assoc();

            if (!$instructor) {
                redirect('login.php', 'Invalid instructor credentials or account inactive', 'danger');
            }

            if (!isActiveStatus($instructor['status'])) {
                redirect('login.php', 'Invalid instructor credentials or account inactive', 'danger');
            }

            if (verifyPasswordWithLegacy($password, $instructor['password'])) {
                if (!password_verify($password, $instructor['password'])) {
                    upgradePasswordHash('instructors', 'instructor_id', $instructor['instructor_id'], $password);
                }
                $_SESSION['instructor_id'] = $instructor['instructor_id'];
                $_SESSION['instructor_name'] = $instructor['name'];
                $_SESSION['instructor_email'] = $instructor['email'];

                // Remember Me Cookie
                setcookie('remember_user', $email, time() + (86400 * 30), "/");

                redirect('../instructor/dashboard.php', 'Welcome Instructor!', 'success');
            } else {
                redirect('login.php', 'Invalid instructor credentials or account inactive', 'danger');
            }
        } else {
            // User login
            $query = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user) {
                redirect('login.php', 'Invalid user credentials or account inactive', 'danger');
            }

            if (!isActiveStatus($user['status'])) {
                redirect('login.php', 'Invalid user credentials or account inactive', 'danger');
            }

            if (verifyPasswordWithLegacy($password, $user['password'])) {
                if (!password_verify($password, $user['password'])) {
                    upgradePasswordHash('users', 'user_id', $user['user_id'], $password);
                }
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                // Remember Me Cookie
                setcookie('remember_user', $email, time() + (86400 * 30), "/");

                redirect('../user/dashboard.php', 'Login successful!', 'success');
            } else {
                redirect('login.php', 'Invalid user credentials or account inactive', 'danger');
            }
        }
    } catch (Exception $e) {
        redirect('login.php', 'An error occurred: ' . $e->getMessage(), 'danger');
    }
}

?>
