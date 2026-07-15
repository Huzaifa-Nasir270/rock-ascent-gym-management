<?php
/**
 * Manage Users - Admin Panel
 */

require_once('../config/functions.php');
requireAdmin();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_user') {
        $userId = sanitize($_POST['user_id'] ?? '');
        $query = "DELETE FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            redirect('users.php', 'User deleted successfully', 'success');
        }
    } elseif ($action === 'toggle_status') {
        $userId = sanitize($_POST['user_id'] ?? '');
        $newStatus = sanitize($_POST['status'] ?? '');
        $query = "UPDATE users SET status = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $newStatus, $userId);
        if ($stmt->execute()) {
            redirect('users.php', 'User status updated', 'success');
        }
    } elseif ($action === 'convert_to_instructor') {
        $userId = sanitize($_POST['user_id'] ?? '');
        $userName = sanitize($_POST['user_name'] ?? '');
        $userEmail = sanitize($_POST['user_email'] ?? '');
        $userPhone = sanitize($_POST['user_phone'] ?? '');
        
        // Get user details
        $getUserQuery = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($getUserQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userResult = $stmt->get_result()->fetch_assoc();
        
        if ($userResult) {
            // Create instructor account
            $instrQuery = "INSERT INTO instructors (name, email, password, phone, specialization, certification, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Active')";
            $instrStmt = $conn->prepare($instrQuery);
            $spec = 'General Fitness';
            $cert = 'Certified';
            $instrStmt->bind_param("ssssss", $userName, $userEmail, $userResult['password'], $userPhone, $spec, $cert);
            
            if ($instrStmt->execute()) {
                // Delete from users
                $delQuery = "DELETE FROM users WHERE user_id = ?";
                $delStmt = $conn->prepare($delQuery);
                $delStmt->bind_param("i", $userId);
                $delStmt->execute();
                redirect('users.php', 'User converted to Instructor successfully', 'success');
            }
        }
    } elseif ($action === 'convert_to_admin') {
        $userId = sanitize($_POST['user_id'] ?? '');
        $userName = sanitize($_POST['user_name'] ?? '');
        $userEmail = sanitize($_POST['user_email'] ?? '');
        $userPhone = sanitize($_POST['user_phone'] ?? '');
        
        // Get user details
        $getUserQuery = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($getUserQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userResult = $stmt->get_result()->fetch_assoc();
        
        if ($userResult) {
            // Check if email already exists in admin
            $checkQuery = "SELECT COUNT(*) as count FROM admin WHERE email = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $userEmail);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result()->fetch_assoc();
            
            if ($checkResult['count'] == 0) {
                // Create admin account
                $adminQuery = "INSERT INTO admin (name, email, password, phone) VALUES (?, ?, ?, ?)";
                $adminStmt = $conn->prepare($adminQuery);
                $adminStmt->bind_param("ssss", $userName, $userEmail, $userResult['password'], $userPhone);
                
                if ($adminStmt->execute()) {
                    // Delete from users
                    $delQuery = "DELETE FROM users WHERE user_id = ?";
                    $delStmt = $conn->prepare($delQuery);
                    $delStmt->bind_param("i", $userId);
                    $delStmt->execute();
                    redirect('users.php', 'User converted to Admin successfully', 'success');
                }
            } else {
                redirect('users.php', 'This email already exists as Admin', 'danger');
            }
        }
    } elseif ($action === 'add_user') {
        $name = sanitize($_POST['new_name'] ?? '');
        $email = sanitize($_POST['new_email'] ?? '');
        $phone = sanitize($_POST['new_phone'] ?? '');
        $gender = sanitize($_POST['new_gender'] ?? '');
        $password = $_POST['new_password'] ?? 'password123';
        
        // Check if email already exists
        $checkQuery = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result()->fetch_assoc();
        
        if ($checkResult['count'] > 0) {
            redirect('users.php', 'Email already exists', 'danger');
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO users (name, email, password, phone, gender, status) VALUES (?, ?, ?, ?, ?, 'Active')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssss", $name, $email, $hashedPassword, $phone, $gender);
            
            if ($stmt->execute()) {
                redirect('users.php', 'User added successfully', 'success');
            }
        }
    } elseif ($action === 'add_instructor') {
        $name = sanitize($_POST['instr_name'] ?? '');
        $email = sanitize($_POST['instr_email'] ?? '');
        $phone = sanitize($_POST['instr_phone'] ?? '');
        $specialization = sanitize($_POST['instr_specialization'] ?? '');
        $certification = sanitize($_POST['instr_certification'] ?? '');
        $password = $_POST['instr_password'] ?? 'password123';
        
        // Check if email already exists
        $checkQuery = "SELECT COUNT(*) as count FROM instructors WHERE email = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result()->fetch_assoc();
        
        if ($checkResult['count'] > 0) {
            redirect('users.php', 'Email already exists as Instructor', 'danger');
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $query = "INSERT INTO instructors (name, email, password, phone, specialization, certification, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'Active')";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssss", $name, $email, $hashedPassword, $phone, $specialization, $certification);
            
            if ($stmt->execute()) {
                redirect('users.php', 'Instructor added successfully', 'success');
            }
        }
    } elseif ($action === 'assign_instructor') {
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
            redirect('users.php', 'User is already assigned to instructor: ' . $anyActiveResult['instructor_name'] . '. Please remove the existing assignment first.', 'danger');
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
                
                redirect('users.php', 'User assigned to instructor successfully (1 Month Validity)', 'success');
            }
        }
    } elseif ($action === 'remove_instructor_assignment') {
        $userId = sanitize($_POST['user_id'] ?? '');
        $instructorId = sanitize($_POST['instructor_id'] ?? '');
        
        // Remove assignment
        $removeQuery = "UPDATE user_instructor_assignments SET status = 'Inactive' WHERE user_id = ? AND instructor_id = ? AND status = 'Active'";
        $removeStmt = $conn->prepare($removeQuery);
        $removeStmt->bind_param("ii", $userId, $instructorId);
        
        if ($removeStmt->execute()) {
            // Clear the user's assigned_instructor_id
            $clearUser = "UPDATE users SET assigned_instructor_id = NULL WHERE user_id = ?";
            $clearStmt = $conn->prepare($clearUser);
            $clearStmt->bind_param("i", $userId);
            $clearStmt->execute();
            
            redirect('users.php', 'Instructor assignment removed successfully', 'success');
        }
    }
}

