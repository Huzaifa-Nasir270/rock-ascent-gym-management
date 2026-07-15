<?php
require_once('../config/functions.php');
requireUser();

$userId = $_SESSION['user_id'];
$attendance = $conn->query("SELECT * FROM attendance WHERE user_id = $userId ORDER BY check_in_date DESC LIMIT 30")->fetch_all(MYSQLI_ASSOC);
$currentMonth = date('Y-m');
$thisMonthAttendance = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE user_id = $userId AND check_in_date LIKE '$currentMonth%' AND status = 'Present'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Attendance - Member</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Unified Sidebar -->
            <?php include('../includes/user_sidebar.php'); ?>

            <div class="main-content">
                <!-- Premium Page Header -->
                <div class="welcome-section mb-5 fade-in-up">
                    <h1 class="mb-2">📍 My <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Attendance</span></h1>
                    <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Track your gym visits and consistency.</p>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Attendance Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="stat-card" style="width: 200px; margin-bottom: 20px;">
                            <div class="stat-icon">📊</div>
                            <div class="stat-number"><?php echo $thisMonthAttendance; ?></div>
                            <div class="stat-label">This Month</div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Attendance</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Check-in Time</th>
                                        <th>Status</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($attendance)) {
                                        foreach ($attendance as $att) {
                                            $badge = $att['status'] === 'Present' ? 'badge-success' : 
                                                    ($att['status'] === 'Absent' ? 'badge-danger' : 'badge-warning');
                                            echo "<tr>
                                                <td>" . formatDate($att['check_in_date']) . "</td>
                                                <td>" . ($att['check_in_time'] ?? '--') . "</td>
                                                <td><span class='badge $badge'>" . $att['status'] . "</span></td>
                                                <td>" . htmlspecialchars($att['notes'] ?? '') . "</td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center text-muted'>No attendance records yet</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
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
