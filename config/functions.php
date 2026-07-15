<?php
/**
 * Common Functions and Utilities
 * Used across the entire application
 */

// Enable output buffering to prevent header errors
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to user, log them instead

// Include database connection
require_once(__DIR__ . '/db.php');

/**
 * Data Integrity Migration - Normalize Fitness Goals
 * Ensures all existing users have a valid goal that matches the config keys.
 */
if (isset($conn)) {
    // Map 'General Fitness' and other common variants to standard 'Stay Fit'
    $conn->query("UPDATE users SET fitness_goal = 'Stay Fit' WHERE fitness_goal IN ('General Fitness', '', 'None') OR fitness_goal IS NULL");
    
    // Normalize casing for common goals
    $conn->query("UPDATE users SET fitness_goal = 'Weight Gain' WHERE LOWER(fitness_goal) = 'weight gain'");
    $conn->query("UPDATE users SET fitness_goal = 'Weight Loss' WHERE LOWER(fitness_goal) = 'weight loss'");
    $conn->query("UPDATE users SET fitness_goal = 'Stay Fit' WHERE LOWER(fitness_goal) = 'stay fit'");
}

/**
 * Hash password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Normalize status values used for active accounts
 */
function isActiveStatus($status) {
    if ($status === null) {
        return false;
    }
    $status = strtolower(trim($status));
    return in_array($status, ['active', '1', 'yes', 'y', 'true'], true);
}

/**
 * Verify current and legacy password hashes.
 * Supports bcrypt plus older raw/md5/sha1/sha256 fallback for legacy database records.
 */
function verifyPasswordWithLegacy($password, $hash) {
    if (empty($hash)) {
        return false;
    }

    if (password_verify($password, $hash)) {
        return true;
    }

    $legacyHashes = [
        $password,
        md5($password),
        sha1($password),
        hash('sha256', $password)
    ];

    return in_array($hash, $legacyHashes, true);
}

/**
 * Upgrade legacy password hashes to bcrypt after successful legacy login.
 */
function upgradePasswordHash($table, $idField, $id, $password) {
    global $conn;
    $newHash = hashPassword($password);
    $query = "UPDATE $table SET password = ? WHERE $idField = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $newHash, $id);
    return $stmt->execute();
}

/**
 * Returns true when the stored hash is bcrypt.
 */
function isBcryptHash($hash) {
    return is_string($hash) && preg_match('/^\$2[aby]\$/', $hash);
}

/**
 * Reset all non-bcrypt password records to a known default.
 */
function resetLegacyPasswords($defaultPassword) {
    global $conn;
    $newHash = hashPassword($defaultPassword);
    $counts = [
        'users' => 0,
        'instructors' => 0,
        'admin' => 0,
    ];

    $queries = [
        'users' => "UPDATE users SET password = ? WHERE password NOT LIKE '$2a$%' AND password NOT LIKE '$2b$%' AND password NOT LIKE '$2y$%'",
        'instructors' => "UPDATE instructors SET password = ? WHERE password NOT LIKE '$2a$%' AND password NOT LIKE '$2b$%' AND password NOT LIKE '$2y$%'",
        'admin' => "UPDATE admin SET password = ? WHERE password NOT LIKE '$2a$%' AND password NOT LIKE '$2b$%' AND password NOT LIKE '$2y$%'",
    ];

    foreach ($queries as $table => $query) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $newHash);
        $stmt->execute();
        $counts[$table] = $stmt->affected_rows;
    }

    return $counts;
}

/**
 * Sanitize input to prevent SQL injection
 */
function sanitize($input) {
    global $conn;
    return $conn->real_escape_string(trim($input));
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['instructor_id']) || isset($_SESSION['admin_id']);
}

/**
 * Get user role from session
 */
function getUserRole() {
    if (isset($_SESSION['admin_id'])) {
        return 'admin';
    } elseif (isset($_SESSION['instructor_id'])) {
        return 'instructor';
    } elseif (isset($_SESSION['user_id'])) {
        return 'user';
    }
    return null;
}

/**
 * Redirect to page with optional message
 */
