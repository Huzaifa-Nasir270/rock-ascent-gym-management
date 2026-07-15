<?php
/**
 * Test Payment Notification System
 * This script helps verify that payment notifications are working correctly
 * Delete this file after testing
 */

require_once('config/functions.php');

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    die('<h2>❌ Error: Admin session not found. Please login as admin first.</h2>');
}

$testResults = [];

// Test 1: Check if createNotification function exists
try {
    if (function_exists('createNotification')) {
        $testResults[] = ['✅ createNotification function exists', 'success'];
    } else {
        $testResults[] = ['❌ createNotification function NOT found', 'danger'];
    }
} catch (Exception $e) {
    $testResults[] = ['❌ Error checking function: ' . $e->getMessage(), 'danger'];
}

// Test 2: Check database connection
try {
    $result = $conn->query("SELECT 1");
    if ($result) {
        $testResults[] = ['✅ Database connection working', 'success'];
    }
} catch (Exception $e) {
    $testResults[] = ['❌ Database connection failed: ' . $e->getMessage(), 'danger'];
}

// Test 3: Check notifications table exists
try {
    $result = $conn->query("SELECT COUNT(*) FROM notifications LIMIT 1");
    if ($result) {
        $count = $result->fetch_row()[0];
        $testResults[] = ['✅ Notifications table exists (' . $count . ' records)', 'success'];
    }
} catch (Exception $e) {
    $testResults[] = ['❌ Error checking notifications table: ' . $e->getMessage(), 'danger'];
}

// Test 4: Check payments table exists
try {
    $result = $conn->query("SELECT COUNT(*) FROM payments LIMIT 1");
    if ($result) {
        $count = $result->fetch_row()[0];
        $testResults[] = ['✅ Payments table exists (' . $count . ' records)', 'success'];
    }
} catch (Exception $e) {
    $testResults[] = ['❌ Error checking payments table: ' . $e->getMessage(), 'danger'];
}

// Test 5: Check for Pending payments
try {
    $result = $conn->query("SELECT COUNT(*) FROM payments WHERE status = 'Pending'");
    if ($result) {
        $count = $result->fetch_row()[0];
        $testResults[] = ['✅ Found ' . $count . ' pending payments to mark as paid', 'info'];
    }
} catch (Exception $e) {
    $testResults[] = ['❌ Error checking pending payments: ' . $e->getMessage(), 'danger'];
}

// Test 6: Check for recent notifications
try {
    $result = $conn->query("SELECT COUNT(*) FROM notifications WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    if ($result) {
        $count = $result->fetch_row()[0];
        $testResults[] = ['✅ Found ' . $count . ' notifications sent in last hour', 'info'];
    }
} catch (Exception $e) {
    $testResults[] = ['❌ Error checking recent notifications: ' . $e->getMessage(), 'danger'];
}

// Test 7: Check for payment notifications specifically
try {
    $result = $conn->query("SELECT COUNT(*) FROM notifications WHERE title LIKE '%Payment%' AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    if ($result) {
        $count = $result->fetch_row()[0];
        $testResults[] = ['✅ Found ' . $count . ' payment notifications in last 24 hours', 'info'];
    }
} catch (Exception $e) {
    $testResults[] = ['❌ Error checking payment notifications: ' . $e->getMessage(), 'danger'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Notification System Test</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 20px; background: #f5f5f5; }
        .test-container { max-width: 600px; margin: 0 auto; }
        .card { margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .test-result { padding: 12px; margin: 10px 0; border-left: 4px solid #ccc; background: white; }
        .test-result.success { border-left-color: #28a745; }
        .test-result.danger { border-left-color: #dc3545; }
        .test-result.info { border-left-color: #17a2b8; }
        .instructions { background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="mb-4">🔔 Payment Notification System Test</h1>
        
        <div class="instructions">
            <h5>📋 How to Test Payment Notifications:</h5>
            <ol>
                <li>Mark a pending payment as "Paid" on the Payments page</li>
                <li>The system will automatically send a notification to the member</li>
                <li>The member can view it in their Notifications page</li>
                <li>Check the results below to verify everything is set up correctly</li>
            </ol>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">✅ System Status Report</h5>
            </div>
            <div class="card-body">
                <?php foreach ($testResults as $result): ?>
                    <div class="test-result <?php echo $result[1]; ?>">
                        <?php echo $result[0]; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">🔧 Available Actions</h5>
            </div>
            <div class="card-body">
                <p><strong>To test payment notifications:</strong></p>
                <ol>
                    <li>Go to <a href="admin/payments.php" class="btn btn-sm btn-primary">Payments Page</a></li>
                    <li>Find a payment with "Pending" status</li>
                    <li>Click "Mark Paid" button</li>
                    <li>Confirm in the modal dialog</li>
                    <li>User will automatically receive a notification</li>
                </ol>
                
                <hr>
                
                <p><strong>To view payment notifications as a member:</strong></p>
                <ol>
                    <li>Login as user (e.g., user@gym.com / password123)</li>
                    <li>Go to Dashboard</li>
                    <li>Click "Notifications" in the sidebar</li>
                    <li>View all payment confirmations and other notifications</li>
                </ol>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0">📝 Notes</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Notifications are sent automatically when payment status changes to "Paid"</li>
                    <li>Only payments that transition TO "Paid" status will trigger notifications</li>
                    <li>Each notification includes member name, amount, payment method, date, and transaction ID</li>
                    <li>Notifications are stored in the database and displayed in real-time</li>
                    <li>Delete this test file (test_payment_notification.php) after verification</li>
                </ul>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="admin/payments.php" class="btn btn-primary btn-lg">
                <i class="fas fa-credit-card"></i> Go to Payments Page
            </a>
            <a href="admin/dashboard.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
