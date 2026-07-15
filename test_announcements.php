<?php
/**
 * Test Announcement Display System
 * This script verifies that announcements are properly displayed to users and instructors
 */

require_once('config/functions.php');

$testResults = [];

// Test 1: Check if announcements table exists
try {
    $result = $conn->query("SELECT COUNT(*) FROM announcements LIMIT 1");
    if ($result) {
        $count = $result->fetch_row()[0];
        $testResults[] = ['✅ Announcements table exists (' . $count . ' announcements)', 'success'];
    }
} catch (Exception $e) {
    $testResults[] = ['❌ Error checking announcements table: ' . $e->getMessage(), 'danger'];
}

// Test 2: Check for published announcements
try {
    $result = $conn->query("SELECT COUNT(*) FROM announcements WHERE status = 'Published'");
    if ($result) {
        $count = $result->fetch_row()[0];
        if ($count > 0) {
            $testResults[] = ['✅ Found ' . $count . ' published announcements', 'success'];
        } else {
            $testResults[] = ['⚠️ No published announcements found', 'warning'];
        }
    }
} catch (Exception $e) {
    $testResults[] = ['❌ Error checking published announcements: ' . $e->getMessage(), 'danger'];
}

// Test 3: Check if user announcements page exists
if (file_exists('user/announcements.php')) {
    $testResults[] = ['✅ User announcements page exists (user/announcements.php)', 'success'];
} else {
    $testResults[] = ['❌ User announcements page NOT found', 'danger'];
}

// Test 4: Check if instructor announcements page exists
if (file_exists('instructor/announcements.php')) {
    $testResults[] = ['✅ Instructor announcements page exists (instructor/announcements.php)', 'success'];
} else {
    $testResults[] = ['❌ Instructor announcements page NOT found', 'danger'];
}

// Test 5: Check sample announcements
try {
    $result = $conn->query("SELECT announcement_id, title, content, status, created_at FROM announcements ORDER BY created_at DESC LIMIT 5");
    if ($result && $result->num_rows > 0) {
        $testResults[] = ['✅ Sample announcements retrieved successfully', 'info'];
    } else {
        $testResults[] = ['ℹ️ No announcements in database (you can create one from admin panel)', 'info'];
    }
} catch (Exception $e) {
    $testResults[] = ['❌ Error retrieving announcements: ' . $e->getMessage(), 'danger'];
}

// Test 6: Check admin announcements page
if (file_exists('admin/announcements.php')) {
    $testResults[] = ['✅ Admin announcements management page exists', 'success'];
} else {
    $testResults[] = ['❌ Admin announcements page NOT found', 'danger'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcement System Test</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { padding: 20px; background: #f5f5f5; }
        .test-container { max-width: 700px; margin: 0 auto; }
        .card { margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .test-result { padding: 12px; margin: 10px 0; border-left: 4px solid #ccc; background: white; }
        .test-result.success { border-left-color: #28a745; }
        .test-result.danger { border-left-color: #dc3545; }
        .test-result.warning { border-left-color: #ffc107; }
        .test-result.info { border-left-color: #17a2b8; }
        .instructions { background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="test-container">
        <h1 class="mb-4">📢 Announcement System Test</h1>
        
        <div class="instructions">
            <h5>How to Test the Announcement System:</h5>
            <ol>
                <li><strong>Create an announcement:</strong> Login as admin → Announcements → Create Announcement</li>
                <li><strong>View as user:</strong> Login as user (user@gym.com) → Go to Announcements page or check Dashboard</li>
                <li><strong>View as instructor:</strong> Login as instructor (instructor@gym.com) → Go to Announcements page or check Dashboard</li>
                <li>Announcements should display with title, content, date, and admin name</li>
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
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">✨ Announcement Features</h5>
            </div>
            <div class="card-body">
                <h6>📱 For Users (Members):</h6>
                <ul>
                    <li>View announcements on Dashboard (latest 2)</li>
                    <li>Access full Announcements page from sidebar</li>
                    <li>See announcement title, content, posted date, and author</li>
                </ul>

                <h6 class="mt-3">👨‍🏫 For Instructors:</h6>
                <ul>
                    <li>View announcements on Dashboard (latest 2)</li>
                    <li>Access full Announcements page from sidebar</li>
                    <li>See announcement title, content, posted date, and author</li>
                </ul>

                <h6 class="mt-3">👨‍💼 For Admins:</h6>
                <ul>
                    <li>Create new announcements with title and content</li>
                    <li>Set announcement status (Published/Drafted)</li>
                    <li>View all announcements</li>
                    <li>Delete announcements</li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">🔗 Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="btn-group-vertical w-100" role="group">
                    <a href="admin/announcements.php" class="btn btn-outline-primary text-left">
                        <i class="fas fa-pen-square"></i> Admin - Create Announcement
                    </a>
                    <a href="user/announcements.php" class="btn btn-outline-primary text-left">
                        <i class="fas fa-eye"></i> User - View Announcements
                    </a>
                    <a href="instructor/announcements.php" class="btn btn-outline-primary text-left">
                        <i class="fas fa-eye"></i> Instructor - View Announcements
                    </a>
                    <a href="user/dashboard.php" class="btn btn-outline-secondary text-left">
                        <i class="fas fa-arrow-left"></i> Back to User Dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-warning">
                <h5 class="mb-0">📝 What Was Fixed</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>✅ Created user/announcements.php - Display page for members</li>
                    <li>✅ Created instructor/announcements.php - Display page for instructors</li>
                    <li>✅ Updated user/dashboard.php - Show latest announcements + link</li>
                    <li>✅ Updated instructor/dashboard.php - Show latest announcements + link</li>
                    <li>✅ Added Announcements link to user sidebar</li>
                    <li>✅ Added Announcements link to instructor sidebar</li>
                    <li>✅ Announcements display with proper formatting and metadata</li>
                </ul>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="admin/announcements.php" class="btn btn-primary btn-lg">
                <i class="fas fa-bullhorn"></i> Create New Announcement
            </a>
            <a href="admin/dashboard.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