function redirect($page, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    // Clear output buffer before redirecting
    while (ob_get_level()) {
        ob_end_clean();
    }
    header("Location: $page");
    exit();
}

/**
 * Display flash message
 */
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'success';
        $alertClass = ($type === 'success') ? 'alert-success' : 'alert-danger';
        echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                $message
                <button type='button' class='close' data-dismiss='alert'>
                    <span>&times;</span>
                </button>
              </div>";
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}

/**
 * Check user authorization for a page
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('../auth/login.php', 'Please login first', 'danger');
    }
}

/**
 * Check admin authorization
 */
function requireAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        redirect('../auth/login.php', 'Admin access required', 'danger');
    }
}

/**
 * Check instructor authorization
 */
function requireInstructor() {
    if (!isset($_SESSION['instructor_id'])) {
        redirect('../auth/login.php', 'Instructor access required', 'danger');
    }
}

/**
 * Check user authorization
 */
function requireUser() {
    if (!isset($_SESSION['user_id'])) {
        redirect('../auth/login.php', 'User access required', 'danger');
    }
}

/**
 * Get days remaining in subscription
 */
function getDaysRemaining($endDate) {
    if (empty($endDate) || $endDate == '0000-00-00' || $endDate == '1970-01-01') {
        return 0;
    }
    $today = date('Y-m-d');
    $endTimestamp = strtotime($endDate);
    if ($endTimestamp === false) return 0;
    
    $diff = $endTimestamp - strtotime($today);
    return (int)ceil($diff / (60 * 60 * 24));
}

/**
 * Format date
 */
function formatDate($date) {
    if (empty($date) || $date == '0000-00-00' || $date == '1970-01-01') {
        return 'Not Set';
    }
    $timestamp = strtotime($date);
    if ($timestamp === false) return 'Invalid Date';
    return date('d M Y', $timestamp);
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return 'PKR ' . number_format($amount, 0);
}

/**
 * Get user details by ID
 */
function getUserDetails($userId) {
    global $conn;
    $query = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get instructor details by ID
 */
function getInstructorDetails($instructorId) {
    global $conn;
    $query = "SELECT * FROM instructors WHERE instructor_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $instructorId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get active subscription for user
 */
function getUserSubscription($userId) {
    global $conn;
    $query = "SELECT s.*, p.name as package_name, p.price as package_price, p.description as package_description 
              FROM subscriptions s 
              JOIN packages p ON s.package_id = p.package_id 
              WHERE s.user_id = ? AND s.status = 'Active' AND s.end_date >= CURDATE() 
              ORDER BY s.created_at DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Get package details
 */
function getPackageDetails($packageId) {
    global $conn;
    $query = "SELECT * FROM packages WHERE package_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $packageId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Mark notification as read
 */
function markNotificationAsRead($notificationId) {
    global $conn;
    $query = "UPDATE notifications SET is_read = 'Yes', read_at = NOW() WHERE notification_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $notificationId);
    return $stmt->execute();
}

/**
 * Create notification
 */
function createNotification($userId, $senderId, $senderType, $title, $message) {
    global $conn;
    $query = "INSERT INTO notifications (user_id, sender_id, sender_type, title, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisss", $userId, $senderId, $senderType, $title, $message);
    return $stmt->execute();
}

/**
 * Get unread notification count
 */
function getUnreadNotificationCount($userId) {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 'No'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'];
}

/**
 * Start session if not already started
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Clean literal newline characters (\r\n, \r, \n) from strings.
 * This handles cases where newlines were incorrectly saved as literal text.
 */
function cleanLiteralNewlines($text) {
    if (empty($text)) return '';
    // Handle double-escaped: \\r\\n, \\r, \\n (stored as literal backslash sequences)
    $text = str_replace(['\\\\r\\\\n', '\\\\r', '\\\\n'], "\n", $text);
    // Handle single-escaped: \r\n, \r, \n (stored as backslash + letter)
    $text = str_replace(['\\r\\n', '\\r', '\\n'], "\n", $text);
    return $text;
}

?>
