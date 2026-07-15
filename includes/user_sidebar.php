<?php
/**
 * User/Member Sidebar Include
 */
$current_page = basename($_SERVER['PHP_SELF']);

// Get unread notification count
$unread_notif = function_exists('getUnreadNotificationCount') ? getUnreadNotificationCount($_SESSION['user_id']) : 0;
?>

<div class="sidebar-nav">
    <div class="sidebar-header">
        <h5 style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 900; letter-spacing: -1px; text-transform: uppercase;">🏋️ Member</h5>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>" href="profile.php">
                <i class="fas fa-user"></i> My Profile
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'packages.php') ? 'active' : ''; ?>" href="packages.php">
                <i class="fas fa-box"></i> Packages
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'my_package_details.php') ? 'active' : ''; ?>" href="my_package_details.php">
                <i class="fas fa-award text-warning"></i> My Active Package
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
            <a class="nav-link <?php echo ($current_page == 'smart_recommendations.php') ? 'active' : ''; ?>" href="smart_recommendations.php">
                <i class="fas fa-brain text-info"></i> Smart Plan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'attendance.php') ? 'active' : ''; ?>" href="attendance.php">
                <i class="fas fa-calendar-check"></i> Attendance
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'payments.php') ? 'active' : ''; ?>" href="payments.php">
                <i class="fas fa-credit-card"></i> Payments
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'notifications.php') ? 'active' : ''; ?>" href="notifications.php">
                <i class="fas fa-bell"></i> Notifications <?php if($unread_notif > 0) echo "<span class='badge badge-warning ml-1'>$unread_notif</span>"; ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'announcements.php') ? 'active' : ''; ?>" href="announcements.php">
                <i class="fas fa-bullhorn"></i> Announcements
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
        <li class="nav-item"><hr style="border-color:rgba(255, 255, 255, 0.1)"></li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'shop.php') ? 'active' : ''; ?>" href="shop.php">
                <i class="fas fa-store"></i> Gym Shop
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'orders.php') ? 'active' : ''; ?>" href="orders.php">
                <i class="fas fa-box-open"></i> My Orders
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'about.php') ? 'active' : ''; ?>" href="about.php">
                <i class="fas fa-info-circle"></i> About Us
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>" href="contact.php">
                <i class="fas fa-phone-alt"></i> Contact Us
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
