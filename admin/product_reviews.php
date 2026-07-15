<?php
require_once('../config/functions.php');
requireAdmin();

$reviews = $conn->query("
    SELECT r.*, p.name as product_name, u.name as user_name 
    FROM shop_product_reviews r 
    JOIN shop_products p ON r.product_id = p.product_id 
    JOIN users u ON r.user_id = u.user_id 
    ORDER BY r.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM shop_product_reviews WHERE review_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    redirect('product_reviews.php', 'Review deleted.', 'success');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Product Reviews - Admin</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
<?php include('../includes/header.php'); ?>
<div class="container-fluid px-4"><div class="dashboard-container">
<?php include('../includes/admin_sidebar.php'); ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-star mr-2" style="color:#f59e0b"></i>Product Reviews</h2>
    </div>
    <?php displayMessage(); ?>
    <div class="card">
        <div class="card-header"><h5 class="mb-0">All Reviews (<?php echo count($reviews); ?>)</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>User</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($reviews)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No reviews yet.</td></tr>
                <?php else: foreach ($reviews as $r): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($r['product_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($r['user_name']); ?></td>
                    <td>
                        <?php for($i=1; $i<=5; $i++): ?>
                            <i class="fas fa-star <?php echo $i<=$r['rating']?'text-warning':'text-muted'; ?>"></i>
                        <?php endfor; ?>
                    </td>
                    <td><small><?php echo htmlspecialchars($r['comment']); ?></small></td>
                    <td><?php echo formatDate($r['created_at']); ?></td>
                    <td>
                        <a href="?delete=<?php echo $r['review_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this review?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div></div></div>
<?php include('../includes/footer.php'); ?>
<?php include('../includes/scripts.php'); ?>
</body></html>
