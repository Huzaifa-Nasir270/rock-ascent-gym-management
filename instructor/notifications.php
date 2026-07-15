<?php
/**
 * Instructor - Notifications
 */

require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'send_notification') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if ($userId > 0 && !empty($title) && !empty($message)) {
            $query = "INSERT INTO notifications (user_id, sender_id, sender_type, title, message) VALUES (?, ?, 'Instructor', ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isss", $userId, $instructorId, $title, $message);
            if ($stmt->execute()) redirect('notifications.php', 'Notification sent successfully!', 'success');
        }
    }

    if ($action === 'edit_notification') {
        $notifId = (int)($_POST['notification_id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if ($notifId > 0 && !empty($title) && !empty($message)) {
            $stmt = $conn->prepare("UPDATE notifications SET title = ?, message = ? WHERE notification_id = ? AND sender_id = ? AND sender_type = 'Instructor'");
            $stmt->bind_param("ssii", $title, $message, $notifId, $instructorId);
            if ($stmt->execute()) redirect('notifications.php', 'Notification updated!', 'success');
        }
    }

    if ($action === 'delete_notification') {
        $notifId = (int)($_POST['notification_id'] ?? 0);
        if ($notifId > 0) {
            $stmt = $conn->prepare("DELETE FROM notifications WHERE notification_id = ? AND sender_id = ? AND sender_type = 'Instructor'");
            $stmt->bind_param("ii", $notifId, $instructorId);
            if ($stmt->execute()) redirect('notifications.php', 'Notification deleted!', 'success');
        }
    }
}

