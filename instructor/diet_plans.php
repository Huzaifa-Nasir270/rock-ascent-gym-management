<?php
/**
 * Instructor - Diet Plans
 */

require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_diet') {
        $userId = sanitize($_POST['user_id'] ?? '');
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $calories = sanitize($_POST['calories'] ?? '');
        $protein = sanitize($_POST['protein'] ?? '');
        $carbs = sanitize($_POST['carbs'] ?? '');
        $fats = sanitize($_POST['fats'] ?? '');
        $details = sanitize($_POST['plan_details'] ?? '');

        // Count existing plans for this user (for versioning)
        $versionStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM diet_plans WHERE user_id = ? AND instructor_id = ?");
        $versionStmt->bind_param("ii", $userId, $instructorId);
        $versionStmt->execute();
        $versionCount = $versionStmt->get_result()->fetch_assoc()['cnt'] + 1;
        $versionedTitle = $title . " (v$versionCount)";

        // Archive any existing ACTIVE plans for this user from this instructor
        $archiveStmt = $conn->prepare("UPDATE diet_plans SET is_active = 0 WHERE user_id = ? AND instructor_id = ? AND is_active = 1");
        $archiveStmt->bind_param("ii", $userId, $instructorId);
        $archiveStmt->execute();

        $query = "INSERT INTO diet_plans (instructor_id, user_id, title, description, calories, protein, carbs, fats, plan_details, is_active) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iissiiiis", $instructorId, $userId, $versionedTitle, $description, $calories, $protein, $carbs, $fats, $details);

        if ($stmt->execute()) {
            redirect('diet_plans.php', 'Diet plan created successfully. Previous plan archived.', 'success');
        }
    } elseif ($action === 'delete_diet') {
        $planId = sanitize($_POST['plan_id'] ?? '');
        $stmt = $conn->prepare("DELETE FROM diet_plans WHERE diet_plan_id = ? AND instructor_id = ?");
        $stmt->bind_param("ii", $planId, $instructorId);
        if ($stmt->execute()) {
            redirect('diet_plans.php', 'Diet plan deleted', 'success');
        }
    } elseif ($action === 'edit_diet') {
        $planId = sanitize($_POST['plan_id'] ?? '');
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $calories = sanitize($_POST['calories'] ?? '');
        $protein = sanitize($_POST['protein'] ?? '');
        $carbs = sanitize($_POST['carbs'] ?? '');
        $fats = sanitize($_POST['fats'] ?? '');
        $details = sanitize($_POST['plan_details'] ?? '');

        $query = "UPDATE diet_plans SET title=?, description=?, calories=?, protein=?, carbs=?, fats=?, plan_details=? WHERE diet_plan_id=? AND instructor_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssiiiisii", $title, $description, $calories, $protein, $carbs, $fats, $details, $planId, $instructorId);

        if ($stmt->execute()) {
            redirect('diet_plans.php', 'Diet plan updated', 'success');
        }
    }
}

// Add is_active column if it doesn't exist (safe migration)
$conn->query("ALTER TABLE diet_plans ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1");
// Mark all existing rows as active if they have no value set
$conn->query("UPDATE diet_plans SET is_active = 1 WHERE is_active IS NULL");

