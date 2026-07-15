<?php
/**
 * Instructor Announcements Page
 */

require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];

// Get all published announcements for instructors within valid time range
$announcements = $conn->query("SELECT a.*, admin.name as admin_name FROM announcements a LEFT JOIN admin ON a.admin_id = admin.admin_id WHERE a.status = 'Published' AND a.audience IN ('Instructors', 'Both') AND (a.start_time IS NULL OR a.start_time <= NOW()) AND (a.end_time IS NULL OR a.end_time >= NOW()) ORDER BY a.created_at DESC")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Announcements - Instructor</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Instructor Sidebar -->
            <?php include('../includes/instructor_sidebar.php'); ?>

            <div class="main-content">
                <h2 class="mb-4">📢 Announcements</h2>

                <?php displayMessage(); ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Latest Announcements from Gym Management</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        if (!empty($announcements)) {
                            foreach ($announcements as $announcement) {
                                echo "
                                <div class='card mb-3 border-left-primary'>
                                    <div class='card-body'>
                                        <div class='d-flex justify-content-between align-items-start'>
                                            <div>
                                                <h5 class='card-title'>
                                                    <i class='fas fa-bullhorn text-primary'></i> " . htmlspecialchars($announcement['title']) . "
                                                </h5>
                                                <p class='card-text' style='white-space: pre-wrap;'>" . nl2br(htmlspecialchars($announcement['content'])) . "</p>
                                            </div>
                                        </div>
                                        <div class='text-muted d-flex justify-content-between'>
                                            <small>Posted on: <strong>" . formatDate($announcement['created_at']) . "</strong></small>
                                            <small>By: <strong>" . htmlspecialchars($announcement['admin_name'] ?? 'Admin') . "</strong></small>
                                        </div>
                                    </div>
                                </div>
                                ";
                            }
                        } else {
                            echo "
                            <div class='text-center py-5'>
                                <i class='fas fa-inbox' style='font-size: 48px; color: #ccc; margin-bottom: 20px;'></i>
                                <p class='text-muted'>No announcements available at the moment.</p>
                                <p class='text-muted small'>Check back soon for updates from gym management!</p>
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
    <style>
        .card.border-left-primary {
            border-left: 4px solid #007bff !important;
        }
        .card {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transition: box-shadow 0.3s ease;
        }
    </style>
</body>
</html>
