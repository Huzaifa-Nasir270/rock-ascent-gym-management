<?php
require_once('../config/functions.php');
requireAdmin();

$uploadDir = '../assets/images/shop/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Delete product
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $p = $conn->prepare("SELECT image FROM shop_products WHERE product_id=?");
    $p->bind_param("i", $id); $p->execute();
    $row = $p->get_result()->fetch_assoc();
    if ($row && $row['image'] && file_exists($uploadDir . $row['image'])) unlink($uploadDir . $row['image']);
    $conn->prepare("DELETE FROM shop_products WHERE product_id=?")->bind_param("i",$id);
    $stmt = $conn->prepare("DELETE FROM shop_products WHERE product_id=?");
    $stmt->bind_param("i",$id); $stmt->execute();
    redirect('shop_products.php','Product deleted.','success');
}

// Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['name'] ?? '');
    $catId   = (int)$_POST['category_id'];
    $desc    = sanitize($_POST['description'] ?? '');
    $price   = (float)$_POST['price'];
    $stock   = (int)$_POST['stock_quantity'];
    $status  = sanitize($_POST['status'] ?? 'Active');
    $image   = sanitize($_POST['existing_image'] ?? '');

    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $image = 'prod_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
        }
    }
    $exclusive = isset($_POST['is_exclusive']) ? 1 : 0;
    if (isset($_POST['add_product'])) {
        $stmt = $conn->prepare("INSERT INTO shop_products (category_id,name,description,price,stock_quantity,image,status,is_exclusive) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("issdissi", $catId,$name,$desc,$price,$stock,$image,$status,$exclusive);
        $stmt->execute();
        $newProductId = $stmt->insert_id;

        if (!empty($_POST['goals'])) {
            foreach ($_POST['goals'] as $goal) {
                $stmtGoal = $conn->prepare("INSERT INTO shop_product_goals (product_id, goal_type) VALUES (?, ?)");
                $stmtGoal->bind_param("is", $newProductId, $goal);
                $stmtGoal->execute();
            }
        }
        redirect('shop_products.php','Product added!','success');
    } elseif (isset($_POST['edit_product'])) {
        $id = (int)$_POST['product_id'];
        $stmt = $conn->prepare("UPDATE shop_products SET category_id=?,name=?,description=?,price=?,stock_quantity=?,image=?,status=?,is_exclusive=? WHERE product_id=?");
        $stmt->bind_param("issdissii",$catId,$name,$desc,$price,$stock,$image,$status,$exclusive,$id);
        $stmt->execute();

        $conn->query("DELETE FROM shop_product_goals WHERE product_id = $id");
        if (!empty($_POST['goals'])) {
            foreach ($_POST['goals'] as $goal) {
                $stmtGoal = $conn->prepare("INSERT INTO shop_product_goals (product_id, goal_type) VALUES (?, ?)");
                $stmtGoal->bind_param("is", $id, $goal);
                $stmtGoal->execute();
            }
        }
        redirect('shop_products.php','Product updated!','success');
    }
}

