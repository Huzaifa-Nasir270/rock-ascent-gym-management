<?php
require_once('config/functions.php');
require_once('config/fitness_plans.php');

$userId = 36; // Ali
$user = getUserDetails($userId);
$userGoal = $user['fitness_goal'] ?? 'Stay Fit';

echo "User: " . $user['name'] . " | Goal: " . $userGoal . "\n";

$plans = $conn->query("
    SELECT wp.*, i.name as instructor_name 
    FROM workout_plans wp 
    LEFT JOIN instructors i ON wp.instructor_id = i.instructor_id 
    WHERE wp.user_id = $userId 
    ORDER BY wp.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

echo "Total raw plans: " . count($plans) . "\n";

$validPlans = array_filter($plans, function($p) {
    return !empty(trim($p['exercises'] ?? ''));
});

echo "Valid plans: " . count($validPlans) . "\n";

$smartWorkoutPlan = null;
if (empty($validPlans)) {
    $allPlans = getFitnessPlans();
    $goal = isset($allPlans[$userGoal]) ? $userGoal : 'Stay Fit';
    $smartWorkoutPlan = $allPlans[$goal];
    echo "Using SMART PLAN: " . $goal . "\n";
    echo "Exercises count: " . count($smartWorkoutPlan['workout']) . "\n";
} else {
    echo "Using ASSIGNED PLAN\n";
}
?>
