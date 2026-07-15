<?php
/**
 * Header Include File
 * Displays navigation header for different roles
 */

// functions.php is already included by the calling page
$role = getUserRole();
$unreadCount = 0;

if ($role === 'user' && isset($_SESSION['user_id'])) {
    $unreadCount = getUnreadNotificationCount($_SESSION['user_id']);
} elseif ($role === 'instructor' && isset($_SESSION['instructor_id'])) {
    $unreadCount = getUnreadNotificationCount($_SESSION['instructor_id']);
}

$depth = count(explode('/', trim($_SERVER['PHP_SELF'], '/'))) - ( (strpos($_SERVER['PHP_SELF'], 'gym_management') !== false) ? 2 : 1 );
$root = str_repeat('../', max(0, $depth));

$navProfileImage = '';
if ($role === 'user' && isset($_SESSION['user_id'])) {
    $r = $conn->query("SELECT profile_image FROM users WHERE user_id=".$_SESSION['user_id']);
    if ($r && $row = $r->fetch_assoc()) { if (!empty($row['profile_image'])) $navProfileImage = $root . 'assets/images/profiles/' . $row['profile_image']; }
} elseif ($role === 'instructor' && isset($_SESSION['instructor_id'])) {
    $r = $conn->query("SELECT profile_image FROM instructors WHERE instructor_id=".$_SESSION['instructor_id']);
    if ($r && $row = $r->fetch_assoc()) { if (!empty($row['profile_image'])) $navProfileImage = $root . 'assets/images/profiles/' . $row['profile_image']; }
} elseif ($role === 'admin' && isset($_SESSION['admin_id'])) {
    $r = $conn->query("SELECT profile_image FROM admin WHERE admin_id=".$_SESSION['admin_id']);
    if ($r && $row = $r->fetch_assoc()) { if (!empty($row['profile_image'])) $navProfileImage = $root . 'assets/images/profiles/' . $row['profile_image']; }
}
?>
<style>
    .glowing-brand {
        text-shadow: 0 0 10px rgba(245, 158, 11, 0.5), 0 0 20px rgba(245, 158, 11, 0.3);
        letter-spacing: 1px;
        transition: all 0.3s ease;
    }
    .glowing-brand:hover {
        text-shadow: 0 0 20px rgba(245, 158, 11, 0.8), 0 0 30px rgba(236, 72, 153, 0.5);
        transform: scale(1.05);
    }
    .nav-avatar {
        width: 35px;
        height: 35px;
        background: var(--primary-gradient);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 15px rgba(236, 72, 153, 0.3);
        border: 2px solid rgba(255,255,255,0.2);
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid px-4">
        <a class="navbar-brand font-weight-bold glowing-brand" href="#">🏋️ Project Rock Ascent</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto align-items-center">
                <?php
                if ($role === 'admin') {
                    echo '<li class="nav-item"><a class="nav-link" href="../admin/dashboard.php"><i class="fas fa-chart-line mr-1"></i>Dashboard</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="../admin/users.php"><i class="fas fa-users mr-1"></i>Users</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="../admin/shop_orders.php"><i class="fas fa-shopping-cart mr-1"></i>Orders</a></li>';
                } elseif ($role === 'instructor') {
                    echo '<li class="nav-item"><a class="nav-link" href="../instructor/dashboard.php"><i class="fas fa-home mr-1"></i>Dashboard</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="../instructor/members.php"><i class="fas fa-users mr-1"></i>Members</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="../instructor/notifications.php"><i class="fas fa-bell mr-1"></i>Notifications <span class="badge badge-warning">' . $unreadCount . '</span></a></li>';
                } elseif ($role === 'user') {
                    echo '<li class="nav-item"><a class="nav-link" href="../user/dashboard.php"><i class="fas fa-home mr-1"></i>Dashboard</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="../user/shop.php"><i class="fas fa-store mr-1"></i>Shop</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="../user/cart.php"><i class="fas fa-shopping-cart mr-1"></i>Cart</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="../user/notifications.php"><i class="fas fa-bell mr-1"></i>Notifications <span class="badge badge-warning">' . $unreadCount . '</span></a></li>';
                }
                ?>
                <li class="nav-item dropdown ml-2">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="userDropdown">
                        <span class="nav-avatar mr-2" style="overflow:hidden;">
                            <?php
                            if ($navProfileImage) {
                                echo '<img src="'.htmlspecialchars($navProfileImage).'" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
                            } else {
                                if ($role === 'admin') echo '<i class="fas fa-user-shield"></i>';
                                elseif ($role === 'instructor') echo '<i class="fas fa-user-tie"></i>';
                                else echo '<i class="fas fa-user"></i>';
                            }
                            ?>
                        </span>
                        <?php
                        if ($role === 'admin') echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
                        elseif ($role === 'instructor') echo htmlspecialchars($_SESSION['instructor_name'] ?? 'Instructor');
                        elseif ($role === 'user') echo htmlspecialchars($_SESSION['user_name'] ?? 'User');
                        ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow-lg" aria-labelledby="userDropdown">
                        <?php 
                        if ($role === 'user') { ?>
                            <a class="dropdown-item" href="<?php echo $root; ?>user/profile.php"><i class="fas fa-user-edit mr-2"></i>My Profile</a>
                            <a class="dropdown-item" href="<?php echo $root; ?>user/orders.php"><i class="fas fa-box mr-2"></i>My Orders</a>
                            <div class="dropdown-divider"></div>
                        <?php } elseif ($role === 'admin') { ?>
                            <a class="dropdown-item" href="<?php echo $root; ?>admin/dashboard.php"><i class="fas fa-tachometer-alt mr-2"></i>Dashboard</a>
                            <div class="dropdown-divider"></div>
                        <?php } ?>
                        <a class="dropdown-item text-danger font-weight-bold" href="<?php echo $root; ?>auth/logout.php"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

