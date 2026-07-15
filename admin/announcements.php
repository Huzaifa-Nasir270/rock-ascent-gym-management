<?php
/**
 * Manage Announcements - Admin Panel
 */

require_once('../config/functions.php');
requireAdmin();

// Handle announcement actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_announcement') {
        $title = sanitize($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $audience = sanitize($_POST['audience'] ?? 'Both');
        $start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : NULL;
        $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : NULL;
        $adminId = $_SESSION['admin_id'];

        $query = "INSERT INTO announcements (admin_id, title, content, audience, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, 'Published')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isssss", $adminId, $title, $content, $audience, $start_time, $end_time);

        if ($stmt->execute()) {
            redirect('announcements.php', 'Announcement published', 'success');
        }
    } elseif ($action === 'delete_announcement') {
        $announcementId = sanitize($_POST['announcement_id'] ?? '');
        $query = "DELETE FROM announcements WHERE announcement_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $announcementId);
        if ($stmt->execute()) {
            redirect('announcements.php', 'Announcement deleted', 'success');
        }
    } elseif ($action === 'edit_announcement') {
        $announcementId = sanitize($_POST['announcement_id'] ?? '');
        $title = sanitize($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $audience = sanitize($_POST['audience'] ?? 'Both');
        $start_time = !empty($_POST['start_time']) ? $_POST['start_time'] : NULL;
        $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : NULL;

        $query = "UPDATE announcements SET title = ?, content = ?, audience = ?, start_time = ?, end_time = ? WHERE announcement_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $title, $content, $audience, $start_time, $end_time, $announcementId);

        if ($stmt->execute()) {
            redirect('announcements.php', 'Announcement updated', 'success');
        }
    }
}

