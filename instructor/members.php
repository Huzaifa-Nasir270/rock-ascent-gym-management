<?php
/**
 * Instructor - Manage Members
 */

require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];

// Handle member removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_member') {
    $userIdToRemove = (int)($_POST['user_id'] ?? 0);
    if ($userIdToRemove > 0) {
        $stmt = $conn->prepare("UPDATE user_instructor_assignments SET status = 'Inactive' WHERE user_id = ? AND instructor_id = ?");
        $stmt->bind_param("ii", $userIdToRemove, $instructorId);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Member removed successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Failed to remove member.";
            $_SESSION['message_type'] = "danger";
        }
    }
}

// Get assigned members from the new assignment table
$members = $conn->query("
    SELECT u.*, 
           COALESCE(s.subscription_id, 0) as subscription_id,
           COALESCE(NULLIF(s.start_date, ''), a.start_date, '') as start_date, 
           COALESCE(NULLIF(s.end_date, ''), a.end_date, '') as end_date, 
           COALESCE(p.name, 'No Package') as packagename, 
           COALESCE(p.price, 0) as price,
           a.assigned_at
    FROM user_instructor_assignments a
    INNER JOIN users u ON a.user_id = u.user_id
    LEFT JOIN subscriptions s ON s.user_id = u.user_id AND s.status = 'Active'
    LEFT JOIN packages p ON s.package_id = p.package_id
    WHERE a.instructor_id = $instructorId AND a.status = 'Active'
    ORDER BY a.assigned_at DESC
")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Members - Instructor</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/instructor_sidebar.php'); ?>

            <div class="main-content">
                <h2 class="mb-4">👥 My Members</h2>

                <?php displayMessage(); ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Assigned Members (<?php echo count($members); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Package</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Days Left</th>
                                        <th style='width: 220px;'>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($members)) {
                                        foreach ($members as $member) {
                                            $daysLeft = getDaysRemaining($member['end_date']);
                                            $daysColor = $daysLeft > 7 ? 'success' : ($daysLeft > 0 ? 'warning' : 'danger');
                                            $daysDisplay = ($member['subscription_id'] == 0) ? "<span class='badge badge-secondary'>No Active Plan</span>" : "<span class='badge badge-$daysColor'>$daysLeft Days Left</span>";
                                            
                                            echo "
                                            <tr>
                                                <td>" . htmlspecialchars($member['name']) . "</td>
                                                <td>" . htmlspecialchars($member['email']) . "</td>
                                                <td>" . htmlspecialchars($member['phone']) . "</td>
                                                <td>" . htmlspecialchars($member['packagename']) . "</td>
                                                <td>" . formatDate($member['start_date']) . "</td>
                                                <td>" . formatDate($member['end_date']) . "</td>
                                                <td>$daysDisplay</td>
                                                <td style='white-space: nowrap;'>
                                                    <div class='d-flex align-items-center' style='gap: 8px;'>
                                                        <a href='member_details.php?id=" . $member['user_id'] . "' class='btn btn-sm' style='background: #10b981; color: white; border-radius: 12px; padding: 8px 18px; font-weight: 800; font-size: 11px; letter-spacing: 1px; border: none;'>
                                                            <i class='fas fa-eye mr-1'></i> VIEW
                                                        </a>
                                                        <form method='POST' onsubmit='return confirm(\"Are you sure you want to remove this member from your list?\");' style='margin:0;'>
                                                            <input type='hidden' name='action' value='remove_member'>
                                                            <input type='hidden' name='user_id' value='" . $member['user_id'] . "'>
                                                            <button type='submit' class='btn btn-sm' style='background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 8px 18px; font-weight: 800; font-size: 11px; letter-spacing: 1px;'>
                                                                <i class='fas fa-user-minus mr-1'></i> REMOVE
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            ";
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center text-muted'>No members assigned yet</td></tr>";
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