// Get assigned members from the new assignment table
$members = $conn->query("
    SELECT u.user_id, u.name FROM users u 
    INNER JOIN user_instructor_assignments a ON u.user_id = a.user_id 
    WHERE a.instructor_id = $instructorId AND a.status = 'Active'
    GROUP BY u.user_id
")->fetch_all(MYSQLI_ASSOC);

// Get ACTIVE diet plans only (latest per user), plus version count
$plans = $conn->query("
    SELECT dp.*, u.name as member_name,
           (SELECT COUNT(*) FROM diet_plans WHERE user_id = dp.user_id AND instructor_id = dp.instructor_id) as version_count
    FROM diet_plans dp 
    INNER JOIN users u ON dp.user_id = u.user_id 
    WHERE dp.instructor_id = $instructorId AND dp.is_active = 1
    ORDER BY dp.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Get plan history (archived plans) grouped by user
$history = $conn->query("
    SELECT dp.*, u.name as member_name
    FROM diet_plans dp 
    INNER JOIN users u ON dp.user_id = u.user_id 
    WHERE dp.instructor_id = $instructorId AND dp.is_active = 0
    ORDER BY dp.user_id, dp.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Group history by user
$historyByUser = [];
foreach ($history as $h) {
    $historyByUser[$h['user_id']][] = $h;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Diet Plans - Instructor</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Instructor Sidebar -->
            <?php include('../includes/instructor_sidebar.php'); ?>

            <div class="main-content">
                <h2 class="mb-4">🥗 Diet Plans</h2>

                <?php displayMessage(); ?>

                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addDietModal">
                    <i class="fas fa-plus"></i> Create Diet Plan
                </button>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Active Diet Plans <span class="badge badge-success ml-2"><?php echo count($plans); ?></span></h5>
                        <small class="text-muted">Only the latest active plan per member is shown</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Plan Title</th>
                                        <th>Calories</th>
                                        <th>Protein (g)</th>
                                        <th>Carbs (g)</th>
                                        <th>Fats (g)</th>
                                        <th>Version</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($plans)) {
                                        foreach ($plans as $plan) {
                                            $planJson = htmlspecialchars(json_encode($plan), ENT_QUOTES, 'UTF-8');
                                            $vCount = $plan['version_count'] ?? 1;
                                            echo "
                                            <tr>
                                                <td><strong>" . htmlspecialchars($plan['member_name']) . "</strong></td>
                                                <td>" . htmlspecialchars($plan['title']) . "</td>
                                                <td>" . $plan['calories'] . "</td>
                                                <td>" . $plan['protein'] . "</td>
                                                <td>" . $plan['carbs'] . "</td>
                                                <td>" . $plan['fats'] . "</td>
                                                <td><span class='badge badge-info'>v$vCount</span></td>
                                                <td>" . formatDate($plan['created_at']) . "</td>
                                                <td>
                                                    <button class='btn btn-sm btn-outline-warning' onclick='openEditDietModal($planJson)'><i class='fas fa-edit'></i></button>
                                                    <form method='POST' style='display:inline;' onsubmit='return confirm(\"Delete this plan?\");'>
                                                        <input type='hidden' name='action' value='delete_diet'>
                                                        <input type='hidden' name='plan_id' value='" . $plan['diet_plan_id'] . "'>
                                                        <button type='submit' class='btn btn-sm btn-outline-danger'><i class='fas fa-trash'></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                            ";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9' class='text-center text-muted'>No active diet plans yet</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if (!empty($historyByUser)): ?>
                <div class="card mt-4">
                    <div class="card-header" id="historyHeader">
                        <h5 class="mb-0">
                            <button class="btn btn-link text-warning p-0" type="button" data-toggle="collapse" data-target="#historyCollapse">
                                <i class="fas fa-history mr-2"></i>Plan History (Archived) 
                                <span class="badge badge-warning ml-2"><?php echo count($history); ?></span>
                            </button>
                        </h5>
                    </div>
                    <div id="historyCollapse" class="collapse">
                        <div class="card-body">
                            <?php foreach ($historyByUser as $uid => $hPlans): ?>
                            <h6 class="text-muted mb-2"><i class="fas fa-user mr-1"></i><?php echo htmlspecialchars($hPlans[0]['member_name']); ?></h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-sm" style="font-size:13px; opacity:0.75;">
                                    <thead><tr><th>Title</th><th>Cal</th><th>Protein</th><th>Carbs</th><th>Fats</th><th>Date</th><th>Del</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($hPlans as $hp): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($hp['title']); ?></td>
                                        <td><?php echo $hp['calories']; ?></td>
                                        <td><?php echo $hp['protein']; ?>g</td>
                                        <td><?php echo $hp['carbs']; ?>g</td>
                                        <td><?php echo $hp['fats']; ?>g</td>
                                        <td><?php echo formatDate($hp['created_at']); ?></td>
                                        <td>
                                            <form method='POST' style='display:inline;' onsubmit='return confirm("Permanently delete?");'>
                                                <input type='hidden' name='action' value='delete_diet'>
                                                <input type='hidden' name='plan_id' value='<?php echo $hp['diet_plan_id']; ?>'>
                                                <button type='submit' class='btn btn-xs btn-outline-danger'><i class='fas fa-trash'></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Diet Modal -->
    <div class="modal fade" id="addDietModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Diet Plan</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_diet">
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
                            <label>Plan Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Daily Calories</label>
                                <input type="number" class="form-control" name="calories">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Protein (g)</label>
                                <input type="number" class="form-control" name="protein">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Carbs (g)</label>
                                <input type="number" class="form-control" name="carbs">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Fats (g)</label>
                                <input type="number" class="form-control" name="fats">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Plan Details</label>
                            <textarea class="form-control" name="plan_details" rows="4" placeholder="Detailed diet plan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Diet Modal -->
    <div class="modal fade" id="editDietModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Diet Plan</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_diet">
                        <input type="hidden" name="plan_id" id="edit_diet_id">
                        <div class="form-group">
                            <label>Plan Title</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="2"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Daily Calories</label>
                                <input type="number" class="form-control" name="calories" id="edit_calories">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Protein (g)</label>
                                <input type="number" class="form-control" name="protein" id="edit_protein">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Carbs (g)</label>
                                <input type="number" class="form-control" name="carbs" id="edit_carbs">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Fats (g)</label>
                                <input type="number" class="form-control" name="fats" id="edit_fats">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Plan Details</label>
                            <textarea class="form-control" name="plan_details" id="edit_details" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
    <script>
        function openEditDietModal(plan) {
            $('#edit_diet_id').val(plan.diet_plan_id);
            $('#edit_title').val(plan.title);
            $('#edit_description').val(plan.description);
            $('#edit_calories').val(plan.calories);
            $('#edit_protein').val(plan.protein);
            $('#edit_carbs').val(plan.carbs);
            $('#edit_fats').val(plan.fats);
            $('#edit_details').val(plan.plan_details);
            $('#editDietModal').modal('show');
        }
    </script>
</body>
</html>
