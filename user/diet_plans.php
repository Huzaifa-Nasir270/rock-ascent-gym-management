<?php
/**
 * User - Diet Plans
 */

require_once('../config/functions.php');
require_once('../config/fitness_plans.php');
requireUser();

$userId = $_SESSION['user_id'];

// Add is_active column if it doesn't exist (safe migration)
$conn->query("ALTER TABLE diet_plans ADD COLUMN IF NOT EXISTS is_active TINYINT(1) NOT NULL DEFAULT 1");
$conn->query("UPDATE diet_plans SET is_active = 1 WHERE is_active IS NULL");

// Get only the single LATEST active diet plan for this user (failsafe: only 1 max)
$plans = $conn->query("
    SELECT dp.*, i.name as instructor_name
    FROM diet_plans dp
    LEFT JOIN instructors i ON dp.instructor_id = i.instructor_id
    WHERE dp.user_id = $userId AND dp.is_active = 1
    ORDER BY dp.created_at DESC
    LIMIT 1
")->fetch_all(MYSQLI_ASSOC);

// Filter out empty plans
$validPlans = array_filter($plans, function($p) {
    return !empty(trim($p['plan_details'] ?? ''));
});
$plans = $validPlans;

// Get archived (old) plan history
$historyPlans = $conn->query("
    SELECT dp.*, i.name as instructor_name
    FROM diet_plans dp
    LEFT JOIN instructors i ON dp.instructor_id = i.instructor_id
    WHERE dp.user_id = $userId AND dp.is_active = 0
    ORDER BY dp.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Diet Plans - Member</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/user_sidebar.php'); ?>

            <div class="main-content">
                <!-- Premium Page Header -->
                <div class="welcome-section mb-5 fade-in-up">
                    <h1 class="mb-2">🥗 My <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Diet Plans</span></h1>
                    <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Fuel your body with professional nutrition guides tailored for you.</p>
                </div>

                <?php displayMessage(); ?>

                <div class="row">
                    <div class="col-12">
                        <?php
                        if (!empty($plans)) {
                            foreach ($plans as $plan) {
                                // ... existing DB plan rendering ...
                                echo '
                                <div class="card mb-4 fade-in-up" style="background: rgba(30, 41, 59, 0.4); border-radius: 24px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-4">
                                            <div>
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge badge-success mr-2" style="border-radius:8px; font-size:11px;">✔ Active Plan</span>
                                                </div>
                                                <h3 class="text-warning mb-1" style="font-weight: 800;">' . htmlspecialchars($plan['title']) . '</h3>
                                                <p class="text-muted mb-0">' . htmlspecialchars($plan['description']) . '</p>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-primary px-3 py-2" style="border-radius: 10px;">Macro Balanced</span><br>
                                                <small class="text-muted mt-1 d-block">By: ' . htmlspecialchars($plan['instructor_name'] ?? 'Instructor') . '</small>
                                            </div>
                                        <!-- Macro Summary -->
                                        <div class="row mb-4">
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="p-3 text-center" style="background: rgba(245, 158, 11, 0.1); border-radius: 20px; border: 1px solid rgba(245, 158, 11, 0.1);">
                                                    <div class="small text-muted font-weight-bold mb-1">CALORIES</div>
                                                    <h4 class="mb-0 text-warning font-weight-bold">' . ($plan['calories'] ?? 0) . '</h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="p-3 text-center" style="background: rgba(236, 72, 153, 0.1); border-radius: 20px; border: 1px solid rgba(236, 72, 153, 0.1);">
                                                    <div class="small text-muted font-weight-bold mb-1">PROTEIN</div>
                                                    <h4 class="mb-0 text-danger font-weight-bold">' . ($plan['protein'] ?? 0) . 'g</h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="p-3 text-center" style="background: rgba(96, 165, 250, 0.1); border-radius: 20px; border: 1px solid rgba(96, 165, 250, 0.1);">
                                                    <div class="small text-muted font-weight-bold mb-1">CARBS</div>
                                                    <h4 class="mb-0 text-primary font-weight-bold">' . ($plan['carbs'] ?? 0) . 'g</h4>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6 mb-3">
                                                <div class="p-3 text-center" style="background: rgba(34, 197, 94, 0.1); border-radius: 20px; border: 1px solid rgba(34, 197, 94, 0.1);">
                                                    <div class="small text-muted font-weight-bold mb-1">FATS</div>
                                                    <h4 class="mb-0 text-success font-weight-bold">' . ($plan['fats'] ?? 0) . 'g</h4>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="p-4" style="background: rgba(0,0,0,0.2); border-radius: 20px;">
                                            <h6 class="text-info mb-3"><i class="fas fa-utensils mr-2"></i>Daily Meal Plan Details</h6>
                                            <p class="text-light mb-0" style="white-space: pre-wrap; line-height: 1.8;">' . nl2br(htmlspecialchars($plan['plan_details'] ?? '')) . '</p>
                                        </div>
                                        <div class="mt-4 d-flex justify-content-between align-items-center">
                                            <small class="text-muted"><i class="fas fa-calendar mr-1"></i>Assigned: ' . formatDate($plan['created_at']) . '</small>
                                        </div>
                                     </div>
                                 </div>
                                 ';
                            }
                        } else {
                            // Smart Recommendation Logic
                            $user = getUserDetails($userId);
                            $userGoal = $user['fitness_goal'] ?? 'Stay Fit';
                            $allPlans = getFitnessPlans();
                            $goal = isset($allPlans[$userGoal]) ? $userGoal : 'Stay Fit';
                            $smartPlan = $allPlans[$goal];
                            
                            echo '
                            <div class="card mb-4 fade-in-up" style="background: rgba(30, 41, 59, 0.4); border-radius: 24px; overflow: hidden; border-top: 5px solid ' . $smartPlan['color'] . ' !important;">
                                <div class="row no-gutters">
                                    <div class="col-md-4">
                                        <img src="../assets/images/' . $smartPlan['image'] . '" class="w-100 h-100" style="object-fit: cover; min-height: 250px;" alt="' . $userGoal . '">
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <div>
                                                    <h3 style="color: ' . $smartPlan['color'] . '; font-weight: 800;">✨ Smart ' . $userGoal . ' Diet</h3>
                                                    <p class="text-muted mb-0">' . $smartPlan['focus'] . '</p>
                                                </div>
                                                <span class="badge" style="background: ' . $smartPlan['color'] . '20; color: ' . $smartPlan['color'] . ';">Recommended</span>
                                            </div>
                                            
                                            <div class="row">';
                                            foreach ($smartPlan['diet'] as $meal) {
                                                echo '
                                                <div class="col-md-6 mb-3">
                                                    <div class="p-3 d-flex align-items-center" style="background: rgba(255,255,255,0.03); border-radius: 15px; border: 1px solid rgba(255,255,255,0.05);">
                                                        <div class="mr-3 text-center" style="width: 40px;">
                                                            <i class="fas fa-' . $meal['icon'] . ' fa-lg" style="color: ' . $smartPlan['color'] . ';"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="font-weight-bold mb-0" style="color: ' . $smartPlan['color'] . ';">' . $meal['title'] . '</h6>
                                                            <p class="small mb-0 text-light opacity-75">' . $meal['desc'] . '</p>
                                                        </div>
                                                    </div>
                                                </div>';
                                            }
                                            echo '</div>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                        }
                        ?>

                        <?php if (!empty($historyPlans)): ?>
                        <div class="card mt-2" style="background: rgba(30,41,59,0.3); border-radius:20px; border:1px solid rgba(255,255,255,0.05);">
                            <div class="card-header" style="background:transparent; border-bottom:1px solid rgba(255,255,255,0.05);">
                                <button class="btn btn-link text-muted p-0" type="button" data-toggle="collapse" data-target="#dietHistoryCollapse" style="font-size:14px;">
                                    <i class="fas fa-history mr-2"></i>View Plan History (<?php echo count($historyPlans); ?> archived plans)
                                </button>
                            </div>
                            <div id="dietHistoryCollapse" class="collapse">
                                <div class="card-body p-3">
                                    <p class="text-muted small mb-3">These are your previous diet plans that have been replaced by newer ones.</p>
                                    <?php foreach ($historyPlans as $hp): ?>
                                    <div class="p-3 mb-3" style="background:rgba(0,0,0,0.2); border-radius:16px; opacity:0.75; border-left:3px solid rgba(255,193,7,0.3);">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 text-muted"><?php echo htmlspecialchars($hp['title']); ?></h6>
                                            <small class="text-muted"><?php echo formatDate($hp['created_at']); ?></small>
                                        </div>
                                        <div class="d-flex gap-3" style="gap:15px;">
                                            <small class="text-muted">🔥 <?php echo $hp['calories']; ?> cal</small>
                                            <small class="text-muted">💪 <?php echo $hp['protein']; ?>g protein</small>
                                            <small class="text-muted">🌾 <?php echo $hp['carbs']; ?>g carbs</small>
                                            <small class="text-muted">🥑 <?php echo $hp['fats']; ?>g fats</small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
</body>
</html>
