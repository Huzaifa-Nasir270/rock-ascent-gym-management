<?php
/**
 * Smart Recommendations Dashboard
 */

require_once('../config/functions.php');
require_once('../includes/recommendation_engine.php');
requireUser();

$userId = $_SESSION['user_id'];
$user = getUserDetails($userId);

// Check if profile is complete
$profileComplete = !empty($user['height']) && !empty($user['weight']) && !empty($user['age']);

if ($profileComplete) {
    $fitnessGoal = $user['fitness_goal'] ?: 'Stay Fit';
    $dietPlan = RecommendationEngine::getDietPlan($fitnessGoal, $user['weight']);
    $workoutPlan = RecommendationEngine::getWorkoutPlan($fitnessGoal);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Smart Recommendations - Gym Management</title>
    <?php include('../includes/head.php'); ?>
    <style>
        .recommendation-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }
        .recommendation-card:hover {
            transform: translateY(-5px);
        }
        .plan-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
        }
        .plan-header i {
            font-size: 2rem;
            margin-right: 15px;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .meal-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            margin-bottom: 10px;
        }
        .meal-info h6 {
            margin: 0;
            color: #fff;
            font-weight: 600;
        }
        .meal-info p {
            margin: 0;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.6);
        }
        .calories-badge {
            background: var(--primary-gradient);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            color: white;
        }
        .exercise-card {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .exercise-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .exercise-body {
            padding: 15px;
        }
        .total-calories-banner {
            background: var(--primary-gradient);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
            color: white;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/user_sidebar.php'); ?>

            <div class="main-content">
                <div class="welcome-section mb-5">
                    <h1>🧠 Smart <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Recommendations</span></h1>
                    <p class="text-muted">Personalized plans based on your profile and goal: <strong><?php echo htmlspecialchars($user['fitness_goal'] ?? 'Not Set'); ?></strong></p>
                </div>

                <?php if (!$profileComplete): ?>
                    <div class="alert alert-warning p-4 text-center" style="border-radius: 20px; background: rgba(255, 193, 7, 0.1); border: 1px solid rgba(255, 193, 7, 0.2); color: #ffc107;">
                        <i class="fas fa-exclamation-triangle mb-3" style="font-size: 3rem;"></i>
                        <h4>Profile Incomplete</h4>
                        <p>Please update your Height, Weight, and Age in your profile to get personalized recommendations.</p>
                        <a href="profile.php" class="btn btn-warning mt-2" style="border-radius: 12px; font-weight: bold;">Update Profile</a>
                    </div>
                <?php else: ?>
                    
                    <div class="total-calories-banner">
                        Daily Calorie Target: <?php echo $dietPlan['total_calories']; ?> kcal
                    </div>

                    <div class="row">
                        <!-- Diet Plan Section -->
                        <div class="col-lg-6">
                            <div class="recommendation-card">
                                <div class="plan-header">
                                    <i class="fas fa-utensils"></i>
                                    <h4 class="mb-0">Personalized Diet Plan</h4>
                                </div>
                                
                                <div class="meal-item">
                                    <div class="meal-info">
                                        <h6>🍳 Breakfast</h6>
                                        <p><?php echo htmlspecialchars($dietPlan['breakfast']['items']); ?></p>
                                    </div>
                                    <span class="calories-badge"><?php echo $dietPlan['breakfast']['calories']; ?> kcal</span>
                                </div>

                                <div class="meal-item">
                                    <div class="meal-info">
                                        <h6>🥗 Lunch</h6>
                                        <p><?php echo htmlspecialchars($dietPlan['lunch']['items']); ?></p>
                                    </div>
                                    <span class="calories-badge"><?php echo $dietPlan['lunch']['calories']; ?> kcal</span>
                                </div>

                                <div class="meal-item">
                                    <div class="meal-info">
                                        <h6>🍎 Snacks</h6>
                                        <p><?php echo htmlspecialchars($dietPlan['snacks']['items']); ?></p>
                                    </div>
                                    <span class="calories-badge"><?php echo $dietPlan['snacks']['calories']; ?> kcal</span>
                                </div>

                                <div class="meal-item">
                                    <div class="meal-info">
                                        <h6>🥩 Dinner</h6>
                                        <p><?php echo htmlspecialchars($dietPlan['dinner']['items']); ?></p>
                                    </div>
                                    <span class="calories-badge"><?php echo $dietPlan['dinner']['calories']; ?> kcal</span>
                                </div>
                            </div>
                        </div>

                        <!-- Workout Plan Section -->
                        <div class="col-lg-6">
                            <div class="recommendation-card">
                                <div class="plan-header">
                                    <i class="fas fa-dumbbell"></i>
                                    <h4 class="mb-0">Personalized Workout Plan</h4>
                                </div>
                                
                                <div class="row">
                                    <?php foreach ($workoutPlan['exercises'] as $exercise): ?>
                                        <div class="col-md-6">
                                            <div class="exercise-card">
                                                <img src="<?php echo $exercise['image']; ?>" class="exercise-img" alt="<?php echo $exercise['name']; ?>" onerror="this.src='../assets/images/exercises/placeholder.jpg'">
                                                <div class="exercise-body">
                                                    <h6 class="text-white mb-1"><?php echo htmlspecialchars($exercise['name']); ?></h6>
                                                    <p class="text-primary small font-weight-bold mb-2"><?php echo htmlspecialchars($exercise['sets_reps']); ?></p>
                                                    <p class="text-muted small mb-0"><?php echo htmlspecialchars($exercise['description']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
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
