<?php
require_once('../config/functions.php');
require_once('../config/RecommendationService.php');
requireUser();

$userId = $_SESSION['user_id'];
$recService = new RecommendationService($conn);

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $goal = sanitize($_POST['fitness_goal']);
    $weight = (float)$_POST['weight'];
    $height = (float)$_POST['height']; // should be in cm
    
    // Auto-correction: If user enters height in meters (e.g. 1.7) instead of cm (170)
    if ($height < 3) {
        $height = $height * 100;
    }

    $bmi = 0;
    if ($height > 0) {
        $heightM = $height / 100;
        $bmi = $weight / ($heightM * $heightM);
    }

    $stmt = $conn->prepare("INSERT INTO user_fitness_stats (user_id, weight, height, bmi, fitness_goal) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iddds", $userId, $weight, $height, $bmi, $goal);
    $stmt->execute();
    redirect('personalized_shop.php', 'Fitness profile updated! Check your new recommendations.', 'success');
}

// Get User Stats
$stmt = $conn->prepare("SELECT * FROM user_fitness_stats WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Get Recommendations
$recommendations = $recService->getPersonalizedRecommendations($userId, 6);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Personalized Shop - Gym Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=3.2">
    <style>
        .rec-card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .rec-card:hover {
            transform: translateY(-10px);
            border-color: #f59e0b;
        }
        .bmi-badge {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body class="dark-theme">
<?php include('../includes/header.php'); ?>
<div class="container-fluid px-4"><div class="dashboard-container">
<?php include('../includes/user_sidebar.php'); ?>
<div class="main-content">
    
    <div class="row mb-5">
        <div class="col-lg-8">
            <h2 class="mb-2">✨ Suggested for You</h2>
            <p class="text-muted">AI-powered recommendations based on your fitness journey.</p>
        </div>
        <div class="col-lg-4 text-right">
            <?php if ($profile): ?>
                <div class="bmi-badge">
                    Current BMI: <?php echo number_format($profile['bmi'], 1); ?> 
                    (<?php echo $profile['fitness_goal']; ?>)
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php displayMessage(); ?>

    <!-- Recommendations Grid -->
    <div class="row">
        <?php if (empty($recommendations)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-store mb-3" style="font-size: 48px; color: #64748b;"></i>
                <p>The shop is currently being stocked with smart suggestions. Please check back later!</p>
            </div>
        <?php else: foreach ($recommendations as $p): 
            $price = $recService->getDiscountedPrice($p['price'], $userId);
        ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card rec-card h-100 border-0">
                    <div class="position-relative">
                        <img src="../assets/images/shop/<?php echo $p['image'] ?: 'default.webp'; ?>" class="card-img-top" style="height: 220px; object-fit: cover;">
                        <div class="badge badge-warning position-absolute" style="top: 15px; right: 15px; padding: 8px 12px; border-radius: 10px; font-weight: 800; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
                            AI Suggestion
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title font-weight-bold mb-1"><?php echo htmlspecialchars($p['name']); ?></h5>
                        <div class="text-warning small mb-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <span class="ml-1 text-muted">(Premium Choice)</span>
                        </div>
                        
                        <!-- AI Reasoning Section (The "Why") -->
                        <div class="ai-insight-box mb-4 p-3" style="background: rgba(245, 158, 11, 0.08); border-left: 3px solid #f59e0b; border-radius: 0 12px 12px 0;">
                            <h6 class="mb-1 text-warning small font-weight-bold"><i class="fas fa-robot mr-1"></i> AI INSIGHT</h6>
                            <p class="mb-0 text-light" style="font-size: 0.85rem; line-height: 1.5; opacity: 0.9;">
                                <?php echo $p['ai_reasoning']; ?>
                            </p>
                        </div>

                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <?php if ($price < $p['price']): ?>
                                        <span class="text-muted small" style="text-decoration: line-through;">PKR <?php echo number_format($p['price'], 0); ?></span><br>
                                    <?php endif; ?>
                                    <strong class="text-warning" style="font-size: 1.4rem;">PKR <?php echo number_format($price, 0); ?></strong>
                                </div>
                                <a href="cart_action.php?add=<?php echo $p['product_id']; ?>" class="btn btn-primary px-4 py-2" style="border-radius: 12px; font-weight: 700;">Add to Cart</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- Update Profile Section -->
    <div class="card mt-5" style="border-radius: 20px; background: rgba(15, 23, 42, 0.4);">
        <div class="card-body p-4">
            <h4><i class="fas fa-user-edit mr-2 text-warning"></i>Update Fitness Profile</h4>
            <p class="text-muted small">We use this data to improve our product suggestions for you.</p>
            <form method="POST">
                <input type="hidden" name="update_profile" value="1">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Goal</label>
                            <select name="fitness_goal" class="form-control">
                                <option value="Muscle Gain" <?php echo ($profile && $profile['fitness_goal'] === 'Muscle Gain') ? 'selected' : ''; ?>>Muscle Gain</option>
                                <option value="Fat Loss" <?php echo ($profile && $profile['fitness_goal'] === 'Fat Loss') ? 'selected' : ''; ?>>Fat Loss</option>
                                <option value="Endurance" <?php echo ($profile && $profile['fitness_goal'] === 'Endurance') ? 'selected' : ''; ?>>Endurance</option>
                                <option value="General Fitness" <?php echo ($profile && $profile['fitness_goal'] === 'General Fitness') ? 'selected' : ''; ?>>General Fitness</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group"><label>Weight (kg)</label><input type="number" name="weight" step="0.1" class="form-control" value="<?php echo $profile['weight'] ?? ''; ?>" required></div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group"><label>Height (cm)</label><input type="number" name="height" step="0.1" class="form-control" value="<?php echo $profile['height'] ?? ''; ?>" required></div>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button class="btn btn-warning btn-block">Update Profile</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div></div></div>
<?php include('../includes/footer.php'); ?>
</body>
</html>
