<?php
/**
 * Admin Dashboard
 * Displays statistics and overview
 */

require_once('../config/functions.php');
requireAdmin();

try {
    // Get statistics
    $totalUsers = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'Active'")->fetch_assoc()['count'] ?? 0;
    $totalInstructors = $conn->query("SELECT COUNT(*) as count FROM instructors WHERE status = 'Active'")->fetch_assoc()['count'] ?? 0;
    $totalRevenue = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status = 'Paid'")->fetch_assoc()['total'] ?? 0;
    $totalPackages = $conn->query("SELECT COUNT(*) as count FROM packages WHERE status = 'Active'")->fetch_assoc()['count'] ?? 0;
    $pendingPayments = $conn->query("SELECT COUNT(*) as count FROM payments WHERE status = 'Pending'")->fetch_assoc()['count'] ?? 0;
    $activeSubscriptions = $conn->query("SELECT COUNT(*) as count FROM subscriptions WHERE status = 'Active' AND end_date >= CURDATE()")->fetch_assoc()['count'] ?? 0;

    // Get recent payments
    $recentPayments = $conn->query("
        SELECT p.*, u.name, s.package_id 
        FROM payments p 
        LEFT JOIN users u ON p.user_id = u.user_id 
        LEFT JOIN subscriptions s ON p.subscription_id = s.subscription_id 
        ORDER BY p.created_at DESC 
        LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC) ?? [];
} catch (Exception $e) {
    ob_end_clean();
    redirect('../auth/login.php', 'Error loading dashboard: ' . $e->getMessage(), 'danger');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard - Gym Management</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/admin_sidebar.php'); ?>

            <div class="main-content">
                <h2 class="mb-4">📊 Dashboard Overview</h2>

                <?php displayMessage(); ?>

                <!-- Stats Row -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">👥</div>
                            <div class="stat-number"><?php echo $totalUsers; ?></div>
                            <div class="stat-label">Active Members</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">🏋️</div>
                            <div class="stat-number"><?php echo $totalInstructors; ?></div>
                            <div class="stat-label">Instructors</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">💰</div>
                            <div class="stat-number"><?php echo formatCurrency($totalRevenue); ?></div>
                            <div class="stat-label">Total Revenue</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-number"><?php echo $totalPackages; ?></div>
                            <div class="stat-label">Packages</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">⏳</div>
                            <div class="stat-number"><?php echo $pendingPayments; ?></div>
                            <div class="stat-label">Pending Payments</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon">✅</div>
                            <div class="stat-number"><?php echo $activeSubscriptions; ?></div>
                            <div class="stat-label">Active Subscriptions</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Payments -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Payments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Member</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($recentPayments)) {
                                        foreach ($recentPayments as $payment) {
                                            $statusBadge = $payment['status'] === 'Paid' ? 
                                                'badge-success' : ($payment['status'] === 'Pending' ? 'badge-warning' : 'badge-danger');
                                            echo "
                                            <tr>
                                                <td>#" . $payment['payment_id'] . "</td>
                                                <td>" . htmlspecialchars($payment['name'] ?? 'N/A') . "</td>
                                                <td>" . formatCurrency($payment['amount']) . "</td>
                                                <td><span class='badge $statusBadge'>" . $payment['status'] . "</span></td>
                                                <td>" . formatDate($payment['payment_date']) . "</td>
                                                <td>
                                                    <a href='payments.php' class='btn btn-sm btn-primary'>View</a>
                                                </td>
                                            </tr>
                                            ";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center text-muted'>No payments yet</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="users.php" class="btn btn-primary">MANAGE USERS</a>
                        <a href="instructors.php" class="btn btn-primary">MANAGE INSTRUCTORS</a>
                        <a href="packages.php" class="btn btn-primary">MANAGE PACKAGES</a>
                        <a href="payments.php" class="btn btn-primary">VIEW PAYMENTS</a>
                        <a href="reports.php" class="btn btn-primary">VIEW REPORTS</a>
                        <a href="reset_passwords.php" class="btn btn-primary">RESET LEGACY PASSWORDS</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
</body>
</html>
