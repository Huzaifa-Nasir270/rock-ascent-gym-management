<?php
require_once('../config/functions.php');
requireUser();

$userId = $_SESSION['user_id'];

// Handle Actions (Pause/Cancel/Resume)
if (isset($_GET['action']) && isset($_GET['sub_id'])) {
    $subId = (int)$_GET['sub_id'];
    $newStatus = '';
    
    if ($_GET['action'] === 'pause') $newStatus = 'Paused';
    elseif ($_GET['action'] === 'cancel') $newStatus = 'Cancelled';
    elseif ($_GET['action'] === 'resume') $newStatus = 'Active';

    if ($newStatus) {
        $stmt = $conn->prepare("UPDATE shop_subscriptions SET status = ? WHERE subscription_id = ? AND user_id = ?");
        $stmt->bind_param("sii", $newStatus, $subId, $userId);
        $stmt->execute();
        redirect('subscription_dashboard.php', 'Subscription updated successfully.', 'success');
    }
}

// Get Active Subscriptions
$stmt = $conn->prepare("SELECT * FROM shop_subscriptions WHERE user_id = ? AND status != 'Cancelled' ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$subscriptions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Subscription Management - Gym Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=3.2">
    <style>
        .sub-card {
            background: rgba(30, 41, 59, 0.4);
            border-radius: 24px;
            border: 1px solid rgba(255,255,255,0.1);
            padding: 30px;
            transition: all 0.3s ease;
        }
        .sub-card:hover { border-color: #f59e0b; }
        .billing-badge {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            padding: 5px 15px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="dark-theme">
<?php include('../includes/header.php'); ?>
<div class="container-fluid px-4"><div class="dashboard-container">
<?php include('../includes/user_sidebar.php'); ?>
<div class="main-content">
    
    <div class="mb-4">
        <h2>📦 My Subscription Boxes</h2>
        <p class="text-muted">Manage your recurring supplement and accessory bundles.</p>
    </div>

    <?php displayMessage(); ?>

    <div class="row">
        <?php if (empty($subscriptions)): ?>
            <div class="col-12 text-center py-5">
                <div class="card p-5" style="background:rgba(255,255,255,0.05); border-radius:20px;">
                    <i class="fas fa-box-open mb-3" style="font-size: 48px; color: #64748b;"></i>
                    <h4>No Active Subscriptions</h4>
                    <p class="text-muted">Explore our curated monthly boxes to save more on your essentials.</p>
                    <a href="shop.php" class="btn btn-primary mt-3">Browse Shop</a>
                </div>
            </div>
        <?php else: foreach ($subscriptions as $sub): ?>
            <div class="col-md-6 mb-4">
                <div class="sub-card">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h4 class="mb-1"><?php echo htmlspecialchars($sub['bundle_name']); ?></h4>
                            <span class="badge <?php echo $sub['status']==='Active'?'badge-success':'badge-warning'; ?>">
                                <?php echo $sub['status']; ?>
                            </span>
                        </div>
                        <div class="text-right">
                            <h5 class="text-warning mb-0">PKR <?php echo number_format($sub['price_per_month'], 0); ?></h5>
                            <small class="text-muted">/ month</small>
                        </div>
                    </div>

                    <div class="billing-badge mb-4">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Next Billing Date: <?php echo date('F d, Y', strtotime($sub['next_billing_date'])); ?>
                    </div>

                    <div class="d-flex gap-2">
                        <?php if ($sub['status'] === 'Active'): ?>
                            <a href="?action=pause&sub_id=<?php echo $sub['subscription_id']; ?>" class="btn btn-outline-warning btn-sm mr-2" onclick="return confirm('Pause this subscription?')">
                                <i class="fas fa-pause mr-1"></i> Pause
                            </a>
                        <?php else: ?>
                            <a href="?action=resume&sub_id=<?php echo $sub['subscription_id']; ?>" class="btn btn-outline-success btn-sm mr-2">
                                <i class="fas fa-play mr-1"></i> Resume
                            </a>
                        <?php endif; ?>
                        
                        <a href="?action=cancel&sub_id=<?php echo $sub['subscription_id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to cancel? This cannot be undone.')">
                            <i class="fas fa-times mr-1"></i> Cancel Subscription
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <!-- Featured Subscriptions -->
    <div class="mt-5">
        <h3 class="mb-4">Recommended Boxes</h3>
        <div class="row">
            <div class="col-md-4">
                <div class="card p-4 h-100" style="background: linear-gradient(135deg, rgba(30,41,59,0.8), rgba(15,23,42,0.8)); border-radius: 20px;">
                    <div class="badge badge-primary mb-3" style="width: fit-content;">Most Popular</div>
                    <h5>Elite Performance Box</h5>
                    <p class="small text-muted">Includes Premium Whey, Pre-workout, and Multivitamins.</p>
                    <div class="h4 text-warning mt-auto">PKR 12,500 <small class="text-muted" style="font-size: 0.6em;">/mo</small></div>
                    <button class="btn btn-primary btn-block mt-3">Subscribe Now</button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card p-4 h-100" style="background: rgba(30,41,59,0.4); border-radius: 20px;">
                    <h5>Lean Shred Box</h5>
                    <p class="small text-muted">Fat Burner, BCAA, and High-Fiber Protein bars.</p>
                    <div class="h4 text-warning mt-auto">PKR 9,800 <small class="text-muted" style="font-size: 0.6em;">/mo</small></div>
                    <button class="btn btn-outline-primary btn-block mt-3">Subscribe Now</button>
                </div>
            </div>
        </div>
    </div>

</div></div></div>
<?php include('../includes/footer.php'); ?>
</body>
</html>
