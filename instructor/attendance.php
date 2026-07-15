<?php
/**
 * Instructor - Mark Attendance
 */

require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];

// Handle attendance marking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = sanitize($_POST['user_id'] ?? '');
    $status = sanitize($_POST['status'] ?? '');
    $notes = sanitize($_POST['notes'] ?? '');

    // Check if attendance already exists for today
    $today = date('Y-m-d');
    $checkQuery = "SELECT attendance_id FROM attendance WHERE user_id = ? AND check_in_date = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("is", $userId, $today);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        $query = "UPDATE attendance SET status = ?, notes = ? WHERE attendance_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $status, $notes, $existing['attendance_id']);
    } else {
        $time = date('H:i:s');
        $query = "INSERT INTO attendance (user_id, check_in_date, check_in_time, status, notes) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issss", $userId, $today, $time, $status, $notes);
    }

    if ($stmt->execute()) {
        redirect('attendance.php', 'Attendance marked', 'success');
    }
}

// Get assigned members from the new assignment table
$members = $conn->query("
    SELECT u.user_id, u.name FROM users u 
    INNER JOIN user_instructor_assignments a ON u.user_id = a.user_id 
    WHERE a.instructor_id = $instructorId AND a.status = 'Active'
    GROUP BY u.user_id
")->fetch_all(MYSQLI_ASSOC);

// Get today's attendance
$today = date('Y-m-d');
$todayAttendance = $conn->query("
    SELECT a.*, u.name 
    FROM attendance a 
    INNER JOIN users u ON a.user_id = u.user_id 
    WHERE a.check_in_date = '$today' AND u.user_id IN (
        SELECT user_id FROM user_instructor_assignments WHERE instructor_id = $instructorId AND status = 'Active'
    )
    ORDER BY a.check_in_time DESC
")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Mark Attendance - Instructor</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Instructor Sidebar -->
            <?php include('../includes/instructor_sidebar.php'); ?>

            <div class="main-content">
                <h2 class="mb-4">📍 Mark Attendance</h2>

                <?php displayMessage(); ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Mark Check-In</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="form-group">
                                        <label>Select Member</label>
                                        <select class="form-control" name="user_id" required>
                                            <option value="">-- Select Member --</option>
                                            <?php
                                            foreach ($members as $member) {
                                                echo "<option value='" . $member['user_id'] . "'>" . htmlspecialchars($member['name']) . "</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select class="form-control" name="status" required>
                                            <option value="Present">Present</option>
                                            <option value="Absent">Absent</option>
                                            <option value="On Leave">On Leave</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea class="form-control" name="notes" rows="3" placeholder="Optional notes..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">Mark Attendance</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Today's Check-ins</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Member</th>
                                                <th>Status</th>
                                                <th>Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if (!empty($todayAttendance)) {
                                                foreach ($todayAttendance as $att) {
                                                    $badge = $att['status'] === 'Present' ? 'badge-success' : 
                                                            ($att['status'] === 'Absent' ? 'badge-danger' : 'badge-warning');
                                                    echo "
                                                    <tr>
                                                        <td>" . htmlspecialchars($att['name']) . "</td>
                                                        <td><span class='badge $badge'>" . $att['status'] . "</span></td>
                                                        <td>" . ($att['check_in_time'] ?? '--') . "</td>
                                                    </tr>
                                                    ";
                                                }
                                            } else {
                                                echo "<tr><td colspan='3' class='text-center text-muted'>No check-ins yet</td></tr>";
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
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
</body>
</html>
