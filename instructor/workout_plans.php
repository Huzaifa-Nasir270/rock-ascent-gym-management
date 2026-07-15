<?php
/**
 * Instructor - Workout Plans
 */

require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_plan') {
        $userId = sanitize($_POST['user_id'] ?? '');
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $duration = sanitize($_POST['duration_weeks'] ?? '');
        $difficulty = sanitize($_POST['difficulty_level'] ?? '');
        $exercises = sanitize($_POST['exercises'] ?? '');

        $query = "INSERT INTO workout_plans (instructor_id, user_id, title, description, duration_weeks, difficulty_level, exercises) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iississ", $instructorId, $userId, $title, $description, $duration, $difficulty, $exercises);

        if ($stmt->execute()) {
            redirect('workout_plans.php', 'Workout plan created', 'success');
        }
    } elseif ($action === 'delete_plan') {
        $planId = sanitize($_POST['plan_id'] ?? '');
        $stmt = $conn->prepare("DELETE FROM workout_plans WHERE workout_plan_id = ? AND instructor_id = ?");
        $stmt->bind_param("ii", $planId, $instructorId);
        if ($stmt->execute()) {
            redirect('workout_plans.php', 'Workout plan deleted', 'success');
        }
    } elseif ($action === 'edit_plan') {
        $planId = sanitize($_POST['plan_id'] ?? '');
        $title = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $duration = sanitize($_POST['duration_weeks'] ?? '');
        $difficulty = sanitize($_POST['difficulty_level'] ?? '');
        $exercises = sanitize($_POST['exercises'] ?? '');

        $query = "UPDATE workout_plans SET title=?, description=?, duration_weeks=?, difficulty_level=?, exercises=? WHERE workout_plan_id=? AND instructor_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssissii", $title, $description, $duration, $difficulty, $exercises, $planId, $instructorId);

        if ($stmt->execute()) {
            redirect('workout_plans.php', 'Workout plan updated', 'success');
        }
    }
}

