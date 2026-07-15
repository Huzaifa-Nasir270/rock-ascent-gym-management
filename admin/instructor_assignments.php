<?php
/**
 * Manage Instructor-User Assignments - Admin Panel
 */

require_once('../config/functions.php');
requireAdmin();

// Handle assignment actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_assignment') {
        $assignmentId = sanitize($_POST['assignment_id'] ?? '');
        
        // Get user_id before deleting to clear their assigned_instructor_id
        $getUserId = "SELECT user_id FROM user_instructor_assignments WHERE assignment_id = ?";
        $stmt = $conn->prepare($getUserId);
        $stmt->bind_param("i", $assignmentId);
        $stmt->execute();
        $userResult = $stmt->get_result()->fetch_assoc();
        
        if ($userResult) {
            $userId = $userResult['user_id'];
            
            // Delete assignment
            $delQuery = "DELETE FROM user_instructor_assignments WHERE assignment_id = ?";
            $delStmt = $conn->prepare($delQuery);
            $delStmt->bind_param("i", $assignmentId);
            
            if ($delStmt->execute()) {
                // Clear the user's assigned_instructor_id
                $clearUser = "UPDATE users SET assigned_instructor_id = NULL WHERE user_id = ?";
                $clearStmt = $conn->prepare($clearUser);
                $clearStmt->bind_param("i", $userId);
                $clearStmt->execute();
                
                redirect('instructor_assignments.php', 'Assignment removed successfully', 'success');
            }
        }
    } elseif ($action === 'toggle_status') {
        $assignmentId = sanitize($_POST['assignment_id'] ?? '');
        $newStatus = sanitize($_POST['status'] ?? '');
        $query = "UPDATE user_instructor_assignments SET status = ? WHERE assignment_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $newStatus, $assignmentId);
        if ($stmt->execute()) {
            redirect('instructor_assignments.php', 'Assignment status updated', 'success');
        }
    } elseif ($action === 'new_assignment') {
        $userId = sanitize($_POST['user_id'] ?? '');
        $instructorId = sanitize($_POST['instructor_id'] ?? '');
        
        // Check if user is already assigned to ANY active instructor
        $checkAnyActive = "SELECT a.*, i.name as instructor_name 
                          FROM user_instructor_assignments a 
                          JOIN instructors i ON a.instructor_id = i.instructor_id 
                          WHERE a.user_id = ? AND a.status = 'Active'";
        $checkAnyStmt = $conn->prepare($checkAnyActive);
        $checkAnyStmt->bind_param("i", $userId);
        $checkAnyStmt->execute();
        $anyActiveResult = $checkAnyStmt->get_result()->fetch_assoc();
        
        if ($anyActiveResult) {
            redirect('instructor_assignments.php', 'User is already assigned to instructor: ' . $anyActiveResult['instructor_name'] . '. Please remove the existing assignment first.', 'danger');
        } else {
            // Insert new assignment with 1 month duration
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime('+1 month'));
            $insertQuery = "INSERT INTO user_instructor_assignments (user_id, instructor_id, assigned_by, start_date, end_date) VALUES (?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $adminId = $_SESSION['admin_id'] ?? null;
            $insertStmt->bind_param("iiiss", $userId, $instructorId, $adminId, $startDate, $endDate);
            
            if ($insertStmt->execute()) {
                // Also update the user's assigned_instructor_id for quick reference
                $updateUser = "UPDATE users SET assigned_instructor_id = ? WHERE user_id = ?";
                $updateStmt = $conn->prepare($updateUser);
                $updateStmt->bind_param("ii", $instructorId, $userId);
                $updateStmt->execute();
                
                redirect('instructor_assignments.php', 'User assigned to instructor successfully (1 Month Validity)', 'success');
            }
        }
    }
}

