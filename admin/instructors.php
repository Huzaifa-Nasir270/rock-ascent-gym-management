<?php
/**
 * Manage Instructors - Admin Panel
 */

require_once('../config/functions.php');
requireAdmin();

// Handle instructor actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_instructor') {
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $specialization = sanitize($_POST['specialization'] ?? '');
        $password = hashPassword('instructor123'); // Default password

        $query = "INSERT INTO instructors (name, email, password, phone, specialization, status) VALUES (?, ?, ?, ?, ?, 'Active')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $name, $email, $password, $phone, $specialization);

        if ($stmt->execute()) {
            redirect('instructors.php', 'Instructor added successfully', 'success');
        }
    } elseif ($action === 'delete_instructor') {
        $instructorId = sanitize($_POST['instructor_id'] ?? '');
        
        // Start transaction for safe deletion
        $conn->begin_transaction();
        
        try {
            // 1. Handle subscriptions (Try to set NULL, if fails it might be NOT NULL)
            // We use a separate try-catch or check if we should delete or nullify
            $stmt1 = $conn->prepare("UPDATE subscriptions SET instructor_id = NULL WHERE instructor_id = ?");
            $stmt1->bind_param("i", $instructorId);
            @$stmt1->execute(); // Use @ to suppress if NULL is not allowed
            
            // 2. Handle user-instructor assignments (These are safe to delete)
            $stmt2 = $conn->prepare("DELETE FROM user_instructor_assignments WHERE instructor_id = ?");
            $stmt2->bind_param("i", $instructorId);
            $stmt2->execute();
            
            // 3. Handle workout and diet plans 
            // The error showed these are NOT NULL or have strict constraints. 
            // We will DELETE them to satisfy the foreign key requirements.
            $stmt3 = $conn->prepare("DELETE FROM workout_plans WHERE instructor_id = ?");
            $stmt3->bind_param("i", $instructorId);
            $stmt3->execute();
            
            $stmt4 = $conn->prepare("DELETE FROM diet_plans WHERE instructor_id = ?");
            $stmt4->bind_param("i", $instructorId);
            $stmt4->execute();
            
            // 4. Handle messages/chat history
            $stmt5 = $conn->prepare("DELETE FROM messages WHERE (sender_id = ? AND sender_type = 'Instructor') OR (receiver_id = ? AND receiver_type = 'Instructor')");
            $stmt5->bind_param("ii", $instructorId, $instructorId);
            $stmt5->execute();

            // 5. Finally delete the instructor
            $stmt6 = $conn->prepare("DELETE FROM instructors WHERE instructor_id = ?");
            $stmt6->bind_param("i", $instructorId);
            $stmt6->execute();
            
            $conn->commit();
            redirect('instructors.php', 'Instructor and all related data removed successfully', 'success');
        } catch (Exception $e) {
            $conn->rollback();
            redirect('instructors.php', 'Error deleting instructor: ' . $e->getMessage(), 'danger');
        }
    }
 elseif ($action === 'convert_to_user') {
        $instructorId = sanitize($_POST['instructor_id'] ?? '');
        $instructorName = sanitize($_POST['instructor_name'] ?? '');
        $instructorEmail = sanitize($_POST['instructor_email'] ?? '');
        $instructorPhone = sanitize($_POST['instructor_phone'] ?? '');
        
        // Get instructor details
        $getInstrQuery = "SELECT * FROM instructors WHERE instructor_id = ?";
        $stmt = $conn->prepare($getInstrQuery);
        $stmt->bind_param("i", $instructorId);
        $stmt->execute();
        $instrResult = $stmt->get_result()->fetch_assoc();
        
        if ($instrResult) {
            // Check if email already exists in users
            $checkQuery = "SELECT COUNT(*) as count FROM users WHERE email = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $instructorEmail);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result()->fetch_assoc();
            
            if ($checkResult['count'] == 0) {
                // Create user account
                $userQuery = "INSERT INTO users (name, email, password, phone, gender, status) VALUES (?, ?, ?, ?, ?, 'Active')";
                $userStmt = $conn->prepare($userQuery);
                $gender = 'Other';
                $userStmt->bind_param("sssss", $instructorName, $instructorEmail, $instrResult['password'], $instructorPhone, $gender);
                
                if ($userStmt->execute()) {
                    // Delete from instructors
                    $delQuery = "DELETE FROM instructors WHERE instructor_id = ?";
                    $delStmt = $conn->prepare($delQuery);
                    $delStmt->bind_param("i", $instructorId);
                    $delStmt->execute();
                    redirect('instructors.php', 'Instructor converted to User successfully', 'success');
                }
            } else {
                redirect('instructors.php', 'This email already exists as User', 'danger');
            }
        }
    }
}

