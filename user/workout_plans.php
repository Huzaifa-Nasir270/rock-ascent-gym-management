<?php
/**
 * User - Workout Plans
 */

require_once('../config/functions.php');
require_once('../config/fitness_plans.php');
requireUser();

$userId = $_SESSION['user_id'];
$user = getUserDetails($userId);
$userId = $_SESSION['user_id'];
$user = getUserDetails($userId);

$userGoal = $user['fitness_goal'] ?? 'Stay Fit';

$plans = $conn->query("
    SELECT wp.*, i.name as instructor_name 
    FROM workout_plans wp 
    LEFT JOIN instructors i ON wp.instructor_id = i.instructor_id 
    WHERE wp.user_id = $userId 
    ORDER BY wp.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Filter out plans with no exercises to prevent empty display for old records
$validPlans = array_filter($plans, function($p) {
    return !empty(trim($p['exercises'] ?? ''));
});

// If no instructor plans or all are empty, show the Smart recommendation
$smartWorkoutPlan = null;
if (empty($validPlans)) {
    $allPlans = getFitnessPlans();
    $goal = isset($allPlans[$userGoal]) ? $userGoal : 'Stay Fit';
    $smartWorkoutPlan = $allPlans[$goal];
}

// Use validPlans for rendering
$plans = $validPlans;

// Fetch exercise library for intelligent media matching
$libQ = $conn->query("SELECT * FROM exercises");
$exercisesLibrary = [];
while ($row = $libQ->fetch_assoc()) {
    $exercisesLibrary[$row['name']] = $row;
}

$weightGainImages = [
    'bench press' => '../assets/images/Weight Gain/bench press.png',
    'chest fly' => '../assets/images/Weight Gain/Chest Fly.png',
    'tricep pushdown' => '../assets/images/Weight Gain/Tricep Pushdown.png',
    'bicep curl' => '../assets/images/Weight Gain/Bicep Curl.png',
    'hammer curl' => '../assets/images/Weight Gain/Hammer Curl.png',
    'lat pulldown' => '../assets/images/Weight Gain/Lat Pulldown.png',
    'seated row' => '../assets/images/Weight Gain/Seated Row.png'
];

$weightLossImages = [
    'bodyweight squats' => '../assets/images/Weight Loss/Bodyweight Squats.png',
    'planks' => '../assets/images/Weight Loss/Planks.png',
    'push ups' => '../assets/images/Weight Loss/Push Ups.png',
    'tricep dips' => '../assets/images/Weight Loss/Tricep Dips.png'
];

$stayFitImages = [
    'deadlift' => '../assets/images/Stay Fit/Deadlift.png',
    'pull ups' => '../assets/images/Stay Fit/Pull Ups.png',
    'shoulder press' => '../assets/images/Stay Fit/Shoulder Press.png',
    'tricep pushdown' => '../assets/images/Stay Fit/Tricep Pushdown.png'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Workout Plans - Member</title>
    <?php include('../includes/head.php'); ?>
    <style>
        .exercise-card-premium:hover {
            transform: translateY(-12px) scale(1.02) !important;
            border-color: var(--primary-color) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7) !important;
        }

        .exercise-card-premium:hover .exercise-thumb {
            transform: scale(1.1);
        }

        .overlay-zoom {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: 0.3s;
            cursor: pointer;
        }

        .exercise-card-premium:hover .overlay-zoom {
            opacity: 1;
        }

        .overlay-zoom i {
            font-size: 2rem;
            color: white;
            transform: scale(0.5);
            transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .exercise-card-premium:hover .overlay-zoom i {
            transform: scale(1);
        }

        .font-weight-black {
            font-weight: 900;
        }

        .uppercase {
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/user_sidebar.php'); ?>

            <div class="main-content">
                <!-- Dynamic Header Logic -->
                <?php
                $latestPlanTitle = 'Professional Program';
                if (!empty($plans)) {
                    $latestPlanTitle = $plans[0]['title'];
                } elseif ($smartWorkoutPlan) {
                    $latestPlanTitle = $userGoal . " Program";
                }

                $isMuscleBuilder = (stripos($latestPlanTitle, 'Muscle') !== false || stripos($latestPlanTitle, 'Bulking') !== false || stripos($latestPlanTitle, 'Gain') !== false);
                $isFatLoss = (stripos($latestPlanTitle, 'Fat') !== false || stripos($latestPlanTitle, 'Weight') !== false || stripos($latestPlanTitle, 'Loss') !== false || stripos($latestPlanTitle, 'Cardio') !== false);
                ?>

                <div class="welcome-section mb-5 fade-in-up">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h1 class="display-4 font-weight-bold mb-2">💪 Workout <span
                                    style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Plans</span>
                            </h1>
                            <p class="text-muted" style="font-size: 1.2rem; font-weight: 500;">Precision training
                                programs designed for your specific goals.</p>
                        </div>
                        <div class="d-none d-md-block">
                            <div class="glass-panel p-3 text-center" style="border-radius: 20px; min-width: 150px;">
                                <div class="text-primary font-weight-bold"
                                    style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px;">Current
                                    Goal</div>
                                <div class="h4 mb-0 text-warning"><?php echo htmlspecialchars($latestPlanTitle); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php displayMessage(); ?>

                <!-- Intelligent Plan Logic Explorer (Dynamic Plan Basis) -->
                <?php if (!empty($plans) || $smartWorkoutPlan): ?>
                    <div class="card mb-5 fade-in-up"
                        style="background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.8) 100%); border: 1px solid rgba(245, 158, 11, 0.2) !important;">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center d-none d-md-block">
                                    <i
                                        class="fas fa-<?php echo $isMuscleBuilder ? 'microchip' : ($isFatLoss ? 'fire' : 'dumbbell'); ?> fa-3x text-warning opacity-75"></i>
                                </div>
                                <div class="col-md-11">
                                    <h5 class="text-warning mb-2"><i class="fas fa-info-circle mr-2"></i> Understanding Your
                                        <?php echo htmlspecialchars($latestPlanTitle); ?>
                                    </h5>
                                    <p class="mb-0 text-light opacity-75" style="line-height: 1.6;">
                                        <?php if ($isMuscleBuilder): ?>
                                            This plan is architected on the principle of <strong>Hypertrophy-Specific Training
                                                (HST)</strong>. It has been assigned to you because your profile indicates an
                                            intermediate experience level with a primary focus on skeletal muscle development.
                                        <?php elseif ($isFatLoss): ?>
                                            This plan utilizes <strong>High-Intensity Interval Training (HIIT)</strong> and
                                            metabolic conditioning to maximize caloric expenditure. It is designed to preserve
                                            lean muscle mass while significantly accelerating fat oxidation.
                                        <?php else: ?>
                                            This customized program has been tailored by your instructor based on your specific
                                            fitness metrics. It focuses on functional strength, mobility, and progressive
                                            overload to ensure consistent growth.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <h3 class="font-weight-bold mb-4"><i class="fas fa-calendar-check text-primary mr-2"></i>Assigned
                        Workout Programs</h3>
                </div>

                <?php
                if (!empty($plans)) {
                    foreach ($plans as $plan) {
                        // ... existing rendering for DB plans ...
                        echo '
                        <div class="card mb-5 shadow-lg border-0 fade-in-up" style="background: var(--card-glass); border-radius: 30px; overflow: hidden;">
                            <div class="card-header border-0 d-flex flex-wrap justify-content-between align-items-center p-4 pb-0" style="background: transparent;">
                                <div>
                                <div class="d-flex flex-wrap align-items-baseline">
                                    <h2 class="font-weight-black mb-1 mr-3" style="letter-spacing: -1px; color: #fff;">' . htmlspecialchars($plan['title'] ?? 'Untitled Plan') . '</h2>
                                </div>
                                <div class="d-flex flex-wrap align-items-center mt-2" style="gap: 10px;">
                                    <span class="badge badge-primary px-3 py-2" style="background: rgba(var(--primary-rgb), 0.1); border: 1px solid var(--primary); color: #fff; font-weight: 700;"><i class="fas fa-user-tie mr-2"></i>Assigned by Instructor: ' . htmlspecialchars($plan['instructor_name'] ?? 'Professional Staff') . '</span>
                                    <span class="badge badge-warning px-3 py-2">' . $plan['difficulty_level'] . '</span>
                                    <span class="badge badge-info px-3 py-2"><i class="far fa-clock mr-1"></i> ' . $plan['duration_weeks'] . ' Weeks</span>
                                </div>
                                </div>
                                <div class="text-md-right mt-3 mt-md-0">
                                    <div class="text-muted small mb-1 uppercase font-weight-bold" style="letter-spacing: 1px;">Assignment Date</div>
                                    <div class="text-light font-weight-bold">' . formatDate($plan['created_at']) . '</div>
                                </div>
                            </div>
                            
                            <div class="card-body p-4">
                                <div class="glass-panel mb-4 p-3" style="background: rgba(255,255,255,0.03); border-radius: 20px;">
                                    <p class="mb-0 text-muted" style="font-size: 1.1rem; font-style: italic; border-left: 4px solid var(--primary-color); padding-left: 15px;">
                                        "' . htmlspecialchars($plan['description'] ?? '') . '"
                                    </p>
                                </div>
                                
                                <h5 class="mb-4 text-primary font-weight-bold"><i class="fas fa-dumbbell mr-2"></i>Training Schedule & Exercises</h5>
                                <div class="row">';

                        $exerciseList = explode(',', $plan['exercises'] ?? '');
                        foreach ($exerciseList as $exercise) {
                            $exName = trim($exercise);
                            if (empty($exName))
                                continue;

                            // Smart Image Mapping: Priority to new Weight Gain assets
                            $media = $exercisesLibrary[$exName] ?? null;
                            $imgPath = '';
                            $key = strtolower($exName);

                            if (isset($weightGainImages[$key])) {
                                $candidate = $weightGainImages[$key];
                                if (file_exists(str_replace('../', 'C:/xampp/htdocs/gym_management/', $candidate))) {
                                    $imgPath = $candidate;
                                }
                            } elseif (isset($weightLossImages[$key])) {
                                $candidate = $weightLossImages[$key];
                                if (file_exists(str_replace('../', 'C:/xampp/htdocs/gym_management/', $candidate))) {
                                    $imgPath = $candidate;
                                }
                            } elseif (isset($stayFitImages[$key])) {
                                $candidate = $stayFitImages[$key];
                                if (file_exists(str_replace('../', 'C:/xampp/htdocs/gym_management/', $candidate))) {
                                    $imgPath = $candidate;
                                }
                            }

                            // 1. Multi-location Check
                            if (empty($imgPath)) {
                                $checkPaths = [
                                    "../assets/images/",
                                    "../assets/images/Weight Gain/",
                                    "../assets/images/Weight Loss/",
                                    "../assets/images/Stay Fit/",
                                    "../assets/images/exercises/"
                                ];

                                foreach ($checkPaths as $path) {
                                    $variants = [
                                        $path . $exName . ".png",
                                        $path . strtolower($exName) . ".png",
                                        $path . ucwords(strtolower($exName)) . ".png",
                                        $path . str_replace(' ', '_', $exName) . ".png"
                                    ];

                                    foreach ($variants as $v) {
                                        $fullPath = str_replace('../', 'C:/xampp/htdocs/gym_management/', $v);
                                        if (file_exists($fullPath)) {
                                            $imgPath = $v;
                                            break 2;
                                        }
                                    }
                                }
                            }

                            // 2. Fallback to Exercise Library
                            if (empty($imgPath) && $media && !empty($media['media_path'])) {
                                $imgPath = htmlspecialchars($media['media_path']);
                            }

                            // 3. Absolute Fallback
                            if (empty($imgPath)) {
                                $imgPath = 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1200';
                            }

                            // Special Handling for Preview Guide (Actual Exercise Image)
                            $previewPath = $imgPath;
                            
                            // Override Thumbnail for UI Consistency (Muscular Athlete)
                            $imgPath = '../assets/images/weight_loss_thumb.png';

                            if (stripos($exName, 'Bench Press') !== false) {
                                $previewPath = '../assets/images/Weight Gain/bench_press_guide.png';
                            }

                            echo '
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="exercise-card-premium" style="background: rgba(15, 23, 42, 0.8); border-radius: 24px; overflow: hidden; height: 100%; border: 1px solid rgba(255,255,255,0.08); transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                                                <div style="position: relative; overflow: hidden;">
                                                    <img src="' . $imgPath . '" 
                                                         onerror="this.onerror=null;this.src=\'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1200\'"
                                                         onclick="previewImage(\'' . $previewPath . '\', \'\'' . addslashes(htmlspecialchars($exName)) . '\')"
                                                         class="w-100 exercise-thumb" style="height: 180px; object-fit: cover; transition: 0.5s;" alt="Exercise">
                                                    <div class="overlay-zoom" onclick="previewImage(\'' . $previewPath . '\', \'' . addslashes(htmlspecialchars($exName)) . '\')">
                                                        <i class="fas fa-search-plus"></i>
                                                    </div>
                                                </div>
                                                <div class="p-4">
                                                    <h5 class="mb-2 font-weight-bold text-white">' . htmlspecialchars($exName) . '</h5>
                                                    <p class="small text-muted mb-0" style="line-height: 1.5; font-size: 0.9rem;">' . ($media ? htmlspecialchars(substr($media['description'], 0, 90)) . '...' : 'Optimized movement pattern for targeting specific muscle groups and improving metabolic efficiency.') . '</p>
                                                    <div class="mt-3 d-flex justify-content-between align-items-center">
                                                        <span class="text-warning small font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Form Focus</span>
                                                        <a href="#" onclick="previewImage(\'' . $previewPath . '\', \'' . addslashes(htmlspecialchars($exName)) . '\')" class="btn btn-sm btn-link p-0 text-primary" style="font-size: 0.8rem; text-decoration: none;">View Guide <i class="fas fa-arrow-right ml-1"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>';
                        }

                        echo '</div>
                            </div>
                        </div>';
                    }
                } elseif ($smartWorkoutPlan) {
                    // Render the Smart Recommendation but label as Instructor Assigned
                    echo '
                    <div class="card mb-5 shadow-lg border-0 fade-in-up" style="background: var(--card-glass); border-radius: 30px; overflow: hidden; border-top: 5px solid ' . $smartWorkoutPlan['color'] . ' !important;">
                        <div class="card-header border-0 d-flex flex-wrap justify-content-between align-items-center p-4" style="background: transparent;">
                            <div>
                                <h2 class="font-weight-black mb-1" style="color: ' . $smartWorkoutPlan['color'] . ';">💪 ' . $userGoal . ' Program</h2>
                                <span class="badge badge-pill badge-primary py-2 px-3"><i class="fas fa-user-tie mr-2"></i>Assigned by Instructor: Professional Coach</span>
                            </div>
                            <div class="text-right">
                                <div class="badge badge-outline-primary">' . count($smartWorkoutPlan['workout']) . ' Exercises • ' . $userGoal . '</div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">';
                    foreach ($smartWorkoutPlan['workout'] as $ex) {
                        $exName = trim($ex['name'] ?? '');
                        if (empty($exName)) {
                            continue;
                        }

                        $imgPath = '';
                        $previewPath = '';
                        $key = strtolower($exName);

                        if (isset($weightGainImages[$key])) {
                            $candidate = $weightGainImages[$key];
                            if (file_exists(str_replace('../', 'C:/xampp/htdocs/gym_management/', $candidate))) {
                                $imgPath = $candidate;
                            }
                        } elseif (isset($weightLossImages[$key])) {
                            $candidate = $weightLossImages[$key];
                            if (file_exists(str_replace('../', 'C:/xampp/htdocs/gym_management/', $candidate))) {
                                $imgPath = $candidate;
                            }
                        } elseif (isset($stayFitImages[$key])) {
                            $candidate = $stayFitImages[$key];
                            if (file_exists(str_replace('../', 'C:/xampp/htdocs/gym_management/', $candidate))) {
                                $imgPath = $candidate;
                            }
                        }

                        if (empty($imgPath)) {
                            $checkPaths = [
                                "../assets/images/",
                                "../assets/images/Weight Gain/",
                                "../assets/images/Weight Loss/",
                                "../assets/images/Stay Fit/",
                                "../assets/images/exercises/"
                            ];

                            foreach ($checkPaths as $path) {
                                $variants = [
                                    $path . $exName . ".png",
                                    $path . strtolower($exName) . ".png",
                                    $path . ucwords(strtolower($exName)) . ".png",
                                    $path . str_replace(' ', '_', $exName) . ".png",
                                    $path . ($ex['img'] ?? '')
                                ];

                                foreach ($variants as $v) {
                                    if (empty($v)) {
                                        continue;
                                    }
                                    $fullPath = str_replace('../', 'C:/xampp/htdocs/gym_management/', $v);
                                    if (file_exists($fullPath)) {
                                        $imgPath = $v;
                                        break 2;
                                    }
                                }
                            }
                        }

                        if (empty($imgPath)) {
                            $imgPath = 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1200';
                        }

                        // Special Handling for Preview Guide (Actual Exercise Image)
                        $previewPath = $imgPath;

                        // Override Thumbnail for UI Consistency (Muscular Athlete)
                        $imgPath = '../assets/images/weight_loss_thumb.png';

                        if (stripos($exName, 'Bench Press') !== false) {
                            $previewPath = '../assets/images/Weight Gain/bench_press_guide.png';
                        }

                        echo '
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="exercise-card-premium" style="background: rgba(15, 23, 42, 0.8); border-radius: 24px; overflow: hidden; height: 100%; border: 1px solid rgba(255,255,255,0.08); transition: all 0.4s ease;">
                                        <div style="position: relative; overflow: hidden;">
                                            <img src="' . $imgPath . '" 
                                                 onerror="this.onerror=null;this.src=\'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=1200\'"
                                                 onclick="previewImage(\'' . $previewPath . '\', \'\'' . addslashes(htmlspecialchars($exName)) . '\')" 
                                                 class="w-100 exercise-thumb" style="height: 200px; object-fit: cover; transition: 0.5s;" alt="' . $exName . '">
                                            <div class="overlay-zoom" onclick="previewImage(\'' . $previewPath . '\', \'' . addslashes(htmlspecialchars($exName)) . '\')">
                                                <i class="fas fa-search-plus"></i>
                                            </div>
                                        </div>
                                        <div class="p-4">
                                            <h5 class="mb-2 font-weight-bold text-white">' . $exName . '</h5>
                                            <p class="small text-muted mb-0" style="line-height: 1.5;">' . $ex['desc'] . '</p>
                                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                                <span class="text-warning small font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Form Focus</span>
                                                <button onclick="previewImage(\'' . $previewPath . '\', \'' . addslashes(htmlspecialchars($exName)) . '\')" class="btn btn-sm btn-link p-0 text-primary" style="font-size: 0.8rem; text-decoration: none;">View Guide <i class="fas fa-arrow-right ml-1"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>';
                    }
                    echo '</div>
                        </div>
                    </div>';
                } else {
                    echo '<div class="glass-panel text-center py-5 fade-in-up">
                        <div class="mb-4">
                            <i class="fas fa-dumbbell fa-4x text-muted opacity-20"></i>
                        </div>
                        <h4 class="text-muted">No Workout Plans Found</h4>
                        <p class="text-muted mx-auto" style="max-width: 400px;">Your instructor hasn\'t assigned a customized training program yet.</p>
                        <a href="chat.php" class="btn btn-primary mt-3"><i class="fas fa-comments mr-2"></i>Contact Instructor</a>
                    </div>';
                }
                ?>

                <!-- Section Removed to avoid confusion with assigned plans -->
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade" id="imagePreviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content"
                style="background: rgba(10, 16, 35, 0.98); border: 1px solid rgba(255,255,255,0.1); border-radius: 30px; box-shadow: 0 40px 100px rgba(0,0,0,0.9);">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h4 class="modal-title font-weight-black" id="previewTitle"
                        style="color: var(--primary-color); letter-spacing: -0.5px;"></h4>
                    <button type="button" class="close text-white opacity-50" data-dismiss="modal"
                        style="font-size: 1.5rem;"><span>&times;</span></button>
                </div>
                <div class="modal-body text-center p-4">
                    <img src="" id="previewImg" class="img-fluid" alt="Preview"
                        style="border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); max-height: 70vh; object-fit: contain;">
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <p class="text-muted small mx-auto mb-0"><i class="fas fa-shield-alt mr-2"></i> Professional
                        training visual aid from your instructor.</p>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
    <script>
        function previewImage(url, title) {
            $('#previewImg').attr('src', url);
            if (title) $('#previewTitle').text(title);
            $('#imagePreviewModal').modal('show');
        }
    </script>
</body>

</html>