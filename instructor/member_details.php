<?php
/**
 * Instructor - Member Details View
 */

require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];
$userId = sanitize($_GET['id'] ?? '');

if (empty($userId)) {
    redirect('members.php', 'Invalid Member ID', 'danger');
}

// Get member details with subscription and package
$query = "
    SELECT u.*, s.subscription_id, s.start_date, s.end_date, s.status as sub_status, p.name as packagename 
    FROM users u 
    INNER JOIN subscriptions s ON u.user_id = s.user_id 
    INNER JOIN packages p ON s.package_id = p.package_id 
    WHERE u.user_id = ? AND s.instructor_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $instructorId);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();

if (!$member) {
    redirect('members.php', 'Member not found or not assigned to you', 'danger');
}

// Get attendance summary
$attendanceStats = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present FROM attendance WHERE user_id = $userId")->fetch_assoc();
$attendanceRate = $attendanceStats['total'] > 0 ? round(($attendanceStats['present'] / $attendanceStats['total']) * 100) : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Member Details - <?php echo htmlspecialchars($member['name']); ?></title>
    <?php include('../includes/head.php'); ?>
    <style>
        .profile-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 40px;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: white;
            box-shadow: 0 10px 25px rgba(236, 72, 153, 0.3);
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .detail-label { color: var(--text-muted); font-weight: 600; }
        .detail-value { font-weight: 700; color: white; }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/instructor_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <h2 class="mb-0">👤 Member Profile</h2>
                    <a href="members.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Back to List
                    </a>
                </div>

                <?php displayMessage(); ?>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="card text-center">
                            <div class="profile-avatar mx-auto mb-4">
                                <?php echo strtoupper(substr($member['name'], 0, 1)); ?>
                            </div>
                            <h3 class="font-weight-bold mb-1"><?php echo htmlspecialchars($member['name']); ?></h3>
                            <p class="text-muted mb-4"><?php echo htmlspecialchars($member['packagename']); ?> Member</p>
                            
                            <div class="text-left mt-4">
                                <div class="detail-row">
                                    <span class="detail-label">Email</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($member['email']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Phone</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($member['phone']); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">Status</span>
                                    <span class="badge badge-<?php echo $member['sub_status'] == 'Active' ? 'success' : 'danger'; ?>">
                                        <?php echo $member['sub_status']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo $attendanceRate; ?>%</div>
                                    <div class="stat-label">Attendance Rate</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo getDaysRemaining($member['end_date']); ?></div>
                                    <div class="stat-label">Days Left in Plan</div>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">Subscription Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Plan Start Date</label>
                                        <p class="font-weight-bold"><?php echo formatDate($member['start_date']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Plan Expiry Date</label>
                                        <p class="font-weight-bold"><?php echo formatDate($member['end_date']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Membership Type</label>
                                        <p class="font-weight-bold"><?php echo htmlspecialchars($member['packagename']); ?></p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Gender</label>
                                        <p class="font-weight-bold"><?php echo htmlspecialchars($member['gender'] ?? 'Not Specified'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 mt-4">
                            <a href="workout_plans.php" class="btn btn-primary flex-fill mr-2">
                                <i class="fas fa-dumbbell mr-2"></i>Manage Workout
                            </a>
                            <a href="diet_plans.php" class="btn btn-info flex-fill">
                                <i class="fas fa-utensils mr-2"></i>Manage Diet
                            </a>
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