// Get all instructors
$instructors = $conn->query("SELECT * FROM instructors ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Instructors - Admin</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/admin_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">🏋️ Manage Instructors</h2>
                    <button class="btn btn-primary px-4" data-toggle="modal" data-target="#addInstructorModal" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-plus mr-2"></i> Add Instructor
                    </button>
                </div>

                <?php displayMessage(); ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">👥 All Instructors</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Instructor Details</th>
                                        <th>Contact</th>
                                        <th>Specialization</th>
                                        <th>Status</th>
                                        <th style="text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($instructors)) {
                                        $totalInstr = count($instructors);
                                        $currentIndex = 0;
                                        foreach ($instructors as $instructor) {
                                            $currentIndex++;
                                            $statusBadge = $instructor['status'] === 'Active' ? 'badge-success' : 'badge-danger';
                                            // Add dropup class for the last few rows to ensure visibility
                                            $dropClass = ($totalInstr - $currentIndex < 2) ? 'dropup' : 'dropdown';
                                            
                                            echo "
                                            <tr>
                                                <td class='text-center'><strong>#" . $instructor['instructor_id'] . "</strong></td>
                                                <td>
                                                    <div class='font-weight-bold'>" . htmlspecialchars($instructor['name']) . "</div>
                                                    <div class='text-muted small'>" . htmlspecialchars($instructor['email']) . "</div>
                                                </td>
                                                <td><small>" . htmlspecialchars($instructor['phone']) . "</small></td>
                                                <td><span class='badge badge-dark p-2' style='border: 1px solid rgba(255,255,255,0.1);'>" . htmlspecialchars($instructor['specialization'] ?? 'General') . "</span></td>
                                                <td><span class='badge $statusBadge'>" . $instructor['status'] . "</span></td>
                                                <td class='text-center'>
                                                    <div class='$dropClass'>
                                                        <button class='btn btn-sm btn-outline-light dropdown-toggle px-3' type='button' data-toggle='dropdown' data-boundary='viewport' style='border-radius: 12px; border: 1px solid rgba(255,255,255,0.2);'>
                                                            <i class='fas fa-cog mr-1'></i> Manage
                                                        </button>
                                                        <div class='dropdown-menu dropdown-menu-right' style='background: #1e293b; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.5); border-radius: 15px; padding: 10px;'>
                                                            <a class='dropdown-item py-2' href='#' onclick=\"setConvertInstructorData('" . $instructor['instructor_id'] . "', '" . addslashes($instructor['name']) . "', '" . $instructor['email'] . "', '" . $instructor['phone'] . "')\" data-toggle='modal' data-target='#convertUserModal' style='color: #cbd5e1;'>
                                                                <i class='fas fa-user-tag mr-2 text-warning'></i> Convert to User
                                                            </a>
                                                            <div class='dropdown-divider' style='border-top: 1px solid rgba(255,255,255,0.05);'></div>
                                                            <form method='POST' onsubmit=\"return confirm('Delete this instructor?');\">
                                                                <input type='hidden' name='action' value='delete_instructor'>
                                                                <input type='hidden' name='instructor_id' value='" . $instructor['instructor_id'] . "'>
                                                                <button type='submit' class='dropdown-item py-2 text-danger' style='background: transparent; border: none; width: 100%; text-align: left;'>
                                                                    <i class='fas fa-trash mr-2'></i> Delete Instructor
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            ";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center text-muted'>No instructors found</td></tr>";
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

    <!-- Add Instructor Modal -->
    <div class="modal fade" id="addInstructorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Add New Instructor</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_instructor">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" class="form-control" name="phone" placeholder="Enter 11 digit phone number" pattern="[0-9]{11}" required>
                        </div>
                        <div class="form-group">
                            <label>Specialization</label>
                            <input type="text" class="form-control" name="specialization" placeholder="e.g., Strength Training">
                        </div>
                        <div class="alert alert-info py-2 mb-0">
                            <small><i class="fas fa-info-circle"></i> Note: The default password for new instructors is <strong>instructor123</strong></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Instructor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Convert to User Modal -->
    <div class="modal fade" id="convertUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">👤 Convert to User</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to convert this instructor to a <strong>User (Member)</strong>?</p>
                    <div class="bg-light p-3 rounded">
                        <p><strong>Instructor Name:</strong> <span id="convertInstrName"></span></p>
                        <p><strong>Email:</strong> <span id="convertInstrEmail"></span></p>
                        <p><strong>Phone:</strong> <span id="convertInstrPhone"></span></p>
                    </div>
                    <p class="mt-3 text-muted"><small>Note: The instructor will be removed from the Instructors list and added to the Members list.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="convert_to_user">
                        <input type="hidden" name="instructor_id" id="convertInstrId">
                        <input type="hidden" name="instructor_name" id="convertInstrInputName">
                        <input type="hidden" name="instructor_email" id="convertInstrInputEmail">
                        <input type="hidden" name="instructor_phone" id="convertInstrInputPhone">
                        <button type="submit" class="btn btn-warning">Convert to User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
    <style>
        .btn-xs {
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        .table td {
            vertical-align: middle;
        }
        @media (max-width: 768px) {
            .btn-xs {
                display: block;
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
    <script>
        function setConvertInstructorData(instructorId, instructorName, instructorEmail, instructorPhone) {
            document.getElementById('convertInstrId').value = instructorId;
            document.getElementById('convertInstrInputName').value = instructorName;
            document.getElementById('convertInstrInputEmail').value = instructorEmail;
            document.getElementById('convertInstrInputPhone').value = instructorPhone;
            
            document.getElementById('convertInstrName').textContent = instructorName;
            document.getElementById('convertInstrEmail').textContent = instructorEmail;
            document.getElementById('convertInstrPhone').textContent = instructorPhone;
        }
    </script>
</body>
</html>
