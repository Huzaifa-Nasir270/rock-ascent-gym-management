<?php
require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];

// Handle Endorsement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_endorsement'])) {
    $productId = (int)$_POST['product_id'];
    $reasoning = sanitize($_POST['reasoning']);

    $stmt = $conn->prepare("INSERT INTO shop_trainer_endorsements (instructor_id, product_id, reasoning) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE reasoning = VALUES(reasoning)");
    $stmt->bind_param("iis", $instructorId, $productId, $reasoning);
    $stmt->execute();
    redirect('endorse_products.php', 'Endorsement saved!', 'success');
}

// Get All Products for selection
$products = $conn->query("SELECT * FROM shop_products WHERE status = 'Active' ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Get Instructor's Current Endorsements
$stmt = $conn->prepare("SELECT e.*, p.name as product_name FROM shop_trainer_endorsements e JOIN shop_products p ON e.product_id = p.product_id WHERE e.instructor_id = ?");
$stmt->bind_param("i", $instructorId);
$stmt->execute();
$endorsements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Endorse Products - Instructor</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
<?php include('../includes/header.php'); ?>
<div class="container-fluid px-4"><div class="dashboard-container">
<?php include('../includes/instructor_sidebar.php'); ?>
<div class="main-content">
    
    <div class="mb-4">
        <h2>🏅 Recommend Products</h2>
        <p class="text-muted">Share your professional expertise by recommending supplements to your students.</p>
    </div>

    <?php displayMessage(); ?>

    <div class="row">
        <div class="col-md-5">
            <div class="card p-4" style="border-radius: 20px; background: rgba(15, 23, 42, 0.4);">
                <h5>Add New Endorsement</h5>
                <form method="POST">
                    <input type="hidden" name="add_endorsement" value="1">
                    <div class="form-group">
                        <label>Select Product</label>
                        <select name="product_id" class="form-control" required>
                            <option value="">-- Choose Product --</option>
                            <?php foreach($products as $p): ?>
                                <option value="<?php echo $p['product_id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Why do you recommend this?</label>
                        <textarea name="reasoning" class="form-control" rows="4" required placeholder="e.g., This protein has a high absorption rate, ideal for post-workout recovery."></textarea>
                    </div>
                    <button class="btn btn-warning btn-block">Save Recommendation</button>
                </form>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card p-4" style="border-radius: 20px; background: rgba(15, 23, 42, 0.4);">
                <h5>Your Current Endorsements</h5>
                <div class="table-responsive">
                    <table class="table text-white">
                        <thead><tr><th>Product</th><th>Reasoning</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php if(empty($endorsements)): ?>
                                <tr><td colspan="3" class="text-center text-muted">You haven't recommended any products yet.</td></tr>
                            <?php else: foreach($endorsements as $e): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($e['product_name']); ?></strong></td>
                                    <td><small><?php echo htmlspecialchars($e['reasoning']); ?></small></td>
                                    <td><a href="?delete=<?php echo $e['endorsement_id']; ?>" class="text-danger"><i class="fas fa-trash"></i></a></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div></div></div>
<?php include('../includes/footer.php'); ?>
<?php include('../includes/scripts.php'); ?>
</body>
</html>
