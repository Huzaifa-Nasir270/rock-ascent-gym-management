<?php
/**
 * Login Diagnostic Tool
 * Tests actual login flow with your database records
 */

require_once 'config/db.php';
require_once 'config/functions.php';

echo "=== LOGIN DIAGNOSTIC ===\n\n";

// Test 1: List all users with their password formats
echo "1. USER RECORDS IN DATABASE:\n";
echo str_repeat("-", 80) . "\n";

$users = $conn->query("SELECT user_id, name, email, status, password FROM users LIMIT 20")->fetch_all(MYSQLI_ASSOC);

foreach ($users as $user) {
    $hash = $user['password'];
    $isBcrypt = preg_match('/^\$2[aby]\$\d{2}\$/', $hash);
    echo "ID: {$user['user_id']} | Email: {$user['email']} | Status: {$user['status']}\n";
    echo "   Password Hash: " . substr($hash, 0, 60) . "...\n";
    echo "   Is Bcrypt: " . ($isBcrypt ? "YES" : "NO") . "\n";
    
    // Test if 'password123' verifies
    if ($isBcrypt) {
        $result = password_verify('password123', $hash);
        echo "   Verifies 'password123': " . ($result ? "YES ✓" : "NO ✗") . "\n";
    } else {
        echo "   Legacy hash format - needs manual check\n";
    }
    echo "\n";
}

echo "\n2. INSTRUCTOR RECORDS IN DATABASE:\n";
echo str_repeat("-", 80) . "\n";

$instructors = $conn->query("SELECT instructor_id, name, email, status, password FROM instructors LIMIT 10")->fetch_all(MYSQLI_ASSOC);

foreach ($instructors as $inst) {
    $hash = $inst['password'];
    $isBcrypt = preg_match('/^\$2[aby]\$\d{2}\$/', $hash);
    echo "ID: {$inst['instructor_id']} | Email: {$inst['email']} | Status: {$inst['status']}\n";
    echo "   Password Hash: " . substr($hash, 0, 60) . "...\n";
    echo "   Is Bcrypt: " . ($isBcrypt ? "YES" : "NO") . "\n";
    
    if ($isBcrypt) {
        $result = password_verify('password123', $hash);
        echo "   Verifies 'password123': " . ($result ? "YES ✓" : "NO ✗") . "\n";
    }
    echo "\n";
}

echo "\n3. ADMIN RECORDS IN DATABASE:\n";
echo str_repeat("-", 80) . "\n";

$admins = $conn->query("SELECT admin_id, name, email, password FROM admin LIMIT 10")->fetch_all(MYSQLI_ASSOC);

foreach ($admins as $admin) {
    $hash = $admin['password'];
    $isBcrypt = preg_match('/^\$2[aby]\$\d{2}\$/', $hash);
    echo "ID: {$admin['admin_id']} | Email: {$admin['email']}\n";
    echo "   Password Hash: " . substr($hash, 0, 60) . "...\n";
    echo "   Is Bcrypt: " . ($isBcrypt ? "YES" : "NO") . "\n";
    
    if ($isBcrypt) {
        $result = password_verify('password123', $hash);
        echo "   Verifies 'password123': " . ($result ? "YES ✓" : "NO ✗") . "\n";
    }
    echo "\n";
}

echo "\n4. TESTING verifyPasswordWithLegacy FUNCTION:\n";
echo str_repeat("-", 80) . "\n";

// Test the function with first user
if (!empty($users)) {
    $firstUser = $users[0];
    echo "Testing with: {$firstUser['email']}\n";
    echo "Hash: {$firstUser['password']}\n";
    
    $legacyResult = verifyPasswordWithLegacy('password123', $firstUser['password']);
    echo "verifyPasswordWithLegacy('password123', hash): " . ($legacyResult ? "YES ✓" : "NO ✗") . "\n";
}

echo "\n=== END DIAGNOSTIC ===\n";