// Get assigned members
$members = $conn->query("
    SELECT u.user_id, u.name FROM users u 
    INNER JOIN user_instructor_assignments uia ON u.user_id = uia.user_id 
    WHERE uia.instructor_id = $instructorId AND uia.status = 'Active'
    ORDER BY u.name
")->fetch_all(MYSQLI_ASSOC);

// Get Sent Notifications History
$sentNotifications = $conn->query("
    SELECT n.*, u.name as member_name 
    FROM notifications n 
    JOIN users u ON n.user_id = u.user_id 
    WHERE n.sender_id = $instructorId AND n.sender_type = 'Instructor' 
    ORDER BY n.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Notifications - Instructor</title>
    <?php include('../includes/head.php'); ?>
    <style>
        .notif-history-card { background: rgba(15, 23, 42, 0.4); border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); overflow: hidden; }
        .table-premium { color: #e2e8f0; }
        .table-premium thead { background: rgba(255,255,255,0.05); }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .status-seen { background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.3); }
        .status-unseen { background: rgba(100, 116, 139, 0.1); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2); }
        .btn-action { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; margin: 0 2px; border: none; }
        .btn-edit { background: rgba(59, 130, 246, 0.1); color: #60a5fa; }
        .btn-edit:hover { background: #3b82f6; color: white; }
        .btn-delete { background: rgba(239, 68, 68, 0.1); color: #f87171; }
        .btn-delete:hover { background: #ef4444; color: white; }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    <div class="container-fluid px-4"><div class="dashboard-container">
        <?php include('../includes/instructor_sidebar.php'); ?>
        <div class="main-content">
            
            <!-- Premium Header -->
            <div class="welcome-section mb-5 fade-in-up">
                <h1 class="mb-2">📢 Member <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Broadcasts</span></h1>
                <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Send announcements, reminders, or personal updates to your members.</p>
            </div>

            <?php displayMessage(); ?>

            <div class="row">
                <div class="col-lg-5 mb-4">
                    <div class="card glass-card">
                        <div class="card-header bg-transparent border-0 pt-4 px-4">
                            <h5 class="mb-0 font-weight-bold"><i class="fas fa-paper-plane mr-2 text-warning"></i>New Broadcast</h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">
                                <input type="hidden" name="action" value="send_notification">
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted">RECIPIENT</label>
                                    <select class="form-control" name="user_id" required>
                                        <option value="">Select Member</option>
                                        <?php foreach ($members as $m): ?>
                                            <option value="<?php echo $m['user_id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted">TITLE</label>
                                    <input type="text" class="form-control" name="title" id="notif_title" placeholder="What is this about?" required>
                                </div>
                                <div class="form-group">
                                    <label class="small font-weight-bold text-muted">MESSAGE</label>
                                    <textarea class="form-control" name="message" id="notif_message" rows="4" placeholder="Type your message here..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold" style="border-radius:12px;">Send Notification</button>
                            </form>
                        </div>
                    </div>

                    <!-- Templates Card -->
                    <div class="card glass-card mt-4">
                        <div class="card-body p-4">
                            <h6 class="font-weight-bold mb-3 small text-muted">QUICK TEMPLATES</h6>
                            <div class="list-group list-group-flush">
                                <a href="javascript:void(0)" onclick="useTemplate('New Workout Plan', 'I have updated your workout routine. Please review it before your next session.')" class="list-group-item list-group-item-action bg-transparent border-0 px-0 py-2 text-light small">
                                    <i class="fas fa-dumbbell mr-2 text-warning"></i> New Workout Routine
                                </a>
                                <a href="javascript:void(0)" onclick="useTemplate('Diet Update', 'Your nutrition plan has been optimized. Let\'s focus on these adjustments this week.')" class="list-group-item list-group-item-action bg-transparent border-0 px-0 py-2 text-light small">
                                    <i class="fas fa-utensils mr-2 text-success"></i> Optimized Diet Plan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="notif-history-card">
                        <div class="p-4 border-bottom border-light">
                            <h5 class="mb-0 font-weight-bold">History & Engagement</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-premium mb-0">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Content</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($sentNotifications)): ?>
                                        <tr><td colspan="4" class="text-center py-5 text-muted">No notifications sent yet.</td></tr>
                                    <?php else: foreach ($sentNotifications as $n): ?>
                                        <tr>
                                            <td class="small font-weight-bold"><?php echo htmlspecialchars($n['member_name']); ?></td>
                                            <td>
                                                <div class="font-weight-bold small"><?php echo htmlspecialchars($n['title']); ?></div>
                                                <div class="text-muted x-small" style="font-size: 0.7rem;"><?php echo date('M d, H:i', strtotime($n['created_at'])); ?></div>
                                            </td>
                                            <td>
                                                <?php if ($n['is_read'] === 'Yes'): ?>
                                                    <span class="status-badge status-seen" title="Seen at: <?php echo isset($n['read_at']) ? $n['read_at'] : 'Unknown'; ?>">
                                                        <i class="fas fa-check-double mr-1"></i> Seen
                                                    </span>
                                                <?php else: ?>
                                                    <span class="status-badge status-unseen">Unseen</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn-action btn-edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($n)); ?>)" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this notification?')">
                                                    <input type="hidden" name="action" value="delete_notification">
                                                    <input type="hidden" name="notification_id" value="<?php echo $n['notification_id']; ?>">
                                                    <button type="submit" class="btn-action btn-delete" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div></div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content glass-card">
                <div class="modal-header border-light">
                    <h5 class="modal-title font-weight-bold">Edit Broadcast</h5>
                    <button type="button" class="close text-light" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <div class="modal-body p-4">
                        <input type="hidden" name="action" value="edit_notification">
                        <input type="hidden" name="notification_id" id="edit_id">
                        <div class="form-group">
                            <label class="small font-weight-bold text-muted">TITLE</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold text-muted">MESSAGE</label>
                            <textarea class="form-control" name="message" id="edit_message" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4">
                        <button type="button" class="btn btn-link text-muted" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Update Broadcast</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>
    <?php include('../includes/scripts.php'); ?>
    <script>
        function useTemplate(title, message) {
            $('#notif_title').val(title);
            $('#notif_message').val(message);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        function openEditModal(notif) {
            $('#edit_id').val(notif.notification_id);
            $('#edit_title').val(notif.title);
            $('#edit_message').val(notif.message);
            $('#editModal').modal('show');
        }
    </script>
</body>
</html>
