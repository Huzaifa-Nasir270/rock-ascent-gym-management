<?php
/**
 * Payments Page Diagnostic Test
 */

require_once('config/functions.php');

$diagnostics = [];

// Test 1: Database connection
try {
    $result = $conn->query("SELECT 1");
    $diagnostics[] = ['✅ Database connection OK', 'success'];
} catch (Exception $e) {
    $diagnostics[] = ['❌ Database connection failed: ' . $e->getMessage(), 'danger'];
}

// Test 2: Check payments table
try {
    $result = $conn->query("SELECT COUNT(*) FROM payments");
    if ($result) {
        $count = $result->fetch_row()[0];
        $diagnostics[] = ['✅ Payments table exists (' . $count . ' records)', 'success'];
    }
} catch (Exception $e) {
    $diagnostics[] = ['❌ Payments table error: ' . $e->getMessage(), 'danger'];
}

// Test 3: Check users table
try {
    $result = $conn->query("SELECT COUNT(*) FROM users");
    if ($result) {
        $count = $result->fetch_row()[0];
        $diagnostics[] = ['✅ Users table exists (' . $count . ' records)', 'success'];
    }
} catch (Exception $e) {
    $diagnostics[] = ['❌ Users table error: ' . $e->getMessage(), 'danger'];
}

// Test 4: Required functions
$functions_to_check = ['formatCurrency', 'formatDate', 'createNotification', 'sanitize', 'redirect'];
foreach ($functions_to_check as $func) {
    if (function_exists($func)) {
        $diagnostics[] = ['✅ Function ' . $func . '() exists', 'success'];
    } else {
        $diagnostics[] = ['❌ Function ' . $func . '() NOT found', 'danger'];
    }
}

// Test 5: Test query
try {
    $stmt = $conn->prepare("SELECT p.*, u.name FROM payments p LEFT JOIN users u ON p.user_id = u.user_id ORDER BY p.created_at DESC LIMIT 1");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->num_rows;
        $diagnostics[] = ['✅ Payment query works (found ' . $count . ' test record)', 'success'];
    } else {
        $diagnostics[] = ['❌ Payment query failed: ' . $conn->error, 'danger'];
    }
} catch (Exception $e) {
    $diagnostics[] = ['❌ Payment query exception: ' . $e->getMessage(), 'danger'];
}

// Test 6: Test update query
try {
    // Test prepare only, don't execute
    $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE payment_id = ?");
    if ($stmt) {
        $diagnostics[] = ['✅ Update query prepared successfully', 'success'];
    } else {
        $diagnostics[] = ['❌ Update query prepare failed: ' . $conn->error, 'danger'];
    }
} catch (Exception $e) {
    $diagnostics[] = ['❌ Update query exception: ' . $e->getMessage(), 'danger'];
}

// Test 7: Check payments page file
if (file_exists('admin/payments.php')) {
    $diagnostics[] = ['✅ Payments page file exists', 'success'];
} else {
    $diagnostics[] = ['❌ Payments page file not found', 'danger'];
}

// Test 8: Sample payment data
try {
    $result = $conn->query("SELECT payment_id, amount, status, payment_method FROM payments ORDER BY created_at DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $payment = $result->fetch_assoc();
        $diagnostics[] = ['✅ Sample payment exists: ID #' . $payment['payment_id'] . ', Status: ' . $payment['status'], 'info'];
    } else {
        $diagnostics[] = ['ℹ️ No payments in database (create one to test)', 'info'];
    }
} catch (Exception $e) {
    $diagnostics[] = ['❌ Error fetching sample payment: ' . $e->getMessage(), 'danger'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments Page Diagnostic</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; }
        .diagnostic { padding: 10px; margin: 8px 0; border-left: 4px solid #ccc; background: white; }
        .diagnostic.success { border-left-color: #28a745; }
        .diagnostic.danger { border-left-color: #dc3545; }
        .diagnostic.info { border-left-color: #17a2b8; }
        .diagnostic.warning { border-left-color: #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🔧 Payments Page Diagnostic</h1>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">System Diagnostics</h5>
            </div>
            <div class="card-body">
                <?php foreach ($diagnostics as $diag): ?>
                    <div class="diagnostic <?php echo $diag[1]; ?>">
                        <?php echo $diag[0]; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">💡 Troubleshooting Steps</h5>
            </div>
            <div class="card-body">
                <h6>If you see ERROR 500:</h6>
                <ol>
                    <li>Ensure all required functions exist in config/functions.php</li>
                    <li>Check database connection in config/db.php</li>
                    <li>Verify payments table exists with proper structure</li>
                    <li>Check PHP error logs in XAMPP</li>
                    <li>Try accessing payments page now</li>
                </ol>

                <h6 class="mt-3">What Was Fixed:</h6>
                <ul>
                    <li>✅ Added proper error handling to payment queries</li>
                    <li>✅ Fixed JavaScript data escaping in onclick handlers</li>
                    <li>✅ Added try-catch blocks for notification creation</li>
                    <li>✅ Improved error messages and diagnostics</li>
                </ul>
            </div>
        </div>

        <div class="text-center">
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
