<?php
/**
 * Manage Admin Accounts - Admin Panel
 */

require_once('../config/functions.php');
requireAdmin();

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_admin') {
        $adminId = sanitize($_POST['admin_id'] ?? '');
        
        // Prevent deleting the current admin
        if ($adminId == $_SESSION['admin_id']) {
            redirect('admin_list.php', 'Cannot delete your own account', 'danger');
        }
        
        $query = "DELETE FROM admin WHERE admin_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $adminId);
        if ($stmt->execute()) {
            redirect('admin_list.php', 'Admin account deleted successfully', 'success');
        }
    } elseif ($action === 'convert_to_user') {
        $adminId = sanitize($_POST['admin_id'] ?? '');
        
        // Prevent downgrading the current admin
        if ($adminId == $_SESSION['admin_id']) {
            redirect('admin_list.php', 'Cannot downgrade your own account', 'danger');
        }
        
        $adminName = sanitize($_POST['admin_name'] ?? '');
        $adminEmail = sanitize($_POST['admin_email'] ?? '');
        $adminPhone = sanitize($_POST['admin_phone'] ?? '');
        
        // Get admin details
        $getAdminQuery = "SELECT * FROM admin WHERE admin_id = ?";
        $stmt = $conn->prepare($getAdminQuery);
        $stmt->bind_param("i", $adminId);
        $stmt->execute();
        $adminResult = $stmt->get_result()->fetch_assoc();
        
        if ($adminResult) {
            // Check if email already exists in users
            $checkQuery = "SELECT COUNT(*) as count FROM users WHERE email = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $adminEmail);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result()->fetch_assoc();
            
            if ($checkResult['count'] == 0) {
                // Create user account
                $userQuery = "INSERT INTO users (name, email, password, phone, gender, status) VALUES (?, ?, ?, ?, ?, 'Active')";
                $userStmt = $conn->prepare($userQuery);
                $gender = 'Other';
                $userStmt->bind_param("sssss", $adminName, $adminEmail, $adminResult['password'], $adminPhone, $gender);
                
                if ($userStmt->execute()) {
                    // Delete from admin
                    $delQuery = "DELETE FROM admin WHERE admin_id = ?";
                    $delStmt = $conn->prepare($delQuery);
                    $delStmt->bind_param("i", $adminId);
                    $delStmt->execute();
                    redirect('admin_list.php', 'Admin converted to User successfully', 'success');
                }
            } else {
                redirect('admin_list.php', 'This email already exists as User', 'danger');
            }
        }
    }
}

