<?php
/**
 * User - Packages & Subscription
 */

require_once('../config/functions.php');
requireUser();

$userId = $_SESSION['user_id'];

// Handle subscription
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'subscribe') {
        $packageId = sanitize($_POST['package_id'] ?? '');
        $instructorId = sanitize($_POST['instructor_id'] ?? '');
        $package = getPackageDetails($packageId);

        if ($package && !empty($instructorId)) {
            // Check if user already has an ACTIVE subscription (not just assignment)
            $activeSub = $conn->prepare("SELECT subscription_id FROM subscriptions WHERE user_id = ? AND status = 'Active' AND end_date >= CURDATE() LIMIT 1");
            $activeSub->bind_param("i", $userId);
            $activeSub->execute();
            $activeSubResult = $activeSub->get_result()->fetch_assoc();

            if ($activeSubResult) {
                redirect('packages.php', 'You already have an active subscription. Please cancel it first before subscribing to a new one.', 'warning');
            }

            // Clear any stale active instructor assignments so user can freely re-subscribe
            $clearStale = $conn->prepare("UPDATE user_instructor_assignments SET status = 'Inactive' WHERE user_id = ? AND status = 'Active'");
            $clearStale->bind_param("i", $userId);
            $clearStale->execute();

            // Also ensure the users table is cleared
            $clearUserStale = $conn->prepare("UPDATE users SET assigned_instructor_id = NULL WHERE user_id = ?");
            $clearUserStale->bind_param("i", $userId);
            $clearUserStale->execute();

            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime("+{$package['duration_months']} months"));

            // Get template
            $templateQ = $conn->query("SELECT * FROM package_templates WHERE package_id = $packageId LIMIT 1");
            $template = $templateQ->fetch_assoc();

            $conn->begin_transaction();
            try {
                // 1. Create Subscription
                $query = "INSERT INTO subscriptions (user_id, package_id, instructor_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, 'Active')";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iiiss", $userId, $packageId, $instructorId, $startDate, $endDate);
                $stmt->execute();
                $subscriptionId = $stmt->insert_id;

                // 2. Create/Reactivate Instructor Assignment (handles unique constraint)
                $assignQuery = "INSERT INTO user_instructor_assignments (user_id, instructor_id, start_date, end_date, status) 
                                VALUES (?, ?, ?, ?, 'Active')
                                ON DUPLICATE KEY UPDATE start_date = VALUES(start_date), end_date = VALUES(end_date), status = 'Active'";
                $assignStmt = $conn->prepare($assignQuery);
                $assignStmt->bind_param("iiss", $userId, $instructorId, $startDate, $endDate);
                $assignStmt->execute();

                // 3. Update User Table for quick reference
                $updateUser = $conn->prepare("UPDATE users SET assigned_instructor_id = ? WHERE user_id = ?");
                $updateUser->bind_param("ii", $instructorId, $userId);
                $updateUser->execute();

                $paymentQuery = "INSERT INTO payments (user_id, subscription_id, amount, payment_date, payment_method, status) VALUES (?, ?, ?, ?, 'Cash', 'Pending')";
                $paymentStmt = $conn->prepare($paymentQuery);
                $today = date('Y-m-d');
                $paymentStmt->bind_param("iids", $userId, $subscriptionId, $package['price'], $today);
                $paymentStmt->execute();
                $paymentId = $paymentStmt->insert_id;

                // Assign default plans
                if ($template) {
                    $wTitle = $template['workout_title'];
                    $wDesc = $template['workout_description'];
                    $wExer = $template['workout_exercises'];
                    $wq = $conn->prepare("INSERT INTO workout_plans (user_id, instructor_id, title, description, exercises) VALUES (?, ?, ?, ?, ?)");
                    $wq->bind_param("iisss", $userId, $instructorId, $wTitle, $wDesc, $wExer);
                    $wq->execute();

                    $dTitle = $template['diet_title'];
                    $dDesc = $template['diet_description'];
                    $cal = $template['calories'];
                    $pro = $template['protein'];
                    $car = $template['carbs'];
                    $fat = $template['fats'];
                    $pDet = $template['plan_details'];
                    $dq = $conn->prepare("INSERT INTO diet_plans (user_id, instructor_id, title, description, calories, protein, carbs, fats, plan_details) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $dq->bind_param("iisssiiis", $userId, $instructorId, $dTitle, $dDesc, $cal, $pro, $car, $fat, $pDet);
                    $dq->execute();
                }
                
                // Add Admin Notification table if it doesn't exist, then insert
                $conn->query("CREATE TABLE IF NOT EXISTS admin_notifications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    title VARCHAR(255),
                    message TEXT,
                    is_read ENUM('Yes', 'No') DEFAULT 'No',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )");
                
                $userName = $_SESSION['user_name'] ?? 'A member';
                $notifTitle = "New Package Subscription";
                $notifMsg = "$userName has subscribed to the {$package['name']} package. They will pay cash to the admin. Please collect " . formatCurrency($package['price']) . " and update their payment status to Paid.";
                $nStmt = $conn->prepare("INSERT INTO admin_notifications (title, message) VALUES (?, ?)");
                $nStmt->bind_param("ss", $notifTitle, $notifMsg);
                $nStmt->execute();
                
                $conn->commit();
                
                redirect('packages.php', 'Package subscribed successfully! Please pay cash to the admin to fully activate your subscription.', 'success');
            } catch (Exception $e) {
                $conn->rollback();
                redirect('packages.php', 'Failed to subscribe: ' . $e->getMessage(), 'danger');
            }
        } else {
            redirect('packages.php', 'Please select an instructor.', 'warning');
        }
    }

    if ($action === 'unsubscribe') {
        $subscriptionId = (int)($_POST['subscription_id'] ?? 0);
        if ($subscriptionId > 0) {
            $stmt = $conn->prepare("UPDATE subscriptions SET status = 'Cancelled' WHERE subscription_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $subscriptionId, $userId);
            if ($stmt->execute()) {
                // Also clear the active instructor assignment so they can choose a new one later
                $clearAssign = $conn->prepare("UPDATE user_instructor_assignments SET status = 'Inactive' WHERE user_id = ? AND status = 'Active'");
                $clearAssign->bind_param("i", $userId);
                $clearAssign->execute();
                
                // Clear assigned_instructor_id in users table
                $clearUser = $conn->prepare("UPDATE users SET assigned_instructor_id = NULL WHERE user_id = ?");
                $clearUser->bind_param("i", $userId);
                $clearUser->execute();

                redirect('packages.php', 'You have successfully unsubscribed from the package and your instructor assignment has been cleared.', 'success');
            } else {
                redirect('packages.php', 'Failed to unsubscribe. Please try again.', 'danger');
            }
        }
    }
}

