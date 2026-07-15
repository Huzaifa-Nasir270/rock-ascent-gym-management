<?php
require_once('../config/functions.php');
requireAdmin();

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)$_POST['order_id'];
    $newStatus = sanitize($_POST['status']);
    $allowed = ['Pending','Paid','Shipped','Delivered','Cancelled'];
    if (in_array($newStatus, $allowed)) {
        $stmt = $conn->prepare("UPDATE shop_orders SET status=? WHERE order_id=?");
        $stmt->bind_param("si", $newStatus, $orderId);
        $stmt->execute();
        redirect('shop_orders.php', 'Order status updated successfully!', 'success');
    }
}

// Filters
$statusFilter = sanitize($_GET['status'] ?? '');
$search       = sanitize($_GET['search'] ?? '');
$page         = max(1, (int)($_GET['page'] ?? 1));
$perPage      = 15;
$offset       = ($page - 1) * $perPage;

$where = "WHERE 1=1";
$params = [];
$types  = "";
if ($statusFilter) { $where .= " AND o.status=?"; $params[] = $statusFilter; $types .= "s"; }
if ($search)       { $where .= " AND (u.name LIKE ? OR o.order_id LIKE ?)"; $s="%$search%"; $params[]=&$s; $params[]=&$s; $types.="ss"; }

$totalQ = $conn->prepare("SELECT COUNT(*) FROM shop_orders o LEFT JOIN users u ON o.user_id=u.user_id $where");
if ($params) { $totalQ->bind_param($types, ...$params); }
$totalQ->execute();
$totalRows = $totalQ->get_result()->fetch_row()[0];
$totalPages = ceil($totalRows / $perPage);

$sql = "SELECT o.*, u.name as member_name, u.email as member_email
        FROM shop_orders o
        LEFT JOIN users u ON o.user_id=u.user_id
        $where ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$params[] = $perPage; $params[] = $offset; $types .= "ii";
$stmt = $conn->prepare($sql);
if ($params) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Stats
$stats = $conn->query("SELECT status, COUNT(*) as cnt, SUM(total_amount) as total FROM shop_orders GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$statMap = [];
foreach ($stats as $s) $statMap[$s['status']] = $s;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shop Orders - Admin</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
<?php include('../includes/header.php'); ?>
<div class="container-fluid px-4">
<div class="dashboard-container">
<?php include('../includes/admin_sidebar.php'); ?>
<div class="main-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-shopping-bag mr-2" style="color:#f59e0b"></i>Shop Orders</h2>
    </div>

    <?php displayMessage(); ?>

    <!-- Stats -->
    <div class="row mb-4">
        <?php
        $statColors = ['Pending'=>'#f59e0b','Paid'=>'#3b82f6','Shipped'=>'#8b5cf6','Delivered'=>'#22c55e','Cancelled'=>'#ef4444'];
        foreach ($statColors as $s => $color) {
            $cnt   = $statMap[$s]['cnt'] ?? 0;
            $total = $statMap[$s]['total'] ?? 0;
            echo "<div class='col-md-2 col-sm-4 mb-3'>
                    <div class='stat-card' style='border-top:3px solid $color'>
                        <div class='stat-number' style='font-size:24px'>$cnt</div>
                        <div class='stat-label'>$s</div>
                        <small style='color:$color'>PKR " . number_format($total) . "</small>
                    </div>
                  </div>";
        }
        ?>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form class="form-row align-items-end" method="GET">
                <div class="col-md-4 mb-2">
                    <input type="text" name="search" class="form-control" placeholder="Search by member name or order ID..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <?php foreach (['Pending','Paid','Shipped','Delivered','Cancelled'] as $s) { ?>
                            <option value="<?php echo $s; ?>" <?php echo $statusFilter===$s?'selected':''; ?>><?php echo $s; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary w-100"><i class="fas fa-search mr-1"></i>Filter</button>
                </div>
                <div class="col-md-2 mb-2">
                    <a href="shop_orders.php" class="btn btn-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header"><h5 class="mb-0">All Orders (<?php echo $totalRows; ?>)</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th><th>Member</th><th>Total</th><th>Payment</th>
                            <th>Status</th><th>Date</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No orders found.</td></tr>
                    <?php else: foreach ($orders as $o):
                        $colors = ['Pending'=>'badge-warning','Paid'=>'badge-info','Shipped'=>'badge-primary','Delivered'=>'badge-success','Cancelled'=>'badge-danger'];
                        $badge  = $colors[$o['status']] ?? 'badge-secondary';
                    ?>
                        <tr>
                            <td>#<?php echo $o['order_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($o['member_name'] ?? 'N/A'); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($o['member_email'] ?? ''); ?></small>
                            </td>
                            <td><strong>PKR <?php echo number_format($o['total_amount']); ?></strong></td>
                            <td><?php echo htmlspecialchars($o['payment_method']); ?></td>
                            <td><span class="badge <?php echo $badge; ?>"><?php echo $o['status']; ?></span></td>
                            <td><?php echo formatDate($o['created_at']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="showUpdateModal(<?php echo $o['order_id']; ?>,'<?php echo $o['status']; ?>')">
                                    <i class="fas fa-edit"></i> Update
                                </button>
                                <button class="btn btn-sm btn-info" onclick="showDetails(<?php echo $o['order_id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="card-footer">
            <nav><ul class="pagination mb-0">
                <?php for ($i=1; $i<=$totalPages; $i++): ?>
                    <li class="page-item <?php echo $i==$page?'active':''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul></nav>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Update Order Status</h5><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="order_id" id="modal_order_id">
                    <div class="form-group">
                        <label>New Status</label>
                        <select name="status" id="modal_status" class="form-control">
                            <?php foreach (['Pending','Paid','Shipped','Delivered','Cancelled'] as $s): ?>
                                <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include('../includes/scripts.php'); ?>
<script>
function showUpdateModal(id, status) {
    document.getElementById('modal_order_id').value = id;
    document.getElementById('modal_status').value = status;
    $('#updateModal').modal('show');
}
</script>
</body>
</html>