// Get all announcements
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Announcements - Admin</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/admin_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">📢 Announcements</h2>
                    <button class="btn btn-primary px-4" data-toggle="modal" data-target="#addAnnouncementModal" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-plus mr-2"></i> Create Announcement
                    </button>
                </div>

                <?php displayMessage(); ?>

                <div class="row">
                    <?php
                    if (!empty($announcements)) {
                        foreach ($announcements as $announcement) {
                            echo "
                            <div class='col-12 mb-4'>
                                <div class='card' style='background: rgba(15, 23, 42, 0.4); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px;'>
                                    <div class='card-body p-4'>
                                        <div class='d-flex justify-content-between align-items-start mb-3'>
                                            <h5 class='card-title font-weight-bold mb-0' style='color: #f8fafc; font-size: 1.3rem;'>" . htmlspecialchars($announcement['title']) . "</h5>
                                            <div>
                                                <span class='badge badge-primary mr-2'>" . htmlspecialchars($announcement['audience']) . "</span>
                                                <span class='badge badge-info'>Published</span>
                                            </div>
                                        </div>
                                        <p class='card-text' style='color: #cbd5e1; line-height: 1.7; font-size: 1.05rem;'>" . nl2br(htmlspecialchars(cleanLiteralNewlines($announcement['content']))) . "</p>
                                        <div class='mb-3'>
                                            " . ($announcement['start_time'] ? "<small class='text-info mr-3'><i class='fas fa-calendar-alt mr-1'></i> Start: " . date('Y-m-d H:i', strtotime($announcement['start_time'])) . "</small>" : "") . "
                                            " . ($announcement['end_time'] ? "<small class='text-warning'><i class='fas fa-calendar-times mr-1'></i> End: " . date('Y-m-d H:i', strtotime($announcement['end_time'])) . "</small>" : "") . "
                                        </div>
                                        <div class='d-flex justify-content-between align-items-center mt-4 pt-3' style='border-top: 1px solid rgba(255,255,255,0.05);'>
                                            <small class='text-muted'><i class='fas fa-clock mr-2'></i> Posted: " . formatDate($announcement['created_at']) . "</small>
                                            <div class='d-flex'>
                                                <button class='btn btn-sm btn-outline-primary mr-2 edit-btn' 
                                                        data-id='" . $announcement['announcement_id'] . "' 
                                                        data-title='" . htmlspecialchars($announcement['title'], ENT_QUOTES) . "' 
                                                        data-content='" . htmlspecialchars(cleanLiteralNewlines($announcement['content']), ENT_QUOTES) . "' 
                                                        data-audience='" . $announcement['audience'] . "'
                                                        data-start='" . ($announcement['start_time'] ? date('Y-m-d\TH:i', strtotime($announcement['start_time'])) : '') . "'
                                                        data-end='" . ($announcement['end_time'] ? date('Y-m-d\TH:i', strtotime($announcement['end_time'])) : '') . "'
                                                        style='border-radius: 10px; padding: 8px 20px; font-size: 0.85rem; border: 1px solid rgba(0, 123, 255, 0.3);'>
                                                    <i class='fas fa-edit mr-1'></i> Edit
                                                </button>
                                                <form method='POST' onsubmit='return confirm(\"Delete this announcement?\");'>
                                                    <input type='hidden' name='action' value='delete_announcement'>
                                                    <input type='hidden' name='announcement_id' value='" . $announcement['announcement_id'] . "'>
                                                    <button type='submit' class='btn btn-sm btn-outline-danger' style='border-radius: 10px; padding: 8px 20px; font-size: 0.85rem; border: 1px solid rgba(239, 68, 68, 0.3);'>
                                                        <i class='fas fa-trash mr-1'></i> Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ";
                        }
                    } else {
                        echo "<div class='col-12'><div class='p-5 text-center text-muted card'>No announcements published yet.</div></div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Announcement Modal -->
    <div class="modal fade" id="addAnnouncementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Announcement</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_announcement">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class='form-group'>
                            <label>Content</label>
                            <textarea class='form-control' name='content' rows='5' required></textarea>
                        </div>
                        <div class='form-group'>
                            <label>Target Audience</label>
                            <select name='audience' class='form-control' required>
                                <option value='Both'>Both (Users & Instructors)</option>
                                <option value='Users'>Users Only</option>
                                <option value='Instructors'>Instructors Only</option>
                            </select>
                        </div>
                        <div class='row'>
                            <div class='col-md-6'>
                                <div class='form-group'>
                                    <label>Start Time (Optional)</label>
                                    <input type='datetime-local' class='form-control' name='start_time'>
                                    <small class='text-muted'>Leave empty for immediate visibility.</small>
                                </div>
                            </div>
                            <div class='col-md-6'>
                                <div class='form-group'>
                                    <label>End Time (Optional)</label>
                                    <input type='datetime-local' class='form-control' name='end_time'>
                                    <small class='text-muted'>Leave empty for indefinite visibility.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Publish</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Announcement Modal -->
    <div class="modal fade" id="editAnnouncementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Announcement</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_announcement">
                        <input type="hidden" name="announcement_id" id="edit_id">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        <div class='form-group'>
                            <label>Content</label>
                            <textarea class='form-control' name='content' id="edit_content" rows='5' required></textarea>
                        </div>
                        <div class='form-group'>
                            <label>Target Audience</label>
                            <select name='audience' id="edit_audience" class='form-control' required>
                                <option value='Both'>Both (Users & Instructors)</option>
                                <option value='Users'>Users Only</option>
                                <option value='Instructors'>Instructors Only</option>
                            </select>
                        </div>
                        <div class='row'>
                            <div class='col-md-6'>
                                <div class='form-group'>
                                    <label>Start Time (Optional)</label>
                                    <input type='datetime-local' class='form-control' name='start_time' id="edit_start">
                                </div>
                            </div>
                            <div class='col-md-6'>
                                <div class='form-group'>
                                    <label>End Time (Optional)</label>
                                    <input type='datetime-local' class='form-control' name='end_time' id="edit_end">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Announcement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
    <script>
        $(document).ready(function() {
            $('.edit-btn').on('click', function() {
                const id = $(this).data('id');
                const title = $(this).data('title');
                const content = $(this).data('content');
                const audience = $(this).data('audience');
                const start = $(this).data('start');
                const end = $(this).data('end');

                $('#edit_id').val(id);
                $('#edit_title').val(title);
                $('#edit_content').val(content);
                $('#edit_audience').val(audience);
                $('#edit_start').val(start);
                $('#edit_end').val(end);

                $('#editAnnouncementModal').modal('show');
            });
        });
    </script>
</body>
</html>
