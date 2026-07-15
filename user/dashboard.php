<?php
/**
 * Member Dashboard
 */

require_once('../config/functions.php');
requireUser();

try {
    $userId = $_SESSION['user_id'];
    $user = getUserDetails($userId);
    $subscription = getUserSubscription($userId);

    // Get statistics
    $totalPayments = $conn->query("SELECT COUNT(*) as count FROM payments WHERE user_id = $userId")->fetch_assoc()['count'] ?? 0;
    $attendanceDays = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE user_id = $userId AND status = 'Present'")->fetch_assoc()['count'] ?? 0;
    $workoutPlans = $conn->query("SELECT COUNT(*) as count FROM workout_plans WHERE user_id = $userId")->fetch_assoc()['count'] ?? 0;
    $unreadNotifications = function_exists('getUnreadNotificationCount') ? getUnreadNotificationCount($userId) : 0;
    
    // Get recent announcements for users
    $recentAnnouncements = $conn->query("SELECT * FROM announcements WHERE status = 'Published' AND audience IN ('Users', 'Both') ORDER BY created_at DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    redirect('../auth/login.php', 'Error loading dashboard: ' . $e->getMessage(), 'danger');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Member Dashboard - Gym Management</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Unified Sidebar -->
            <?php include('../includes/user_sidebar.php'); ?>

            <div class="main-content">
                <!-- Dynamic Greeting Section -->
                <?php 
                    $hour = date('H');
                    $greeting = ($hour < 12) ? 'Good Morning' : (($hour < 18) ? 'Good Afternoon' : 'Good Evening');
                ?>
                <div class="welcome-section mb-5 fade-in-up">
                    <h1 class="mb-2"><?php echo $greeting; ?>, <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo htmlspecialchars($user['name']); ?>!</span> 👋</h1>
                    <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Let's make today another step toward your fitness peak.</p>
                </div>

                <?php displayMessage(); ?>

                <!-- Stats Grid -->
                <div class="row fade-in-up">
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon"><i class="fas fa-calendar-check text-warning"></i></div>
                            <div class="stat-number" style="font-size: 2.5rem; font-weight: 800;"><?php echo $attendanceDays; ?></div>
                            <div class="stat-label small text-muted font-weight-bold uppercase">Days Present</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon"><i class="fas fa-dumbbell text-danger"></i></div>
                            <div class="stat-number" style="font-size: 2.5rem; font-weight: 800;"><?php echo $workoutPlans; ?></div>
                            <div class="stat-label small text-muted font-weight-bold uppercase">Active Plans</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon"><i class="fas fa-credit-card text-primary"></i></div>
                            <div class="stat-number" style="font-size: 2.5rem; font-weight: 800;"><?php echo $totalPayments; ?></div>
                            <div class="stat-label small text-muted font-weight-bold uppercase">Payments</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-4">
                        <div class="stat-card text-center">
                            <div class="stat-icon"><i class="fas fa-bell text-warning"></i></div>
                            <div class="stat-number" style="font-size: 2.5rem; font-weight: 800;"><?php echo $unreadNotifications; ?></div>
                            <div class="stat-label small text-muted font-weight-bold uppercase">Unread</div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <!-- Announcements -->
                    <div class="col-lg-8 fade-in-up">
                        <div class="card h-100">
                            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-bullhorn mr-2"></i> Announcements</h5>
                                <a href="announcements.php" class="btn btn-sm btn-outline-warning" style="padding: 5px 15px !important; font-size: 11px !important;">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if(!empty($recentAnnouncements)): ?>
                                    <?php foreach($recentAnnouncements as $ann): ?>
                                        <div class="announcement-item">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="font-weight-bold text-warning"><?php echo htmlspecialchars($ann['title']); ?></h6>
                                                <small class="text-muted"><?php echo date('M d', strtotime($ann['created_at'])); ?></small>
                                            </div>
                                            <?php 
                                                $annText = cleanLiteralNewlines($ann['message'] ?? $ann['content'] ?? ''); 
                                                $displayMsg = (strlen($annText) > 150) ? substr($annText, 0, 150) . '...' : $annText;
                                            ?>
                                            <p class="small text-muted mb-0 mt-1"><?php echo nl2br(htmlspecialchars($displayMsg)); ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-info-circle fa-2x mb-3 text-muted"></i>
                                        <p class="text-muted">No recent announcements.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Details -->
                    <div class="col-lg-4 fade-in-up">
                        <div class="card h-100 text-center" style="background: linear-gradient(145deg, rgba(30, 41, 59, 0.4), rgba(15, 23, 42, 0.6)) !important;">
                            <div class="card-header border-0 text-left">
                                <h5 class="mb-0"><i class="fas fa-star mr-2"></i> Membership</h5>
                            </div>
                            <div class="card-body">
                                <?php if($subscription): ?>
                                    <div class="my-4">
                                        <div style="background: var(--primary-gradient); width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; box-shadow: 0 10px 20px rgba(236, 72, 153, 0.3);">
                                            <i class="fas fa-crown fa-2x text-white"></i>
                                        </div>
                                    </div>
                                    <h4 class="font-weight-bold"><?php echo htmlspecialchars($subscription['package_name']); ?></h4>
                                    <span class="badge badge-success mb-4">ACTIVE</span>
                                    <div class="text-left p-3 mt-2" style="background: rgba(0,0,0,0.2); border-radius: 15px;">
                                        <p class="small text-muted mb-0">Expires On</p>
                                        <p class="font-weight-bold mb-0"><?php echo formatDate($subscription['end_date']); ?></p>
                                    </div>
                                    <a href="packages.php" class="btn btn-primary btn-block mt-4">Upgrade</a>
                                <?php else: ?>
                                    <div class="py-4">
                                        <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No active membership found.</p>
                                        <a href="packages.php" class="btn btn-warning btn-block">View Packages</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
</body>
</html>