// Get assigned members for dropdown from the new assignment table
$members = $conn->query("
    SELECT u.user_id, u.name FROM users u 
    INNER JOIN user_instructor_assignments a ON u.user_id = a.user_id 
    WHERE a.instructor_id = $instructorId AND a.status = 'Active'
    GROUP BY u.user_id
")->fetch_all(MYSQLI_ASSOC);

// Get all workout plans
$plans = $conn->query("
    SELECT wp.*, u.name as member_name 
    FROM workout_plans wp 
    INNER JOIN users u ON wp.user_id = u.user_id 
    WHERE wp.instructor_id = $instructorId 
    ORDER BY wp.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Fetch system-wide exercises for quick library selection
$systemExercises = $conn->query("SELECT * FROM exercises WHERE instructor_id IS NULL ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Workout Plans - Instructor</title>
    <?php include('../includes/head.php'); ?>
    <style>
        .ex-lib-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            overflow: hidden;
            background: rgba(255,255,255,0.05);
            margin-bottom: 10px;
        }
        .ex-lib-item:hover {
            background: rgba(245, 158, 11, 0.2);
            border-color: #f59e0b;
            transform: translateY(-2px);
        }
        .ex-lib-img {
            width: 100%;
            height: 80px;
            object-fit: cover;
        }
        .ex-lib-name {
            font-size: 0.8rem;
            padding: 5px;
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Instructor Sidebar -->
            <?php include('../includes/instructor_sidebar.php'); ?>

            <div class="main-content">
                <h2 class="mb-4">💪 Workout Plans</h2>

                <?php displayMessage(); ?>

                <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addPlanModal">
                    <i class="fas fa-plus"></i> Create New Plan
                </button>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Workout Plans</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Member</th>
                                        <th>Plan Title</th>
                                        <th>Duration</th>
                                        <th>Difficulty</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($plans)) {
                                        foreach ($plans as $plan) {
                                            $planJson = htmlspecialchars(json_encode($plan), ENT_QUOTES, 'UTF-8');
                                            echo "
                                            <tr>
                                                <td>" . htmlspecialchars($plan['member_name']) . "</td>
                                                <td>" . htmlspecialchars($plan['title']) . "</td>
                                                <td>" . $plan['duration_weeks'] . " weeks</td>
                                                <td><span class='badge badge-info'>" . $plan['difficulty_level'] . "</span></td>
                                                <td>" . formatDate($plan['created_at']) . "</td>
                                                <td>
                                                    <button class='btn btn-sm btn-outline-warning' onclick='openEditPlanModal($planJson)'><i class='fas fa-edit'></i></button>
                                                    <form method='POST' style='display:inline;' onsubmit='return confirm(\"Delete this plan?\");'>
                                                        <input type='hidden' name='action' value='delete_plan'>
                                                        <input type='hidden' name='plan_id' value='" . $plan['workout_plan_id'] . "'>
                                                        <button type='submit' class='btn btn-sm btn-outline-danger'><i class='fas fa-trash'></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                            ";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center text-muted'>No workout plans yet</td></tr>";
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

    <!-- Add Plan Modal -->
    <div class="modal fade" id="addPlanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Workout Plan</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_plan">
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
                            <div class="form-group col-md-6">
                                <label>Duration (Weeks)</label>
                                <input type="number" class="form-control" name="duration_weeks" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Difficulty</label>
                                <select class="form-control" name="difficulty_level">
                                    <option>Beginner</option>
                                    <option>Intermediate</option>
                                    <option>Advanced</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Exercises</label>
                            <textarea class="form-control" name="exercises" id="add_plan_exercises" rows="4" placeholder="List exercises separated by comma"></textarea>
                            <small class="text-muted">Type manually or select from the library below.</small>
                        </div>

                        <div class="exercise-library-section mt-3">
                            <h6 class="text-warning mb-3"><i class="fas fa-book mr-2"></i>Quick Add from Exercise Library</h6>
                            <div class="row">
                                <?php foreach($systemExercises as $sex): ?>
                                    <div class="col-3 col-md-2">
                                        <div class="ex-lib-item" onclick="addExerciseToPlan('<?php echo addslashes($sex['name']); ?>', 'add_plan_exercises')">
                                            <img src="../<?php echo htmlspecialchars($sex['media_path']); ?>" 
                                                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=200';" 
                                                 class="ex-lib-img" alt="Exercise">
                                            <div class="ex-lib-name text-truncate"><?php echo htmlspecialchars($sex['name']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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

    <!-- Edit Plan Modal -->
    <div class="modal fade" id="editPlanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Workout Plan</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_plan">
                        <input type="hidden" name="plan_id" id="edit_plan_id">
                        <div class="form-group">
                            <label>Plan Title</label>
                            <input type="text" class="form-control" name="title" id="edit_title" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="2"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Duration (Weeks)</label>
                                <input type="number" class="form-control" name="duration_weeks" id="edit_duration" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Difficulty</label>
                                <select class="form-control" name="difficulty_level" id="edit_difficulty">
                                    <option>Beginner</option>
                                    <option>Intermediate</option>
                                    <option>Advanced</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Exercises (Comma separated)</label>
                            <textarea class="form-control" name="exercises" id="edit_exercises" rows="4"></textarea>
                            <small class="text-muted">Type manually or select from the library below.</small>
                        </div>

                        <div class="exercise-library-section mt-3">
                            <h6 class="text-warning mb-3"><i class="fas fa-book mr-2"></i>Quick Add from Exercise Library</h6>
                            <div class="row">
                                <?php foreach($systemExercises as $sex): ?>
                                    <div class="col-3 col-md-2">
                                        <div class="ex-lib-item" onclick="addExerciseToPlan('<?php echo addslashes($sex['name']); ?>', 'edit_exercises')">
                                            <img src="../<?php echo htmlspecialchars($sex['media_path']); ?>" 
                                                 onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=200';" 
                                                 class="ex-lib-img" alt="Exercise">
                                            <div class="ex-lib-name text-truncate"><?php echo htmlspecialchars($sex['name']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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
        function addExerciseToPlan(name, targetId) {
            const textarea = document.getElementById(targetId);
            let currentVal = textarea.value.trim();
            
            if (currentVal === '') {
                textarea.value = name;
            } else {
                // Check if already exists to avoid duplicates
                const exercises = currentVal.split(',').map(s => s.trim());
                if (!exercises.includes(name)) {
                    textarea.value = currentVal + ', ' + name;
                }
            }
            
            // Subtle animation feedback
            textarea.classList.add('is-valid');
            setTimeout(() => textarea.classList.remove('is-valid'), 1000);
        }

        function openEditPlanModal(plan) {
            $('#edit_plan_id').val(plan.workout_plan_id);
            $('#edit_title').val(plan.title);
            $('#edit_description').val(plan.description);
            $('#edit_duration').val(plan.duration_weeks);
            $('#edit_difficulty').val(plan.difficulty_level);
            $('#edit_exercises').val(plan.exercises);
            $('#editPlanModal').modal('show');
        }
    </script>
</body>
</html>