$cats     = $conn->query("SELECT * FROM shop_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("SELECT p.*,c.name as cat_name FROM shop_products p LEFT JOIN shop_categories c ON p.category_id=c.category_id ORDER BY p.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shop Products - Admin</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
<?php include('../includes/header.php'); ?>
<div class="container-fluid px-4"><div class="dashboard-container">
<?php include('../includes/admin_sidebar.php'); ?>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-box-open mr-2" style="color:#f59e0b"></i>Shop Products</h2>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">
            <i class="fas fa-plus mr-1"></i>Add Product
        </button>
    </div>
    <?php displayMessage(); ?>
    <div class="card">
        <div class="card-header"><h5 class="mb-0">All Products (<?php echo count($products); ?>)</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No products yet. Add your first product!</td></tr>
                <?php else: foreach ($products as $p):
                    $imgSrc = !empty($p['image']) ? '../assets/images/shop/'.htmlspecialchars($p['image']) : '';
                    $fallback = "https://placehold.co/60x60/1e293b/f59e0b?text=IMG";
                ?>
                <tr>
                    <td><img src="<?php echo $imgSrc ?: $fallback; ?>" width="55" height="55" style="border-radius:10px;object-fit:cover;" onerror="this.src='<?php echo $fallback; ?>';this.onerror=null;"></td>
                    <td><strong><?php echo htmlspecialchars($p['name']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars(substr($p['description'],0,50)); ?>...</small></td>
                    <td><span class="badge badge-info"><?php echo htmlspecialchars($p['cat_name']??'N/A'); ?></span></td>
                    <td><strong>PKR <?php echo number_format($p['price'],0); ?></strong></td>
                    <td>
                        <span class="badge <?php echo $p['stock_quantity']<=5?'badge-danger':($p['stock_quantity']<=20?'badge-warning':'badge-success'); ?>">
                            <?php echo $p['stock_quantity']; ?>
                        </span>
                    </td>
                    <td><span class="badge <?php echo $p['status']==='Active'?'badge-success':'badge-secondary'; ?>"><?php echo $p['status']; ?></span></td>
                    <td>
                        <?php
                        $pg = $conn->query("SELECT goal_type FROM shop_product_goals WHERE product_id = {$p['product_id']}")->fetch_all(MYSQLI_ASSOC);
                        $goalList = array_column($pg, 'goal_type');
                        ?>
                        <button class="btn btn-sm btn-warning" onclick='editProduct(<?php echo json_encode(array_merge($p, ["goals" => $goalList])); ?>)'><i class="fas fa-edit"></i></button>
                        <a href="?delete=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div></div></div>

<!-- Add Product Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
<div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Add Product</h5><button class="close text-white" data-dismiss="modal">&times;</button></div>
    <form method="POST" enctype="multipart/form-data">
    <div class="modal-body">
        <input type="hidden" name="add_product" value="1">
        <div class="row">
            <div class="col-md-6"><div class="form-group"><label>Product Name *</label><input type="text" name="name" class="form-control" required></div></div>
            <div class="col-md-6"><div class="form-group"><label>Category *</label>
                <select name="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php foreach ($cats as $c): ?><option value="<?php echo $c['category_id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?>
                </select>
            </div></div>
            <div class="col-md-6"><div class="form-group"><label>Price (PKR) *</label><input type="number" name="price" step="0.01" class="form-control" required></div></div>
            <div class="col-md-6"><div class="form-group"><label>Stock Quantity *</label><input type="number" name="stock_quantity" class="form-control" required></div></div>
            <div class="col-md-6"><div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div></div>
            <div class="col-md-6"><div class="form-group"><label>Product Image</label><input type="file" name="image" class="form-control-file" accept="image/*"></div></div>
            <div class="col-md-6">
                <div class="form-group pt-4">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="is_exclusive" class="custom-control-input" id="addExclusive">
                        <label class="custom-control-label" for="addExclusive">Exclusive to Gold/Premium Members</label>
                    </div>
                </div>
            </div>
            <div class="col-12"><div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"></textarea></div></div>
            <div class="col-12">
                <div class="form-group">
                    <label>Fitness Goals (AI Recommendations)</label><br>
                    <div class="btn-group-toggle" data-toggle="buttons">
                        <?php foreach(['Muscle Gain', 'Fat Loss', 'Endurance', 'Recovery', 'Energy'] as $g): ?>
                        <label class="btn btn-outline-info btn-sm mb-2 mr-2">
                            <input type="checkbox" name="goals[]" value="<?php echo $g; ?>"> <?php echo $g; ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancel</button><button class="btn btn-primary">Add Product</button></div>
    </form>
</div></div></div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
<div class="modal-dialog modal-lg"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Edit Product</h5><button class="close text-white" data-dismiss="modal">&times;</button></div>
    <form method="POST" enctype="multipart/form-data">
    <div class="modal-body">
        <input type="hidden" name="edit_product" value="1">
        <input type="hidden" name="product_id" id="e_id">
        <input type="hidden" name="existing_image" id="e_img">
        <div class="row">
            <div class="col-md-6"><div class="form-group"><label>Product Name *</label><input type="text" name="name" id="e_name" class="form-control" required></div></div>
            <div class="col-md-6"><div class="form-group"><label>Category *</label>
                <select name="category_id" id="e_cat" class="form-control">
                    <?php foreach ($cats as $c): ?><option value="<?php echo $c['category_id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?>
                </select>
            </div></div>
            <div class="col-md-6"><div class="form-group"><label>Price (PKR)</label><input type="number" name="price" id="e_price" step="0.01" class="form-control"></div></div>
            <div class="col-md-6"><div class="form-group"><label>Stock</label><input type="number" name="stock_quantity" id="e_stock" class="form-control"></div></div>
            <div class="col-md-6"><div class="form-group"><label>Status</label><select name="status" id="e_status" class="form-control"><option value="Active">Active</option><option value="Inactive">Inactive</option></select></div></div>
            <div class="col-md-6"><div class="form-group"><label>New Image (optional)</label><input type="file" name="image" class="form-control-file" accept="image/*"></div></div>
            <div class="col-md-6">
                <div class="form-group pt-4">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="is_exclusive" class="custom-control-input" id="e_exclusive">
                        <label class="custom-control-label" for="e_exclusive">Exclusive to Gold/Premium Members</label>
                    </div>
                </div>
            </div>
            <div class="col-12"><div class="form-group"><label>Description</label><textarea name="description" id="e_desc" class="form-control" rows="3"></textarea></div></div>
            <div class="col-12">
                <div class="form-group">
                    <label>Fitness Goals (AI Recommendations)</label><br>
                    <div id="edit_goals_container">
                        <?php foreach(['Muscle Gain', 'Fat Loss', 'Endurance', 'Recovery', 'Energy'] as $g): ?>
                        <div class="custom-control custom-checkbox custom-control-inline">
                            <input type="checkbox" name="goals[]" value="<?php echo $g; ?>" class="custom-control-input goal-check" id="edit_goal_<?php echo str_replace(' ', '_', $g); ?>">
                            <label class="custom-control-label" for="edit_goal_<?php echo str_replace(' ', '_', $g); ?>"><?php echo $g; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer"><button class="btn btn-secondary" data-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Changes</button></div>
    </form>
</div></div></div>

<?php include('../includes/footer.php'); ?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include('../includes/scripts.php'); ?>
<script>
function editProduct(p) {
    document.getElementById('e_id').value    = p.product_id;
    document.getElementById('e_name').value  = p.name;
    document.getElementById('e_cat').value   = p.category_id;
    document.getElementById('e_price').value = p.price;
    document.getElementById('e_stock').value = p.stock_quantity;
    document.getElementById('e_status').value= p.status;
    document.getElementById('e_desc').value  = p.description;
    document.getElementById('e_img').value   = p.image;
    document.getElementById('e_exclusive').checked = p.is_exclusive == 1;
    
    // Reset and set goal checkboxes
    document.querySelectorAll('.goal-check').forEach(cb => cb.checked = false);
    if (p.goals) {
        p.goals.forEach(goal => {
            let id = 'edit_goal_' + goal.replace(' ', '_');
            let el = document.getElementById(id);
            if (el) el.checked = true;
        });
    }
    $('#editModal').modal('show');
}
</script>
</body></html>