// Get all packages
$packages = $conn->query("SELECT * FROM packages WHERE status = 'Active' ORDER BY price ASC")->fetch_all(MYSQLI_ASSOC);

// Get active instructors
$instructors = $conn->query("SELECT * FROM instructors WHERE status = 'Active'")->fetch_all(MYSQLI_ASSOC);

// Get user's current subscription
$currentSubscription = getUserSubscription($userId);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Packages - Member</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Unified Sidebar -->
            <?php include('../includes/user_sidebar.php'); ?>

            <div class="main-content">
                <!-- Premium Page Header -->
                <div class="welcome-section mb-5 fade-in-up">
                    <h1 class="mb-2">📦 Available <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Packages</span></h1>
                    <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Choose the perfect membership plan for your fitness journey.</p>
                </div>

                <?php displayMessage(); ?>

                <?php if ($currentSubscription) { ?>
                    <div class="alert alert-success d-flex justify-content-between align-items-center">
                        <div>
                            ✅ You have an active subscription (<?php echo htmlspecialchars($currentSubscription['package_name']); ?>) until <?php echo formatDate($currentSubscription['end_date']); ?>
                        </div>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to unsubscribe? This action cannot be undone.')">
                            <input type="hidden" name="action" value="unsubscribe">
                            <input type="hidden" name="subscription_id" value="<?php echo $currentSubscription['subscription_id']; ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm font-weight-bold" style="border-radius: 8px;">Cancel Subscription</button>
                        </form>
                    </div>
                <?php } ?>

                <div class="row">
                    <?php
                    if (!empty($packages)) {
                        foreach ($packages as $package) {
                            $isCurrentPackage = ($currentSubscription && $currentSubscription['package_id'] == $package['package_id']);
                            $features = explode(',', $package['features']);
                            ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="glass-card h-100 d-flex flex-column" style="border-radius: 24px; border: 1px solid rgba(255,255,255,0.1); overflow: hidden; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(20px);">
                                    <div class="p-4 border-0" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(236, 72, 153, 0.1) 100%);">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h4 class="mb-0 font-weight-bold text-white"><?php echo htmlspecialchars($package['name']); ?></h4>
                                            <?php if ($isCurrentPackage): ?>
                                                <span class="badge badge-success px-3 py-1" style="border-radius: 50px; font-size: 10px; text-transform: uppercase;">Current</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="p-4 flex-grow-1">
                                        <div class="d-flex align-items-baseline mb-3">
                                            <h2 class="mb-0 font-weight-bold" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo formatCurrency($package['price']); ?></h2>
                                            <span class="text-muted ml-2" style="font-size: 0.9rem;">/ <?php echo $package['duration_months']; ?> Mo</span>
                                        </div>
                                        
                                        <p class="text-muted small mb-4"><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>
                                        
                                        <div class="features-list mb-4">
                                            <h6 class="text-white font-weight-bold mb-2 small text-uppercase" style="letter-spacing: 1px;">Features</h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php foreach ($features as $feature): ?>
                                                    <span class="badge badge-dark py-1 px-2" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); font-weight: normal; font-size: 11px;">
                                                        <i class="fas fa-check text-success mr-1" style="font-size: 9px;"></i> <?php echo htmlspecialchars(trim($feature)); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!$isCurrentPackage): ?>
                                        <div class="p-4 pt-0">
                                            <form method="POST">
                                                <div class="instructor-selection-box p-3 mb-3" style="background: rgba(0,0,0,0.2); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05);">
                                                    <label class="small text-muted font-weight-bold text-uppercase mb-2 d-block" style="letter-spacing: 1px;">Choose Instructor</label>
                                                    <div class="instructor-selector-grid">
                                                        <?php foreach ($instructors as $inst): ?>
                                                            <div class="instructor-mini-card" onclick="showInstructorProfile(<?php echo htmlspecialchars(json_encode($inst)); ?>)">
                                                                <img src="<?php echo !empty($inst['profile_image']) ? "../assets/images/profiles/" . htmlspecialchars($inst['profile_image']) : "https://ui-avatars.com/api/?name=" . urlencode($inst['name']) . "&background=f59e0b&color=fff"; ?>" class="mini-avatar">
                                                                <div class="mini-info">
                                                                    <span class="mini-name"><?php echo htmlspecialchars($inst['name']); ?></span>
                                                                    <span class="mini-spec"><?php echo htmlspecialchars($inst['specialization']); ?></span>
                                                                </div>
                                                                <input type="radio" name="instructor_id" value="<?php echo $inst['instructor_id']; ?>" required class="instructor-radio">
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="action" value="subscribe">
                                                <input type="hidden" name="package_id" value="<?php echo $package['package_id']; ?>">
                                                <button type="submit" class="btn btn-primary btn-block subscribe-btn" disabled style="border-radius: 12px; font-weight: bold; background: var(--primary-gradient); border: none; padding: 12px;">Subscribe Now</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructor Profile Modal -->
    <div class="modal fade" id="profileModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="background: #0f172a; border-radius: 32px; border: 1px solid rgba(255,255,255,0.1); overflow: hidden;">
                <div class="modal-header border-0 p-0" style="height: 120px; background: linear-gradient(135deg, #f59e0b 0%, #ec4899 100%);">
                    <button type="button" class="close text-white p-4" data-dismiss="modal" style="position: absolute; right: 0; z-index: 10;">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-5 pt-0">
                    <div class="profile-header-meta text-center" style="margin-top: -60px;">
                        <img id="modalAvatar" src="" class="rounded-circle border border-white" style="width: 120px; height: 120px; object-fit: cover; background: #fff;">
                        <h2 id="modalName" class="mt-3 mb-1 font-weight-bold"></h2>
                        <p id="modalSpec" class="text-warning font-weight-bold mb-3"></p>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-7">
                            <h5 class="font-weight-bold mb-3"><i class="fas fa-user-tie text-primary mr-2"></i> Professional Bio</h5>
                            <p id="modalBio" class="text-muted" style="line-height: 1.6;"></p>
                            
                            <h5 class="font-weight-bold mt-4 mb-3"><i class="fas fa-briefcase text-primary mr-2"></i> Experience</h5>
                            <p id="modalExp" class="text-muted"></p>
                        </div>
                        <div class="col-md-5">
                            <h5 class="font-weight-bold mb-3"><i class="fas fa-certificate text-primary mr-2"></i> Certifications</h5>
                            <p id="modalCert" class="text-muted small"></p>
                            
                            <h5 class="font-weight-bold mt-4 mb-3"><i class="fas fa-star text-primary mr-2"></i> Skills</h5>
                            <div id="modalSkills" class="d-flex flex-wrap gap-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 bg-dark-subtle">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" style="border-radius: 12px;">Close</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .instructor-selector-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            max-height: 160px;
            overflow-y: auto;
            padding: 5px;
        }
        .instructor-selector-grid::-webkit-scrollbar {
            width: 4px;
        }
        .instructor-selector-grid::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
        }
        .instructor-selector-grid::-webkit-scrollbar-thumb {
            background: rgba(245, 158, 11, 0.3);
            border-radius: 10px;
        }
        .instructor-mini-card {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        .instructor-mini-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(245, 158, 11, 0.3);
            transform: translateX(5px);
        }
        .instructor-mini-card.selected {
            background: rgba(245, 158, 11, 0.1);
            border-color: #f59e0b;
        }
        .mini-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            object-fit: cover;
            margin-right: 15px;
        }
        .mini-info {
            flex: 1;
        }
        .mini-name {
            display: block;
            font-weight: 700;
            color: #e2e8f0;
            font-size: 14px;
        }
        .mini-spec {
            display: block;
            font-size: 11px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .instructor-radio {
            position: absolute;
            right: 15px;
            accent-color: #f59e0b;
            transform: scale(1.2);
        }
        .skill-badge {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            margin-right: 8px;
            margin-bottom: 8px;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }
    </style>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
    <script>
        function showInstructorProfile(instructor) {
            $('#modalName').text(instructor.name);
            $('#modalSpec').text(instructor.specialization || 'Professional Instructor');
            $('#modalBio').text(instructor.bio || 'This instructor hasn\'t provided a bio yet.');
            $('#modalExp').text(instructor.experience || 'Experience details not provided.');
            $('#modalCert').text(instructor.certification || 'No specific certifications listed.');
            
            // Set avatar
            const avatarUrl = instructor.profile_image 
                ? '../assets/images/profiles/' + instructor.profile_image 
                : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(instructor.name) + '&background=f59e0b&color=fff';
            $('#modalAvatar').attr('src', avatarUrl);
            
            // Handle skills
            const skillsContainer = $('#modalSkills');
            skillsContainer.empty();
            if (instructor.skills) {
                const skillsArray = instructor.skills.split(',');
                skillsArray.forEach(skill => {
                    skillsContainer.append(`<span class="skill-badge">${skill.trim()}</span>`);
                });
            } else {
                skillsContainer.append('<span class="text-muted small italic">Skills not listed.</span>');
            }
            
            $('#profileModal').modal('show');
        }

        // Selection logic for mini cards
        $(document).on('click', '.instructor-mini-card', function(e) {
            // Prevent modal opening from stopping radio selection
            const radio = $(this).find('input[type="radio"]');
            radio.prop('checked', true);
            
            $('.instructor-mini-card').removeClass('selected');
            $(this).addClass('selected');
            
            // Enable subscribe button in the same form
            $(this).closest('form').find('.subscribe-btn').prop('disabled', false);
        });
    </script>
</body>
</html>