// Fetch all instructors and users for the modal
$instructors_list = $conn->query("SELECT instructor_id, name FROM instructors WHERE status = 'Active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$users_list = $conn->query("SELECT user_id, name, email FROM users WHERE status = 'Active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Fetch all assignments with user and instructor details
$query = "SELECT a.*, u.name as user_name, u.email as user_email, i.name as instr_name, i.specialization 
          FROM user_instructor_assignments a
          JOIN users u ON a.user_id = u.user_id
          JOIN instructors i ON a.instructor_id = i.instructor_id
          ORDER BY a.assigned_at DESC";
$assignments = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

// Fetch stats
$totalAssignments = count($assignments);
$activeAssignments = $conn->query("SELECT COUNT(*) FROM user_instructor_assignments WHERE status = 'Active'")->fetch_row()[0];
$topInstructor = $conn->query("SELECT i.name, COUNT(a.assignment_id) as count 
                              FROM instructors i 
                              LEFT JOIN user_instructor_assignments a ON i.instructor_id = a.instructor_id 
                              GROUP BY i.instructor_id 
                              ORDER BY count DESC LIMIT 1")->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Instructor Assignments - Admin</title>
    <?php include('../includes/head.php'); ?>
    <style>
        .dropdown-menu {
            margin-top: 0;
            transform-origin: top right;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4) !important;
        }
        .dropdown-item:hover {
            background: var(--primary-gradient) !important;
            color: white !important;
        }
        .btn-action-trigger:hover {
            background: rgba(255,255,255,0.1) !important;
            border-color: var(--primary-color) !important;
        }
        .btn-action-trigger:hover i {
            color: var(--primary-color) !important;
        }
        /* Ensure table allows dropdowns to overflow */
        .table-responsive {
            overflow: visible !important;
            padding-bottom: 60px; /* Extra space for bottom rows */
        }
        .animate {
            animation-duration: 0.2s;
            animation-fill-mode: both;
        }
        @keyframes slideIn {
            0% { transform: translateY(10px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }
        .slideIn { animation-name: slideIn; }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/admin_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">🤝 Instructor Assignments</h2>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#newAssignmentModal"><i class="fas fa-plus mr-2"></i>New Assignment</button>
                </div>

                <?php displayMessage(); ?>

                <!-- Stats Overview -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon text-primary"><i class="fas fa-link"></i></div>
                            <div class="stat-number"><?php echo $totalAssignments; ?></div>
                            <div class="stat-label">Total Pairings</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon text-success"><i class="fas fa-check-circle"></i></div>
                            <div class="stat-number"><?php echo $activeAssignments; ?></div>
                            <div class="stat-label">Active Assignments</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card">
                            <div class="stat-icon text-warning"><i class="fas fa-award"></i></div>
                            <div class="stat-number"><?php echo $topInstructor ? $topInstructor['count'] : 0; ?></div>
                            <div class="stat-label">Top Instructor (<?php echo $topInstructor ? htmlspecialchars($topInstructor['name']) : 'N/A'; ?>)</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list mr-2"></i>Active Connections</h5>
                        <input type="text" class="form-control" style="width: 250px;" id="searchInput" placeholder="Search pairings...">
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="assignmentsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Member</th>
                                        <th>Assigned Instructor</th>
                                        <th>Period</th>
                                        <th>Days Left</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($assignments)): ?>
                                        <?php foreach ($assignments as $a): ?>
                                            <?php 
                                                $daysLeft = getDaysRemaining($a['end_date']);
                                                $daysColor = $daysLeft > 7 ? 'badge-success' : ($daysLeft > 0 ? 'badge-warning' : 'badge-danger');
                                            ?>
                                            <tr>
                                                <td>#<?php echo $a['assignment_id']; ?></td>
                                                <td>
                                                    <div class="font-weight-bold"><?php echo htmlspecialchars($a['user_name']); ?></div>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($a['user_email']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="font-weight-bold text-warning"><?php echo htmlspecialchars($a['instr_name']); ?></div>
                                                    <div class="badge badge-outline-info" style="font-size: 10px;"><?php echo htmlspecialchars($a['specialization']); ?></div>
                                                </td>
                                                <td>
                                                    <div class="small">From: <strong><?php echo formatDate($a['start_date']); ?></strong></div>
                                                    <div class="small">To: <strong><?php echo formatDate($a['end_date']); ?></strong></div>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $daysColor; ?> p-2" style="min-width: 80px;">
                                                        <?php echo $daysLeft; ?> Days
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $a['status'] === 'Active' ? 'badge-success' : 'badge-danger'; ?> p-2" style="min-width: 80px;">
                                                        <?php echo htmlspecialchars($a['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="dropdown" style="position: static;">
                                                        <button class="btn btn-sm btn-action-trigger" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.3s;">
                                                            <i class="fas fa-ellipsis-v text-muted"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right shadow-lg animate slideIn" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.15); border-radius: 15px; padding: 10px; min-width: 180px; z-index: 1050;">
                                                            <div class="dropdown-header text-muted small text-uppercase font-weight-bold mb-2" style="letter-spacing: 1px; font-size: 10px;">Assignment Actions</div>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="action" value="toggle_status">
                                                                <input type="hidden" name="assignment_id" value="<?php echo $a['assignment_id']; ?>">
                                                                <input type="hidden" name="status" value="<?php echo $a['status'] === 'Active' ? 'Inactive' : 'Active'; ?>">
                                                                <button type="submit" class="dropdown-item py-2 d-flex align-items-center" style="border-radius: 10px; color: #e2e8f0;">
                                                                    <i class="fas fa-toggle-<?php echo $a['status'] === 'Active' ? 'on' : 'off'; ?> mr-3 text-warning" style="width: 20px;"></i>
                                                                    <span>Mark <?php echo $a['status'] === 'Active' ? 'Inactive' : 'Active'; ?></span>
                                                                </button>
                                                            </form>
                                                            <div class="dropdown-divider" style="border-color: rgba(255,255,255,0.05);"></div>
                                                            <button class="dropdown-item py-2 d-flex align-items-center text-danger" onclick="confirmDelete(<?php echo $a['assignment_id']; ?>)" style="border-radius: 10px;">
                                                                <i class="fas fa-trash-alt mr-3" style="width: 20px;"></i>
                                                                <span>Delete Assignment</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="7" class="text-center text-muted">No assignments found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🗑️ Remove Assignment</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to remove this instructor assignment? This will disconnect the member from their instructor.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form method="POST">
                        <input type="hidden" name="action" value="delete_assignment">
                        <input type="hidden" name="assignment_id" id="deleteAssignmentId">
                        <button type="submit" class="btn btn-danger">Confirm Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- New Assignment Modal -->
    <div class="modal fade" id="newAssignmentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">👨‍🏫 New Instructor Assignment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="new_assignment">
                        
                        <div class="form-group">
                            <label>Select Member <span class="text-danger">*</span></label>
                            <select class="form-control" name="user_id" required>
                                <option value="">-- Select Member --</option>
                                <?php foreach ($users_list as $u): ?>
                                    <option value="<?php echo $u['user_id']; ?>">
                                        <?php echo htmlspecialchars($u['name'] . ' (' . $u['email'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Select Instructor <span class="text-danger">*</span></label>
                            <select class="form-control" name="instructor_id" required>
                                <option value="">-- Select Instructor --</option>
                                <?php foreach ($instructors_list as $i): ?>
                                    <option value="<?php echo $i['instructor_id']; ?>">
                                        <?php echo htmlspecialchars($i['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <p class="text-muted small">Note: This will directly assign the user to the selected instructor for 1 month. Ensure the user is not already assigned to another active instructor.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Instructor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
    <script>
        function confirmDelete(id) {
            $('#deleteAssignmentId').val(id);
            $('#deleteModal').modal('show');
        }

        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('assignmentsTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const text = rows[i].textContent.toLowerCase();
                rows[i].style.display = text.includes(searchTerm) ? '' : 'none';
            }
        });
    </script>
</body>
</html>