// Get all instructors for assignment dropdown
$instructors = $conn->query("SELECT instructor_id, name FROM instructors WHERE status = 'Active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Get all users
$search = sanitize($_GET['search'] ?? '');
$query = "SELECT * FROM users";
if (!empty($search)) {
    $query .= " WHERE name LIKE ? OR email LIKE ? OR user_id LIKE ?";
    $stmt = $conn->prepare($query);
    $searchTerm = "%$search%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare($query);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users - Admin</title>
    <?php include('../includes/head.php'); ?>
    <style>
        .dropdown-menu {
            background: #1e293b !important;
            border: 1px solid rgba(255,255,255,0.15) !important;
            border-radius: 15px !important;
            padding: 10px !important;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4) !important;
            z-index: 1050 !important;
        }
        .dropdown-item {
            border-radius: 10px !important;
            padding: 10px 15px !important;
            color: #e2e8f0 !important;
            transition: all 0.2s !important;
        }
        .dropdown-item:hover {
            background: var(--primary-gradient) !important;
            color: white !important;
            transform: translateX(5px);
        }
        .btn-manage-trigger {
            background: rgba(255,255,255,0.05) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            border-radius: 12px !important;
            padding: 8px 16px !important;
            color: #94a3b8 !important;
            transition: all 0.3s !important;
        }
        .btn-manage-trigger:hover {
            border-color: var(--primary-color) !important;
            color: var(--primary-color) !important;
            background: rgba(255,255,255,0.1) !important;
        }
        .table-responsive {
            overflow: visible !important;
            padding-bottom: 80px;
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
                <h2 class="mb-4">👥 Manage Users</h2>

                <?php displayMessage(); ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">👥 All Members</h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addUserModal">
                                <i class="fas fa-plus"></i> Add User
                            </button>
                            <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#addInstructorModal">
                                <i class="fas fa-user-plus"></i> Add Instructor
                            </button>
                            <input type="text" class="form-control" style="width: 250px; max-width: 100%;" id="searchInput" placeholder="Search by name, ID, or email...">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive" style="font-size: 13px;">
                            <table class="table table-hover table-bordered" id="usersTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 60px;">ID</th>
                                        <th>Member Details</th>
                                        <th style="width: 120px;">Contact</th>
                                        <th style="width: 80px;">Gender</th>
                                        <th style="width: 90px;">Status</th>
                                        <th style="width: 100px;">Joined</th>
                                        <th style="width: 250px; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($users)) {
                                        $totalUsers = count($users);
                                        $currentIndex = 0;
                                        foreach ($users as $user) {
                                            $currentIndex++;
                                            $statusBadge = $user['status'] === 'Active' ? 'badge-success' : 'badge-danger';
                                            // Add dropup class for the last 3 rows to ensure visibility
                                            $dropClass = ($totalUsers - $currentIndex < 3) ? 'dropup' : 'dropdown';
                                            
                                            echo "
                                            <tr>
                                                <td class='text-center'><strong>#" . $user['user_id'] . "</strong></td>
                                                <td>
                                                    <div class='font-weight-bold'>" . htmlspecialchars($user['name']) . "</div>
                                                    <div class='text-muted small'>" . htmlspecialchars($user['email']) . "</div>
                                                </td>
                                                <td><small>" . htmlspecialchars($user['phone']) . "</small></td>
                                                <td class='text-center'>" . htmlspecialchars($user['gender'] ?? 'N/A') . "</td>
                                                <td class='text-center'><span class='badge $statusBadge'>" . $user['status'] . "</span></td>
                                                <td><small>" . formatDate($user['created_at']) . "</small></td>
                                                <td class='text-center'>
                                                    <div class='$dropClass'>
                                                        <button class='btn btn-sm btn-manage-trigger dropdown-toggle px-3' type='button' data-toggle='dropdown' data-boundary='viewport' aria-haspopup='true' aria-expanded='false'>
                                                            <i class='fas fa-cog mr-1'></i> Manage
                                                        </button>
                                                        <div class='dropdown-menu dropdown-menu-right shadow-lg animate slideIn'>
                                                            <div class='dropdown-header text-muted small text-uppercase font-weight-bold mb-2' style='letter-spacing: 1px; font-size: 10px;'>Member Actions</div>
                                                            ";
                                                            
                                                            if (!empty($user['assigned_instructor_id'])) {
                                                                echo "
                                                                <a class='dropdown-item py-2' href='#' onclick=\"setRemoveInstructorData('" . $user['user_id'] . "', '" . $user['assigned_instructor_id'] . "', '" . addslashes($user['name']) . "')\" data-toggle='modal' data-target='#removeInstructorModal'>
                                                                    <i class='fas fa-user-minus mr-3 text-warning' style='width: 20px;'></i> Remove Instructor
                                                                </a>";
                                                            } else {
                                                                echo "
                                                                <a class='dropdown-item py-2' href='#' onclick=\"setAssignInstructorData('" . $user['user_id'] . "', '" . addslashes($user['name']) . "')\" data-toggle='modal' data-target='#assignInstructorModal'>
                                                                    <i class='fas fa-user-plus mr-3 text-primary' style='width: 20px;'></i> Assign Instructor
                                                                </a>";
                                                            }
                                                            
                                                            echo "
                                                            <a class='dropdown-item py-2' href='#' onclick=\"setConvertData('" . $user['user_id'] . "', '" . addslashes($user['name']) . "', '" . $user['email'] . "', '" . $user['phone'] . "')\" data-toggle='modal' data-target='#convertInstructorModal'>
                                                                <i class='fas fa-user-tie mr-3 text-info' style='width: 20px;'></i> Convert to Instructor
                                                            </a>
                                                            <a class='dropdown-item py-2' href='#' onclick=\"setConvertData('" . $user['user_id'] . "', '" . addslashes($user['name']) . "', '" . $user['email'] . "', '" . $user['phone'] . "')\" data-toggle='modal' data-target='#convertAdminModal'>
                                                                <i class='fas fa-crown mr-3 text-success' style='width: 20px;'></i> Convert to Admin
                                                            </a>
                                                            <div class='dropdown-divider' style='border-top: 1px solid rgba(255,255,255,0.05);'></div>
                                                            <a class='dropdown-item py-2' href='#' onclick=\"toggleUserStatus('" . $user['user_id'] . "', '" . ($user['status'] === 'Active' ? 'Inactive' : 'Active') . "')\">
                                                                <i class='fas fa-toggle-" . ($user['status'] === 'Active' ? 'on' : 'off') . " mr-3 text-warning' style='width: 20px;'></i> 
                                                                " . ($user['status'] === 'Active' ? 'Deactivate' : 'Activate') . "
                                                            </a>
                                                            <a class='dropdown-item py-2 text-danger' href='#' onclick='deleteUser(" . $user['user_id'] . ")'>
                                                                <i class='fas fa-trash mr-3' style='width: 20px;'></i> Delete User
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            ";
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center text-muted'>No users found</td></tr>";
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

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">➕ Add New User</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_user">
                        <div class="form-group">
                            <label>Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="new_name" required>
                        </div>
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="new_email" required>
                        </div>
                        <div class="form-group">
                            <label>Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="new_phone" pattern="[0-9]{11}" placeholder="11 digit phone number" required>
                        </div>
                        <div class="form-group">
                            <label>Gender <span class="text-danger">*</span></label>
                            <select class="form-control" name="new_gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="new_password" placeholder="Default: password123" value="password123" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Instructor Modal -->
    <div class="modal fade" id="addInstructorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">👨‍🏫 Add New Instructor</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_instructor">
                        <div class="form-group">
                            <label>Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="instr_name" required>
                        </div>
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="instr_email" required>
                        </div>
                        <div class="form-group">
                            <label>Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="instr_phone" pattern="[0-9]{11}" placeholder="11 digit phone number" required>
                        </div>
                        <div class="form-group">
                            <label>Specialization <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="instr_specialization" placeholder="e.g., Weight Training, Cardio" required>
                        </div>
                        <div class="form-group">
                            <label>Certification</label>
                            <input type="text" class="form-control" name="instr_certification" placeholder="e.g., ACE Certified">
                        </div>
                        <div class="form-group">
                            <label>Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="instr_password" placeholder="Default: password123" value="password123" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Add Instructor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assign Instructor Modal -->
    <div class="modal fade" id="assignInstructorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">👨‍🏫 Assign Instructor to User</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="assign_instructor">
                        <input type="hidden" name="user_id" id="assignUserId">
                        <p>Assign an instructor to: <strong id="assignUserName"></strong></p>
                        <p class="text-muted"><small>Note: This will directly assign the user to the selected instructor. The instructor can then manage their attendance, workout plans, and diet plans.</small></p>
                        <div class="form-group">
                            <label>Select Instructor <span class="text-danger">*</span></label>
                            <select class="form-control" name="instructor_id" required>
                                <option value="">-- Select Instructor --</option>
                                <?php foreach ($instructors as $instr): ?>
                                    <option value="<?php echo $instr['instructor_id']; ?>"><?php echo htmlspecialchars($instr['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Instructor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Remove Instructor Modal -->
    <div class="modal fade" id="removeInstructorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">⚠️ Remove Instructor Assignment</h5>
                    <button type="button" class="close text-dark" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="remove_instructor_assignment">
                        <input type="hidden" name="user_id" id="removeAssignUserId">
                        <input type="hidden" name="instructor_id" id="removeAssignInstructorId">
                        <p>Are you sure you want to remove the instructor from: <strong id="removeAssignUserName"></strong>?</p>
                        <p class="text-muted"><small>Note: This will remove the member from the instructor's dashboard, but the member's profile and data will remain in the database.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Remove Instructor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">🗑️ Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_user">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Convert to Instructor Modal -->
    <div class="modal fade" id="convertInstructorModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">👨‍🏫 Convert to Instructor</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to convert this user to an <strong>Instructor</strong>?</p>
                    <div class="bg-light p-3 rounded">
                        <p><strong>User Name:</strong> <span id="instrName"></span></p>
                        <p><strong>Email:</strong> <span id="instrEmail"></span></p>
                        <p><strong>Phone:</strong> <span id="instrPhone"></span></p>
                    </div>
                    <p class="mt-3 text-muted"><small>Note: The user will be removed from the Members list and added to the Instructors list.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="convert_to_instructor">
                        <input type="hidden" name="user_id" id="instrUserId">
                        <input type="hidden" name="user_name" id="instrUserName">
                        <input type="hidden" name="user_email" id="instrUserEmail">
                        <input type="hidden" name="user_phone" id="instrUserPhone">
                        <button type="submit" class="btn btn-info">Convert to Instructor</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Convert to Admin Modal -->
    <div class="modal fade" id="convertAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">👨‍💼 Convert to Admin</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to convert this user to an <strong>Admin</strong>?</p>
                    <div class="bg-light p-3 rounded">
                        <p><strong>User Name:</strong> <span id="adminName"></span></p>
                        <p><strong>Email:</strong> <span id="adminEmail"></span></p>
                        <p><strong>Phone:</strong> <span id="adminPhone"></span></p>
                    </div>
                    <div class="alert alert-warning mt-3" role="alert">
                        <strong>⚠️ Warning:</strong> Admin accounts have full system access. Please confirm before proceeding.
                    </div>
                    <p class="text-muted"><small>Note: The user will be removed from the Members list and added to the Admin list.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="convert_to_admin">
                        <input type="hidden" name="user_id" id="adminUserId">
                        <input type="hidden" name="user_name" id="adminUserName">
                        <input type="hidden" name="user_email" id="adminUserEmail">
                        <input type="hidden" name="user_phone" id="adminUserPhone">
                        <button type="submit" class="btn btn-success">Convert to Admin</button>
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
        function deleteUser(userId) {
            document.getElementById('deleteUserId').value = userId;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function setConvertData(userId, userName, userEmail, userPhone) {
            // For Instructor modal
            document.getElementById('instrUserId').value = userId;
            document.getElementById('instrUserName').value = userName;
            document.getElementById('instrUserEmail').value = userEmail;
            document.getElementById('instrUserPhone').value = userPhone;
            
            document.getElementById('instrName').textContent = userName;
            document.getElementById('instrEmail').textContent = userEmail;
            document.getElementById('instrPhone').textContent = userPhone;
            
            // For Admin modal
            document.getElementById('adminUserId').value = userId;
            document.getElementById('adminUserName').value = userName;
            document.getElementById('adminUserEmail').value = userEmail;
            document.getElementById('adminUserPhone').value = userPhone;
            
            document.getElementById('adminName').textContent = userName;
            document.getElementById('adminEmail').textContent = userEmail;
            document.getElementById('adminPhone').textContent = userPhone;
        }

        function setAssignInstructorData(userId, userName) {
            document.getElementById('assignUserId').value = userId;
            document.getElementById('assignUserName').textContent = userName;
        }

        function setRemoveInstructorData(userId, instructorId, userName) {
            document.getElementById('removeAssignUserId').value = userId;
            document.getElementById('removeAssignInstructorId').value = instructorId;
            document.getElementById('removeAssignUserName').textContent = userName;
        }

        function toggleUserStatus(userId, newStatus) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="user_id" value="${userId}">
                <input type="hidden" name="status" value="${newStatus}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        // Search filter
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('usersTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const text = rows[i].textContent.toLowerCase();
                rows[i].style.display = text.includes(searchTerm) ? '' : 'none';
            }
        });
    </script>
</body>
</html>
