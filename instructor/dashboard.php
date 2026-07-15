<?php
/**
 * Instructor Dashboard
 */

require_once('../config/functions.php');
requireInstructor();

try {
    $instructorId = $_SESSION['instructor_id'];

    // Get statistics from the new assignment table
    $totalAssignedMembers = $conn->query("SELECT COUNT(*) as count FROM user_instructor_assignments WHERE instructor_id = $instructorId AND status = 'Active'")->fetch_assoc()['count'] ?? 0;
    $totalWorkoutPlans = $conn->query("SELECT COUNT(*) as count FROM workout_plans WHERE instructor_id = $instructorId")->fetch_assoc()['count'] ?? 0;
    $totalDietPlans = $conn->query("SELECT COUNT(*) as count FROM diet_plans WHERE instructor_id = $instructorId")->fetch_assoc()['count'] ?? 0;
    $todayAttendance = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE check_in_date = CURDATE() AND user_id IN (SELECT user_id FROM user_instructor_assignments WHERE instructor_id = $instructorId AND status = 'Active')")->fetch_assoc()['count'] ?? 0;

    // Get instructor info
    $instructor = getInstructorDetails($instructorId);
    
    // Get recent announcements for instructors within valid time range
    $recentAnnouncements = $conn->query("SELECT * FROM announcements WHERE status = 'Published' AND audience IN ('Instructors', 'Both') AND (start_time IS NULL OR start_time <= NOW()) AND (end_time IS NULL OR end_time >= NOW()) ORDER BY created_at DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    ob_end_clean();
    redirect('../auth/login.php', 'Error loading dashboard: ' . $e->getMessage(), 'danger');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Instructor Dashboard</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Instructor Sidebar -->
            <?php include('../includes/instructor_sidebar.php'); ?>

            <div class="main-content">
                <!-- Premium Welcome Header -->
                <div class="welcome-section mb-5 fade-in-up">
                    <h1 class="mb-2">👋 Welcome back, <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo htmlspecialchars($instructor['name']); ?></span></h1>
                    <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">You have <span class="text-warning"><?php echo $totalAssignedMembers; ?> active members</span> under your supervision today. Ready to crush some goals?</p>
                </div>

                <?php displayMessage(); ?>

                <!-- Stats -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">👥</div>
                            <div class="stat-number"><?php echo $totalAssignedMembers; ?></div>
                            <div class="stat-label">Assigned Members</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">💪</div>
                            <div class="stat-number"><?php echo $totalWorkoutPlans; ?></div>
                            <div class="stat-label">Workout Plans</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">🥗</div>
                            <div class="stat-number"><?php echo $totalDietPlans; ?></div>
                            <div class="stat-label">Diet Plans</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">📍</div>
                            <div class="stat-number"><?php echo $todayAttendance; ?></div>
                            <div class="stat-label">Today's Check-ins</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="members.php" class="btn btn-primary">View Members</a>
                        <a href="workout_plans.php" class="btn btn-primary">Create Workout Plan</a>
                        <a href="diet_plans.php" class="btn btn-primary">Create Diet Plan</a>
                        <a href="attendance.php" class="btn btn-primary">Mark Attendance</a>
                    </div>
                </div>

                <!-- Edit Profile Button -->
                <div class="text-right mb-4">
                    <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
                </div>

                <!-- Recent Announcements -->
                <?php if (!empty($recentAnnouncements)) { ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">📢 Latest Announcements</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach (array_slice($recentAnnouncements, 0, 2) as $announcement) { ?>
                        <div class="mb-3 p-4" style="background: rgba(255, 255, 255, 0.03); border-left: 4px solid var(--primary-color); border-radius: 12px; border-top: 1px solid rgba(255,255,255,0.05); border-right: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05); transition: all 0.3s ease;">
                            <h6 class="mb-2" style="font-weight: 700; color: white;"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                            <p class="mb-2 text-muted small" style="white-space: pre-wrap; font-size: 0.95rem;"><?php echo nl2br(htmlspecialchars(substr($announcement['content'], 0, 150))); ?><?php echo strlen($announcement['content']) > 150 ? '...' : ''; ?></p>
                            <small style="color: var(--primary-color); font-weight: 600;">Posted: <?php echo formatDate($announcement['created_at']); ?></small>
                        </div>
                        <?php } ?>
                        <a href="announcements.php" class="btn btn-sm btn-outline-primary">View All Announcements</a>
                    </div>
                </div>
                <?php } ?>

                <!-- Profile Info -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Your Profile</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($instructor['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($instructor['email']); ?></p>
                        <p><strong>Specialization:</strong> <?php echo htmlspecialchars($instructor['specialization']); ?></p>
                        <p><strong>Status:</strong> <span class="badge badge-success"><?php echo $instructor['status']; ?></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
</body>
</html>
