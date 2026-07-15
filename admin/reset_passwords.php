<?php
/**
 * Admin Password Reset Tool
 * Resets non-bcrypt password records to a known default password.
 */

require_once('../config/functions.php');
requireAdmin();

$defaultPassword = 'password123';
$message = '';
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $defaultPassword = trim($_POST['reset_password'] ?? 'password123');
    if (empty($defaultPassword)) {
        $defaultPassword = 'password123';
    }

    try {
        $result = resetLegacyPasswords($defaultPassword);
        $updatedCount = $result['users'] + $result['instructors'] + $result['admin'];
        $message = "Passwords updated for $updatedCount legacy accounts.";
        if ($updatedCount > 0) {
            $message .= " New password: <strong>" . htmlspecialchars($defaultPassword) . "</strong>";
        } else {
            $message .= " No legacy or non-bcrypt password records were found.";
        }
    } catch (Exception $e) {
        $message = 'Error updating passwords: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Passwords - Admin</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/admin_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">🔐 Reset Legacy Passwords</h2>
                    <div class="small text-muted">System security maintenance tool</div>
                </div>

                <?php displayMessage(); ?>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i> About this tool</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-light" style="opacity: 0.8; line-height: 1.8;">
                            This tool updates any stored passwords that are not currently using secure bcrypt hashing.
                            When legacy password data is found, it resets those accounts to the password you choose below.
                        </p>
                        <p class="text-light" style="opacity: 0.8; line-height: 1.8;">
                            Recommended default: <strong class="text-warning">password123</strong>.
                            After reset, share the new password with users or ask them to update it immediately.
                        </p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-key mr-2"></i> Reset Parameters</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="reset_passwords.php">
                            <div class="form-group mb-4">
                                <label for="reset_password" class="font-weight-bold" style="color: #cbd5e1;">Default Password</label>
                                <input type="text" id="reset_password" name="reset_password" class="form-control" value="<?php echo htmlspecialchars($defaultPassword); ?>" required style="background: rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 12px; padding: 15px;">
                                <small class="form-text text-muted mt-2"><i class="fas fa-shield-alt mr-1"></i> All legacy or non-bcrypt password records will be updated to this password.</small>
                            </div>
                            <button type="submit" class="btn btn-primary px-5 py-3" style="border-radius: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                                <i class="fas fa-sync-alt mr-2"></i> Reset Legacy Passwords
                            </button>
                        </form>
                    </div>
                </div>

                <?php if ($result): ?>
                    <div class="row mt-5">
                        <div class="col-md-4">
                            <div class="stat-card" style="background: rgba(34, 197, 94, 0.1); border-color: rgba(34, 197, 94, 0.2);">
                                <div class="stat-icon" style="filter: drop-shadow(0 0 10px rgba(34, 197, 94, 0.4));">👤</div>
                                <div class="stat-number" style="background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $result['users']; ?></div>
                                <div class="stat-label">Users Updated</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-icon">🏋️</div>
                                <div class="stat-number"><?php echo $result['instructors']; ?></div>
                                <div class="stat-label">Instructor records updated</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-card">
                                <div class="stat-icon">👨‍💼</div>
                                <div class="stat-number"><?php echo $result['admin']; ?></div>
                                <div class="stat-label">Admin records updated</div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
</body>
</html>
