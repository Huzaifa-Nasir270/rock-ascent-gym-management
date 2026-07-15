<?php
/**
 * User Announcements Page
 */

require_once('../config/functions.php');
requireUser();

$userId = $_SESSION['user_id'];

// Get all published announcements for users
$announcements = $conn->query("SELECT a.*, admin.name as admin_name FROM announcements a LEFT JOIN admin ON a.admin_id = admin.admin_id WHERE a.status = 'Published' AND a.audience IN ('Users', 'Both') ORDER BY a.created_at DESC")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Announcements - Member</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Unified Sidebar -->
            <?php include('../includes/user_sidebar.php'); ?>

            <div class="main-content">
                <h2 class="mb-4">📢 Announcements</h2>

                <?php displayMessage(); ?>

                <div class="row">
                    <div class="col-12">
                        <?php
                        if (!empty($announcements)) {
                            foreach ($announcements as $ann) {
                                $content = cleanLiteralNewlines($ann['content'] ?? $ann['message'] ?? '');
                                echo "
                                <div class='card mb-4 fade-in-up announcement-card-premium'>
                                    <div class='card-body p-0'>
                                        <div class='d-flex'>
                                            <div class='accent-bar'></div>
                                            <div class='p-4 w-100'>
                                                <div class='d-flex justify-content-between align-items-start mb-3'>
                                                    <div>
                                                        <h4 class='ann-title mb-1'>" . htmlspecialchars($ann['title']) . "</h4>
                                                        <div class='ann-meta'>
                                                            <span><i class='far fa-calendar-alt mr-1'></i> " . formatDate($ann['created_at']) . "</span>
                                                            <span class='mx-2 text-muted'>•</span> 
                                                            <span><i class='far fa-user mr-1'></i> Management</span>
                                                        </div>
                                                    </div>
                                                    <div class='official-badge'>
                                                        <i class='fas fa-shield-alt mr-1'></i> OFFICIAL
                                                    </div>
                                                </div>
                                                <div class='ann-content-wrapper'>
                                                    <p class='ann-text'>" . nl2br(htmlspecialchars($content)) . "</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                ";
                            }
                        } else {
                            echo "
                            <div class='text-center py-5'>
                                <i class='fas fa-inbox fa-3x text-muted mb-3 opacity-30'></i>
                                <p class='text-muted'>No announcements available at the moment.</p>
                            </div>
                            ";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
</body>
</html>
