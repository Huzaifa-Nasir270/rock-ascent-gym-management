<?php
/**
 * Manage Packages - Admin Panel
 */

require_once('../config/functions.php');
requireAdmin();

// Handle package actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_package') {
        $name = sanitize($_POST['name'] ?? '');
        $description = $_POST['description'] ?? '';
        
        // Clean description: remove literal '\r\n' strings and slashes but keep actual newlines
        $description = str_replace(['\r\n', '\n', '\r', '\\'], "\n", $description);
        $description = sanitize(trim($description));
        $price = sanitize($_POST['price'] ?? '');
        $duration = sanitize($_POST['duration_months'] ?? '');
        $features = $_POST['features'] ?? '';
        
        // Clean features: replace actual newlines and literal '\r\n' strings with comma, clean up slashes
        $features = str_replace(["\r\n", "\n", "\r", '\r\n', '\n', '\r', '\\'], ',', $features);
        $features = preg_replace('/,+/', ',', $features);
        $features = sanitize(trim($features, ','));

        $query = "INSERT INTO packages (name, description, price, duration_months, features) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdis", $name, $description, $price, $duration, $features);

        if ($stmt->execute()) {
            redirect('packages.php', 'Package added successfully', 'success');
        }
    } elseif ($action === 'edit_package') {
        $packageId = intval($_POST['package_id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $description = $_POST['description'] ?? '';
        
        // Clean description: remove literal '\r\n' strings and slashes but keep actual newlines
        $description = str_replace(['\r\n', '\n', '\r', '\\'], "\n", $description);
        $description = sanitize(trim($description));
        $price = sanitize($_POST['price'] ?? '');
        $duration = sanitize($_POST['duration_months'] ?? '');
        $features = $_POST['features'] ?? '';
        
        // Clean features: replace actual newlines and literal '\r\n' strings with comma, clean up slashes
        $features = str_replace(["\r\n", "\n", "\r", '\r\n', '\n', '\r', '\\'], ',', $features);
        $features = preg_replace('/,+/', ',', $features);
        $features = sanitize(trim($features, ','));

        $query = "UPDATE packages SET name = ?, description = ?, price = ?, duration_months = ?, features = ? WHERE package_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssdisi", $name, $description, $price, $duration, $features, $packageId);

        if ($stmt->execute()) {
            redirect('packages.php', 'Package updated successfully', 'success');
        }
    } elseif ($action === 'delete_package') {
        $packageId = sanitize($_POST['package_id'] ?? '');
        $query = "DELETE FROM packages WHERE package_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $packageId);
        if ($stmt->execute()) {
            redirect('packages.php', 'Package deleted', 'success');
        }
    }
}

// Get all packages
$packages = $conn->query("SELECT * FROM packages ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Packages - Admin</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/admin_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">📦 Manage Packages</h2>
                    <button class="btn btn-primary px-4" data-toggle="modal" data-target="#addPackageModal" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-plus mr-2"></i> Add New Package
                    </button>
                </div>

                <?php displayMessage(); ?>

                <div class="row">
                    <?php
                    if (!empty($packages)) {
                        foreach ($packages as $package) {
                            $featuresArr = explode(',', $package['features']);
                            ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="glass-card h-100 d-flex flex-column" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 24px; transition: transform 0.3s; overflow: hidden;">
                                    <div class="p-4 border-0" style="background: rgba(255,255,255,0.03);">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0 font-weight-bold text-white"><?php echo htmlspecialchars($package['name']); ?></h5>
                                            <span class="badge badge-primary px-2 py-1" style="border-radius: 50px; font-size: 10px;"><?php echo $package['duration_months']; ?> MO</span>
                                        </div>
                                    </div>
                                    
                                    <div class="p-4 flex-grow-1">
                                        <div class="d-flex align-items-baseline mb-3">
                                            <h3 class="font-weight-bold mb-0" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo formatCurrency($package['price']); ?></h3>
                                        </div>
                                        
                                        <p class="text-muted small mb-3" style="line-height: 1.5;"><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>
                                        
                                        <div class="mb-4">
                                            <h6 class="text-white font-weight-bold mb-2 small text-uppercase" style="letter-spacing: 1px; font-size: 10px;">Features</h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php foreach ($featuresArr as $f): 
                                                    if (trim($f)): ?>
                                                        <span class="badge badge-dark py-1 px-2" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); font-weight: normal; font-size: 10px;">
                                                            <i class="fas fa-check text-success mr-1" style="font-size: 8px;"></i> <?php echo htmlspecialchars(trim($f)); ?>
                                                        </span>
                                                    <?php endif; 
                                                endforeach; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-4 pt-0 d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-light flex-grow-1" style="border-radius: 10px; border: 1px solid rgba(255,255,255,0.1); font-size: 12px; padding: 8px;" onclick="editPackage(<?php echo htmlspecialchars(json_encode($package)); ?>)">
                                            <i class="fas fa-edit mr-1"></i> Edit
                                        </button>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this package?');" class="flex-grow-1">
                                            <input type="hidden" name="action" value="delete_package">
                                            <input type="hidden" name="package_id" value="<?php echo $package['package_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100" style="border-radius: 10px; border: 1px solid rgba(239, 68, 68, 0.3); font-size: 12px; padding: 8px;">
                                                <i class="fas fa-trash mr-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<div class='col-12'><p class='text-muted text-center'>No packages found</p></div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Package Modal -->
    <div class="modal fade" id="addPackageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">Add New Package</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_package">
                        <div class="form-group">
                            <label>Package Name</label>
                            <input type="text" class="form-control" name="name" placeholder="e.g. Platinum Membership" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" rows="2" placeholder="Brief overview of the package"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Price (PKR)</label>
                            <input type="number" class="form-control" name="price" placeholder="Enter amount in PKR" required>
                        </div>
                        <div class="form-group">
                            <label>Duration (Months)</label>
                            <input type="number" class="form-control" name="duration_months" value="1" required>
                        </div>
                        <div class="form-group">
                            <label>Features (comma-separated)</label>
                            <textarea class="form-control" name="features" rows="3" placeholder="e.g., Gym Access, Locker, Personal Trainer"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Package Modal -->
    <div class="modal fade" id="editPackageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">Edit Package</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_package">
                        <input type="hidden" name="package_id" id="edit_package_id">
                        <div class="form-group">
                            <label>Package Name</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="2"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Price (PKR)</label>
                            <input type="number" class="form-control" name="price" id="edit_price" required>
                        </div>
                        <div class="form-group">
                            <label>Duration (Months)</label>
                            <input type="number" class="form-control" name="duration_months" id="edit_duration" required>
                        </div>
                        <div class="form-group">
                            <label>Features (comma-separated)</label>
                            <textarea class="form-control" name="features" id="edit_features" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Update Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
    <script>
        function editPackage(package) {
            document.getElementById('edit_package_id').value = package.package_id;
            document.getElementById('edit_name').value = package.name;
            document.getElementById('edit_description').value = package.description;
            document.getElementById('edit_price').value = package.price;
            document.getElementById('edit_duration').value = package.duration_months;
            document.getElementById('edit_features').value = package.features;
            
            $('#editPackageModal').modal('show');
        }
    </script>
</body>
</html>
