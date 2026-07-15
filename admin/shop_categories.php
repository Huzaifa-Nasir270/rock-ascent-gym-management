<?php
require_once('../config/functions.php');
requireAdmin();

// Handle Add/Edit/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $desc = sanitize($_POST['description'] ?? '');
    
    if (isset($_POST['add_category']) && $name) {
        $stmt = $conn->prepare("INSERT INTO shop_categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $desc);
        $stmt->execute();
        redirect('shop_categories.php', 'Category added successfully!', 'success');
    }
    if (isset($_POST['edit_category'])) {
        $id = (int)$_POST['category_id'];
        $stmt = $conn->prepare("UPDATE shop_categories SET name=?, description=? WHERE category_id=?");
        $stmt->bind_param("ssi", $name, $desc, $id);
        $stmt->execute();
        redirect('shop_categories.php', 'Category updated!', 'success');
    }
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Check if products exist
    $check = $conn->prepare("SELECT COUNT(*) FROM shop_products WHERE category_id=?");
    $check->bind_param("i", $id);
    $check->execute();
    $count = $check->get_result()->fetch_row()[0];
    if ($count > 0) {
        redirect('shop_categories.php', "Cannot delete: $count products exist in this category.", 'danger');
    }
    $conn->prepare("DELETE FROM shop_categories WHERE category_id=?")->bind_param("i",$id);
    $conn->prepare("DELETE FROM shop_categories WHERE category_id=?")->execute();
    $stmt = $conn->prepare("DELETE FROM shop_categories WHERE category_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    redirect('shop_categories.php', 'Category deleted.', 'success');
}

$categories = $conn->query("SELECT c.*, COUNT(p.product_id) as product_count FROM shop_categories c LEFT JOIN shop_products p ON c.category_id=p.category_id GROUP BY c.category_id ORDER BY c.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shop Categories - Admin</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
<?php include('../includes/header.php'); ?>
<div class="container-fluid px-4"><div class="dashboard-container">
<?php include('../includes/admin_sidebar.php'); ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tags mr-2" style="color:#f59e0b"></i>Shop Categories</h2>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">
            <i class="fas fa-plus mr-1"></i> Add Category
        </button>
    </div>
    <?php displayMessage(); ?>
    <div class="card">
        <div class="card-header"><h5 class="mb-0">All Categories (<?php echo count($categories); ?>)</h5></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead><tr><th>#</th><th>Name</th><th>Description</th><th>Products</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">No categories yet.</td></tr>
                <?php else: foreach ($categories as $c): ?>
                <tr>
                    <td><?php echo $c['category_id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($c['description'] ?? '-'); ?></td>
                    <td><span class="badge badge-primary"><?php echo $c['product_count']; ?> products</span></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editCategory(<?php echo $c['category_id']; ?>,'<?php echo addslashes($c['name']); ?>','<?php echo addslashes($c['description']); ?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?delete=<?php echo $c['category_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div></div></div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Add Category</h5><button class="close text-white" data-dismiss="modal">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="add_category" value="1">
                <div class="form-group"><label>Name *</label><input type="text" name="name" class="form-control" required></div>
                <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Add Category</button>
            </div>
        </form>
    </div></div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Edit Category</h5><button class="close text-white" data-dismiss="modal">&times;</button></div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="edit_category" value="1">
                <input type="hidden" name="category_id" id="edit_id">
                <div class="form-group"><label>Name *</label><input type="text" name="name" id="edit_name" class="form-control" required></div>
                <div class="form-group"><label>Description</label><textarea name="description" id="edit_desc" class="form-control" rows="3"></textarea></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div></div>
</div>

<?php include('../includes/footer.php'); ?>
<?php include('../includes/scripts.php'); ?>
<script>
function editCategory(id, name, desc) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_desc').value = desc;
    $('#editModal').modal('show');
}
</script>
</body></html>
