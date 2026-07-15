<?php
/**
 * Instructor Sidebar Include
 */
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar-nav">
    <div class="sidebar-header">
        <h5 style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 900; letter-spacing: -1px; text-transform: uppercase;">🏋️ Instructor</h5>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'members.php') ? 'active' : ''; ?>" href="members.php">
                <i class="fas fa-users"></i> My Members
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'workout_plans.php') ? 'active' : ''; ?>" href="workout_plans.php">
                <i class="fas fa-dumbbell"></i> Workout Plans
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'diet_plans.php') ? 'active' : ''; ?>" href="diet_plans.php">
                <i class="fas fa-utensils"></i> Diet Plans
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'manage_videos.php') ? 'active' : ''; ?>" href="manage_videos.php">
                <i class="fas fa-video"></i> Training Videos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>" href="attendance.php">
                <i class="fas fa-calendar-check"></i> Attendance
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'notifications.php') ? 'active' : ''; ?>" href="notifications.php">
                <i class="fas fa-bell"></i> Notifications
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'announcements.php') ? 'active' : ''; ?>" href="announcements.php">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'recommend_products.php') ? 'active' : ''; ?>" href="recommend_products.php">
                <i class="fas fa-star"></i> Recommend Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'feedback.php') ? 'active' : ''; ?>" href="feedback.php">
                <i class="fas fa-comment-dots"></i> Feedback
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'chat.php') ? 'active' : ''; ?>" href="chat.php">
                <i class="fas fa-comments text-primary"></i> Live Chat
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'ai_coach.php') ? 'active' : ''; ?>" href="ai_coach.php">
                <i class="fas fa-robot"></i> AI Coach Advisor
            </a>
        </li>
        <li class="nav-item"><hr style="border-color:rgba(255, 255, 255, 0.1)"></li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="../auth/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>
