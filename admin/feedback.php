<?php
/**
 * Admin Feedback Management Page
 * Shows all feedback from users and instructors to the admin
 */

require_once('../config/functions.php');
requireAdmin();

$message = '';

// Handle status update
if (isset($_POST['update_status']) && isset($_POST['feedback_id'])) {
    $feedbackId = intval($_POST['feedback_id']);
    $newStatus = sanitize($_POST['status']);
    
    $query = "UPDATE feedback SET status = ? WHERE feedback_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $newStatus, $feedbackId);
    
    if ($stmt->execute()) {
        $message = 'Feedback status updated successfully!';
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$whereClause = '';
if ($filter === 'pending') {
    $whereClause = "WHERE status = 'pending'";
} elseif ($filter === 'reviewed') {
    $whereClause = "WHERE status = 'reviewed'";
} elseif ($filter === 'resolved') {
    $whereClause = "WHERE status = 'resolved'";
}

// Get feedback with pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

$totalQuery = "SELECT COUNT(*) as total FROM feedback $whereClause";
$total = $conn->query($totalQuery)->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);

$feedbackQuery = "SELECT * FROM feedback $whereClause ORDER BY created_at DESC LIMIT $perPage OFFSET $offset";
$allFeedback = $conn->query($feedbackQuery)->fetch_all(MYSQLI_ASSOC) ?? [];

// Get statistics
$stats = [
    'total' => $conn->query("SELECT COUNT(*) as count FROM feedback")->fetch_assoc()['count'],
    'pending' => $conn->query("SELECT COUNT(*) as count FROM feedback WHERE status = 'pending'")->fetch_assoc()['count'],
    'reviewed' => $conn->query("SELECT COUNT(*) as count FROM feedback WHERE status = 'reviewed'")->fetch_assoc()['count'],
    'resolved' => $conn->query("SELECT COUNT(*) as count FROM feedback WHERE status = 'resolved'")->fetch_assoc()['count'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Feedback Management - Admin</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/admin_sidebar.php'); ?>

            <div class="main-content">
                <h2 class="mb-4">💬 Feedback Management</h2>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">📊</div>
                            <div class="stat-number"><?php echo $stats['total']; ?></div>
                            <div class="stat-label">Total Feedback</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">⏳</div>
                            <div class="stat-number"><?php echo $stats['pending']; ?></div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">👁️</div>
                            <div class="stat-number"><?php echo $stats['reviewed']; ?></div>
                            <div class="stat-label">Reviewed</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon">✅</div>
                            <div class="stat-number"><?php echo $stats['resolved']; ?></div>
                            <div class="stat-label">Resolved</div>
                        </div>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <ul class="nav nav-pills" id="feedbackTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter === 'all' ? 'active' : ''; ?>" href="?filter=all">All (<?php echo $stats['total']; ?>)</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter === 'pending' ? 'active' : ''; ?>" href="?filter=pending">Pending (<?php echo $stats['pending']; ?>)</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter === 'reviewed' ? 'active' : ''; ?>" href="?filter=reviewed">Reviewed (<?php echo $stats['reviewed']; ?>)</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $filter === 'resolved' ? 'active' : ''; ?>" href="?filter=resolved">Resolved (<?php echo $stats['resolved']; ?>)</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>From</th>
                                        <th>Role</th>
                                        <th>Rating</th>
                                        <th>Subject</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($allFeedback)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">
                                                No feedback found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($allFeedback as $fb): ?>
                                            <tr>
                                                <td>#<?php echo $fb['feedback_id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($fb['user_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($fb['user_email']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-<?php echo $fb['user_role'] === 'instructor' ? 'warning' : 'info'; ?>">
                                                        <?php echo ucfirst($fb['user_role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $fb['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                    <?php endfor; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($fb['subject']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#viewModal<?php echo $fb['feedback_id']; ?>">
                                                        View Message
                                                    </button>
                                                </td>
                                                <td><?php echo formatDate($fb['created_at']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $fb['status'] === 'resolved' ? 'success' : ($fb['status'] === 'reviewed' ? 'info' : 'warning'); ?>">
                                                        <?php echo ucfirst($fb['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <form method="POST" action="feedback.php" class="d-inline">
                                                        <input type="hidden" name="feedback_id" value="<?php echo $fb['feedback_id']; ?>">
                                                        <input type="hidden" name="update_status" value="1">
                                                        <select name="status" class="status-select" onchange="this.form.submit()">
                                                            <option value="pending" <?php echo $fb['status'] === 'pending' ? 'selected' : ''; ?>>⏳ Pending</option>
                                                            <option value="reviewed" <?php echo $fb['status'] === 'reviewed' ? 'selected' : ''; ?>>👁️ Reviewed</option>
                                                            <option value="resolved" <?php echo $fb['status'] === 'resolved' ? 'selected' : ''; ?>>✅ Resolved</option>
                                                        </select>
                                                    </form>
                                                                           </tr>
                                         <?php endforeach; ?>
                                     <?php endif; ?>
                                 </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modals moved outside table for stability -->
                <?php foreach ($allFeedback as $fb): ?>
                    <div class="modal fade" id="viewModal<?php echo $fb['feedback_id']; ?>" tabindex="-1" role="dialog">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Feedback from <?php echo htmlspecialchars($fb['user_name']); ?></h5>
                                    <button type="button" class="close text-white" data-dismiss="modal">
                                        <span>&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($fb['user_email']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Role:</strong> <?php echo ucfirst($fb['user_role']); ?></p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p><strong>Rating:</strong> 
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?php echo $i <= $fb['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                                <?php endfor; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Date:</strong> <?php echo formatDate($fb['created_at']); ?></p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <p><strong>Subject:</strong> <?php echo htmlspecialchars($fb['subject']); ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p><strong>Message:</strong></p>
                                            <div class="p-3" style="background: rgba(15, 23, 42, 0.5); border-radius: 8px;">
                                                <?php echo nl2br(htmlspecialchars($fb['message'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?filter=<?php echo $filter; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
</body>
</html>