// Get all admin accounts
$admins = $conn->query("SELECT * FROM admin ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Admin Accounts - Admin</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/admin_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">👨‍💼 Manage Admin Accounts</h2>
                    <div class="text-muted small">System administration and access control</div>
                </div>

                <?php displayMessage(); ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">👥 All Admin Accounts</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Admin Details</th>
                                        <th>Contact</th>
                                        <th>Joined</th>
                                        <th style="text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($admins)) {
                                        $totalAdmins = count($admins);
                                        $currentIndex = 0;
                                        foreach ($admins as $admin) {
                                            $currentIndex++;
                                            $isCurrent = $admin['admin_id'] == $_SESSION['admin_id'];
                                            $currentBadge = $isCurrent ? '<span class="badge badge-primary ml-2">Current</span>' : '';
                                            // Add dropup class for the last few rows to ensure visibility
                                            $dropClass = ($totalAdmins - $currentIndex < 2) ? 'dropup' : 'dropdown';
                                            
                                            echo "
                                            <tr" . ($isCurrent ? " style='background: rgba(245, 158, 11, 0.05);'" : "") . ">
                                                <td class='text-center'><strong>#" . $admin['admin_id'] . "</strong></td>
                                                <td>
                                                    <div class='font-weight-bold'>" . htmlspecialchars($admin['name']) . " " . $currentBadge . "</div>
                                                    <div class='text-muted small'>" . htmlspecialchars($admin['email']) . "</div>
                                                </td>
                                                <td><small>" . htmlspecialchars($admin['phone']) . "</small></td>
                                                <td><small>" . formatDate($admin['created_at'] ?? date('Y-m-d')) . "</small></td>
                                                <td class='text-center'>";
                                            
                                            if ($isCurrent) {
                                                echo "<span class='badge badge-dark p-2' style='border: 1px solid rgba(255,255,255,0.1);'><i class='fas fa-user-check mr-1'></i> Current Account</span>";
                                            } else {
                                                echo "
                                                    <div class='$dropClass'>
                                                        <button class='btn btn-sm btn-outline-light dropdown-toggle px-3' type='button' data-toggle='dropdown' data-boundary='viewport' style='border-radius: 12px; border: 1px solid rgba(255,255,255,0.2);'>
                                                            <i class='fas fa-cog mr-1'></i> Manage
                                                        </button>
                                                        <div class='dropdown-menu dropdown-menu-right'>
                                                            <a class='dropdown-item py-2' href='#' onclick=\"setConvertAdminData('" . $admin['admin_id'] . "', '" . addslashes($admin['name']) . "', '" . $admin['email'] . "', '" . $admin['phone'] . "')\" data-toggle='modal' data-target='#convertUserModal' style='color: #cbd5e1;'>
                                                                <i class='fas fa-user-tag mr-2 text-warning'></i> Convert to User
                                                            </a>
                                                            <div class='dropdown-divider' style='border-top: 1px solid rgba(255,255,255,0.05);'></div>
                                                            <a class='dropdown-item py-2 text-danger' href='#' onclick='deleteAdmin(" . $admin['admin_id'] . ")'>
                                                                <i class='fas fa-trash mr-2'></i> Delete Admin
                                                            </a>
                                                        </div>
                                                    </div>";
                                            }
                                            
                                            echo "
                                                </td>
                                            </tr>
                                            ";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center text-muted'>No admin accounts found</td></tr>";
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

    <!-- Delete Admin Modal -->
    <div class="modal fade" id="deleteAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">🗑️ Delete Admin Account</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this admin account? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete_admin">
                        <input type="hidden" name="admin_id" id="deleteAdminId">
                        <button type="submit" class="btn btn-danger">Delete Admin Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Convert to User Modal -->
    <div class="modal fade" id="convertUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">👤 Downgrade to User</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to downgrade this admin to a <strong>User (Member)</strong>?</p>
                    <div class="bg-light p-3 rounded">
                        <p><strong>Admin Name:</strong> <span id="convertAdminName"></span></p>
                        <p><strong>Email:</strong> <span id="convertAdminEmail"></span></p>
                        <p><strong>Phone:</strong> <span id="convertAdminPhone"></span></p>
                    </div>
                    <p class="mt-3 text-muted"><small>Note: The admin will lose all administrative privileges and be added to the Members list.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="convert_to_user">
                        <input type="hidden" name="admin_id" id="convertAdminId">
                        <input type="hidden" name="admin_name" id="convertAdminInputName">
                        <input type="hidden" name="admin_email" id="convertAdminInputEmail">
                        <input type="hidden" name="admin_phone" id="convertAdminInputPhone">
                        <button type="submit" class="btn btn-warning">Downgrade to User</button>
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
        function deleteAdmin(adminId) {
            document.getElementById('deleteAdminId').value = adminId;
            new bootstrap.Modal(document.getElementById('deleteAdminModal')).show();
        }

        function setConvertAdminData(adminId, adminName, adminEmail, adminPhone) {
            document.getElementById('convertAdminId').value = adminId;
            document.getElementById('convertAdminInputName').value = adminName;
            document.getElementById('convertAdminInputEmail').value = adminEmail;
            document.getElementById('convertAdminInputPhone').value = adminPhone;
            
            document.getElementById('convertAdminName').textContent = adminName;
            document.getElementById('convertAdminEmail').textContent = adminEmail;
            document.getElementById('convertAdminPhone').textContent = adminPhone;
        }
    </script>
</body>
</html>
