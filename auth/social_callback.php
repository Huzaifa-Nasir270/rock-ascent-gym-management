<?php
/**
 * Social Login Callback
 * Handles POST from the Google sign-in simulation.
 * Looks up the submitted email across admin → instructor → user tables
 * and creates the appropriate session.
 */
require_once('../config/functions.php');

// ── Accept both GET (legacy demo links) and POST (new Google flow) ──────────
$provider = $_POST['provider'] ?? $_GET['provider'] ?? 'google';
$gEmail   = isset($_POST['g_email']) ? trim($_POST['g_email']) : '';

// Legacy GET-based role parameter (Facebook demo still uses this)
$legacyRole = $_GET['role'] ?? '';

// ── If legacy GET role is provided (Facebook demo) ───────────────────────────
if (!empty($legacyRole) && empty($gEmail)) {
    $providerName = ucfirst(htmlspecialchars($provider));

    try {
        switch ($legacyRole) {
            case 'admin':
                $result = $conn->query("SELECT * FROM admin LIMIT 1");
                if ($result && $result->num_rows > 0) {
                    $admin = $result->fetch_assoc();
                    $_SESSION['admin_id']    = $admin['admin_id'];
                    $_SESSION['admin_name']  = $admin['name'];
                    $_SESSION['admin_email'] = $admin['email'];
                    redirect('../admin/dashboard.php', "Welcome, {$admin['name']}! Logged in via {$providerName}.");
                }
                redirect('login.php', 'No admin account found.', 'danger');
                break;

            case 'instructor':
                $result = $conn->query("SELECT * FROM instructors LIMIT 1");
                if ($result && $result->num_rows > 0) {
                    $inst = $result->fetch_assoc();
                    $_SESSION['instructor_id']    = $inst['instructor_id'];
                    $_SESSION['instructor_name']  = $inst['name'];
                    $_SESSION['instructor_email'] = $inst['email'];
                    redirect('../instructor/dashboard.php', "Welcome, {$inst['name']}! Logged in via {$providerName}.");
                }
                redirect('login.php', 'No instructor account found.', 'danger');
                break;

            case 'member':
            default:
                $result = $conn->query("SELECT * FROM users LIMIT 1");
                if ($result && $result->num_rows > 0) {
                    $user = $result->fetch_assoc();
                    $_SESSION['user_id']    = $user['user_id'];
                    $_SESSION['user_name']  = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    redirect('../user/dashboard.php', "Welcome, {$user['name']}! Logged in via {$providerName}.");
                }
                redirect('login.php', 'No user account found.', 'danger');
        }
    } catch (Exception $e) {
        redirect('login.php', 'Login error: ' . $e->getMessage(), 'danger');
    }
    exit;
}

// ── Google flow: email-based lookup ─────────────────────────────────────────
if (empty($gEmail)) {
    redirect('login.php', 'No email provided.', 'danger');
}

if (!filter_var($gEmail, FILTER_VALIDATE_EMAIL)) {
    redirect('login.php', 'Invalid email address.', 'danger');
}

$safeEmail = $conn->real_escape_string($gEmail);

try {
    // 1. Check admin table
    $res = $conn->query("SELECT * FROM admin WHERE email = '$safeEmail' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $admin = $res->fetch_assoc();
        $_SESSION['admin_id']    = $admin['admin_id'];
        $_SESSION['admin_name']  = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        redirect('../admin/dashboard.php', "Welcome back, {$admin['name']}! Signed in with Google.");
    }

    // 2. Check instructors table
    $res = $conn->query("SELECT * FROM instructors WHERE email = '$safeEmail' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $inst = $res->fetch_assoc();
        $_SESSION['instructor_id']    = $inst['instructor_id'];
        $_SESSION['instructor_name']  = $inst['name'];
        $_SESSION['instructor_email'] = $inst['email'];
        redirect('../instructor/dashboard.php', "Welcome back, {$inst['name']}! Signed in with Google.");
    }

    // 3. Check users table
    $res = $conn->query("SELECT * FROM users WHERE email = '$safeEmail' LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $_SESSION['user_id']    = $user['user_id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        redirect('../user/dashboard.php', "Welcome back, {$user['name']}! Signed in with Google.");
    }

    // 4. Email not found in any table → show "account not found" page
    $_SESSION['google_email_not_found'] = $gEmail;
    redirect('google_not_found.php');

} catch (Exception $e) {
    redirect('login.php', 'Google Sign-In error: ' . $e->getMessage(), 'danger');
}
