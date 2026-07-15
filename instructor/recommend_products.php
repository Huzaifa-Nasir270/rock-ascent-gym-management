<?php
/**
 * Instructor - Recommend Products
 */

require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_recommendation') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $userIdStr = sanitize($_POST['user_id'] ?? '');
        $message = sanitize($_POST['message'] ?? '');
        
        $userId = ($userIdStr === 'all' || empty($userIdStr)) ? NULL : (int)$userIdStr;

        if ($productId > 0) {
            $query = "INSERT INTO instructor_product_recommendations (instructor_id, user_id, product_id, message) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iiis", $instructorId, $userId, $productId, $message);
            
            if ($stmt->execute()) {
                redirect('recommend_products.php', 'Recommendation added successfully!', 'success');
            } else {
                redirect('recommend_products.php', 'Failed to add recommendation.', 'danger');
            }
        }
    } elseif ($action === 'delete_recommendation') {
        $id = (int)($_POST['id'] ?? 0);
        
        $query = "DELETE FROM instructor_product_recommendations WHERE id = ? AND instructor_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $id, $instructorId);
        
        if ($stmt->execute()) {
            redirect('recommend_products.php', 'Recommendation removed.', 'success');
        } else {
            redirect('recommend_products.php', 'Failed to remove recommendation.', 'danger');
        }
    }
}

// Fetch assigned members for the dropdown
$membersQuery = "SELECT u.user_id, u.name 
                 FROM user_instructor_assignments uia 
                 JOIN users u ON uia.user_id = u.user_id 
                 WHERE uia.instructor_id = ? AND uia.status = 'Active'";
$stmt = $conn->prepare($membersQuery);
$stmt->bind_param("i", $instructorId);
$stmt->execute();
$assignedMembers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch all active products
$products = $conn->query("SELECT product_id, name, price FROM shop_products WHERE status = 'Active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Fetch existing recommendations
$recQuery = "SELECT r.*, p.name as product_name, u.name as user_name 
             FROM instructor_product_recommendations r
             JOIN shop_products p ON r.product_id = p.product_id
             LEFT JOIN users u ON r.user_id = u.user_id
             WHERE r.instructor_id = ?
             ORDER BY r.created_at DESC";
$stmt = $conn->prepare($recQuery);
$stmt->bind_param("i", $instructorId);
$stmt->execute();
$recommendations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Recommend Products - Instructor</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/instructor_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">⭐ Recommend Products</h2>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addRecModal">
                        <i class="fas fa-plus mr-2"></i> New Recommendation
                    </button>
                </div>

                <?php displayMessage(); ?>

                <div class="card glass-panel fade-in-up">
                    <div class="card-header border-0 pb-0 bg-transparent">
                        <h5 class="mb-0">Your Active Recommendations</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Target User</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recommendations)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="fas fa-box-open mb-3" style="font-size: 3rem; opacity: 0.5;"></i><br>
                                                You haven't recommended any products yet.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recommendations as $rec): ?>
                                            <tr>
                                                <td class="font-weight-bold text-primary">
                                                    <?php echo htmlspecialchars($rec['product_name']); ?>
                                                </td>
                                                <td>
                                                    <?php if (empty($rec['user_id'])): ?>
                                                        <span class="badge badge-info">All My Members</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-success"><?php echo htmlspecialchars($rec['user_name']); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-muted small" style="max-width: 250px;">
                                                    <?php echo htmlspecialchars($rec['message'] ?: 'No message provided'); ?>
                                                </td>
                                                <td class="text-muted small">
                                                    <?php echo date('d M Y', strtotime($rec['created_at'])); ?>
                                                </td>
                                                <td class="text-right">
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this recommendation?');">
                                                        <input type="hidden" name="action" value="delete_recommendation">
                                                        <input type="hidden" name="id" value="<?php echo $rec['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" style="padding: 8px 12px !important; border-radius: 10px !important;">
                                                            <i class="fas fa-trash"></i> Remove
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Recommendation Modal (FEAT-REC-001) -->
    <div class="modal fade" id="addRecModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="background: #1e293b; border: 1px solid var(--primary-color);">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-star text-warning mr-2"></i> Recommend a Product</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_recommendation">
                        
                        <div class="form-group mb-4">
                            <label class="text-muted font-weight-bold">Select Product <span class="text-danger">*</span></label>
                            <select class="form-control custom-select-dark" name="product_id" required style="height: 50px; background: #0f172a; color: white; border: 1px solid rgba(255,255,255,0.1);">
                                <option value="" style="background: #0f172a;">-- Choose a product --</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?php echo $p['product_id']; ?>" style="background: #0f172a;">
                                        <?php echo htmlspecialchars($p['name']) . " — [PKR " . number_format($p['price']) . "]"; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group mb-4">
                            <label class="text-muted font-weight-bold">Target Member</label>
                            <select class="form-control custom-select-dark" name="user_id" style="height: 50px; background: #0f172a; color: white; border: 1px solid rgba(255,255,255,0.1);">
                                <option value="all" class="text-info font-weight-bold" style="background: #0f172a;">Everyone (All My Assigned Members)</option>
                                <?php foreach ($assignedMembers as $m): ?>
                                    <option value="<?php echo $m['user_id']; ?>">
                                        <?php echo htmlspecialchars($m['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted mt-2">Select a specific member or recommend to all your members.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="text-muted font-weight-bold">Personalized Message (Optional)</label>
                            <textarea class="form-control" name="message" rows="3" placeholder="e.g., I highly recommend this protein powder to help you hit your daily macro goals!" style="background: #0f172a; color: white; border: 1px solid rgba(255,255,255,0.1);"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Recommendation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>
    <?php include('../includes/scripts.php'); ?>
</body>
</html>
