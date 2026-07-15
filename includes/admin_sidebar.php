<?php
/**
 * Admin Sidebar Include
 */
?>

<div class="sidebar-nav">
    <div class="sidebar-header">
        <h5 style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 900; letter-spacing: -1px; text-transform: uppercase;">👨‍💼 Admin Panel</h5>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="edit_profile.php">
                <i class="fas fa-user-edit"></i> Edit Profile
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="users.php">
                <i class="fas fa-users"></i> Manage Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="instructors.php">
                <i class="fas fa-user-tie"></i> Manage Instructors
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="instructor_assignments.php">
                <i class="fas fa-handshake"></i> Instructor Assignments
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="admin_list.php">
                <i class="fas fa-crown"></i> Manage Admin Accounts
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="packages.php">
                <i class="fas fa-box"></i> Manage Packages
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="payments.php">
                <i class="fas fa-credit-card"></i> Payments
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="announcements.php">
                <i class="fas fa-bullhorn"></i> Announcements
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="reports.php">
                <i class="fas fa-file-alt"></i> Reports
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="feedback.php">
                <i class="fas fa-comment-dots"></i> Feedback
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="reset_passwords.php">
                <i class="fas fa-key"></i> Reset Passwords
            </a>
        </li>
        <li class="nav-item"><hr style="border-color:rgba(245,158,11,0.3)"></li>
        <li class="nav-item">
            <div style="padding:8px 16px;font-size:11px;text-transform:uppercase;letter-spacing:0.1em;color:#f59e0b;font-weight:700;">🛒 Shop Management</div>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="shop_intelligence.php">
                <i class="fas fa-chart-line"></i> Shop Intelligence
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="shop_categories.php">
                <i class="fas fa-tags"></i> Shop Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="shop_products.php">
                <i class="fas fa-box-open"></i> Shop Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="shop_orders.php">
                <i class="fas fa-shopping-bag"></i> Shop Orders
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="product_reviews.php">
                <i class="fas fa-star"></i> Product Reviews
            </a>
        </li>
        <li class="nav-item"><hr></li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="../auth/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>
</div>
