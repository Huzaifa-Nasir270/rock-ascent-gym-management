<?php
require_once('../config/functions.php');
requireUser();
$userId = $_SESSION['user_id'];

$orders = $conn->prepare("SELECT * FROM shop_orders WHERE user_id=? ORDER BY created_at DESC");
$orders->bind_param("i", $userId);
$orders->execute();
$orders = $orders->get_result()->fetch_all(MYSQLI_ASSOC);

$selectedOrder = null;
$orderItems = [];
if (isset($_GET['view'])) {
    $oid = (int)$_GET['view'];
    $s = $conn->prepare("SELECT * FROM shop_orders WHERE order_id=? AND user_id=?");
    $s->bind_param("ii", $oid, $userId); $s->execute();
    $selectedOrder = $s->get_result()->fetch_assoc();
    if ($selectedOrder) {
        $si = $conn->prepare("SELECT oi.*, p.name, p.image FROM shop_order_items oi LEFT JOIN shop_products p ON oi.product_id=p.product_id WHERE oi.order_id=?");
        $si->bind_param("i", $oid); $si->execute();
        $orderItems = $si->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Orders - Gym Shop</title>
    <?php include('../includes/head.php'); ?>
    <style>
        .order-card { border-radius:16px; border:1px solid rgba(255,255,255,0.06); padding:20px; margin-bottom:16px; transition:all 0.3s; }
        .order-card:hover { border-color:rgba(245,158,11,0.3); transform:translateY(-2px); }
        .status-badge { padding:6px 14px; border-radius:20px; font-weight:700; font-size:12px; }
        .status-Pending  { background:rgba(251,191,36,0.15); color:#fbbf24; }
        .status-Paid     { background:rgba(59,130,246,0.15); color:#60a5fa; }
        .status-Shipped  { background:rgba(139,92,246,0.15); color:#a78bfa; }
        .status-Delivered{ background:rgba(34,197,94,0.15); color:#4ade80; }
        .status-Cancelled{ background:rgba(239,68,68,0.15); color:#f87171; }
        .order-detail-panel { background:rgba(245,158,11,0.05); border:1px solid rgba(245,158,11,0.15); border-radius:16px; padding:24px; }
        .progress-step { text-align:center; flex:1; position:relative; }
        .progress-step::before { content:''; position:absolute; top:16px; left:-50%; right:50%; height:2px; background:rgba(255,255,255,0.1); z-index:0; }
        .progress-step:first-child::before { display:none; }
        .step-circle { width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; position:relative; z-index:1; }
        .step-active .step-circle { background:linear-gradient(135deg,#f59e0b,#ec4899); color:white; }
        .step-done .step-circle   { background:rgba(34,197,94,0.3); color:#4ade80; border:2px solid #4ade80; }
        .step-pending .step-circle { background:rgba(255,255,255,0.05); color:#94a3b8; border:2px solid rgba(255,255,255,0.1); }
        
        /* Star Rating */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 5px;
        }
        .star-rating input { display: none; }
        .star-rating label {
            font-size: 24px;
            color: #444;
            cursor: pointer;
            transition: color 0.2s;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input:checked ~ label {
            color: #f59e0b;
        }
    </style>
</head>
<body>
<?php include('../includes/header.php'); ?>
<div class="container-fluid px-4"><div class="dashboard-container">
    <!-- User Sidebar -->
    <?php include('../includes/user_sidebar.php'); ?>
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-box-open mr-2" style="color:#f59e0b"></i>My Orders</h2>
        <?php displayMessage(); ?>

        <?php if ($selectedOrder): ?>
        <!-- Order Detail View -->
        <div class="d-flex align-items-center mb-4">
            <a href="orders.php" class="btn btn-secondary mr-3"><i class="fas fa-arrow-left mr-1"></i>All Orders</a>
            <h4 class="mb-0">Order #<?php echo $selectedOrder['order_id']; ?></h4>
        </div>

        <!-- Progress Tracker -->
        <?php
        $steps = ['Pending','Paid','Shipped','Delivered'];
        $curIdx = array_search($selectedOrder['status'], $steps);
        if ($selectedOrder['status'] === 'Cancelled') $curIdx = -1;
        ?>
        <?php if ($selectedOrder['status'] !== 'Cancelled'): ?>
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex">
                    <?php foreach ($steps as $i => $step): 
                        $cls = $i < $curIdx ? 'step-done' : ($i === $curIdx ? 'step-active' : 'step-pending');
                        $icons = ['Pending'=>'clock','Paid'=>'check','Shipped'=>'truck','Delivered'=>'box'];
                    ?>
                    <div class="progress-step <?php echo $cls; ?>">
                        <div class="step-circle mb-2"><i class="fas fa-<?php echo $icons[$step]; ?>"></i></div>
                        <small class="d-block font-weight-bold"><?php echo $step; ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-7">
                <div class="card mb-4">
                    <div class="card-header"><h5 class="mb-0">Items Ordered</h5></div>
                    <div class="card-body">
                        <?php foreach ($orderItems as $it):
                        $imgSrc = !empty($it['image']) ? '../assets/images/shop/'.htmlspecialchars($it['image']) : 'https://placehold.co/60x60/1e293b/f59e0b?text=P';
                        ?>
                        <div class="d-flex align-items-center mb-3 pb-3" style="border-bottom:1px solid rgba(255,255,255,0.06)">
                            <img src="<?php echo $imgSrc; ?>" width="60" height="60" style="border-radius:10px;object-fit:cover;" class="mr-3" onerror="this.src='https://placehold.co/60x60/1e293b/f59e0b?text=P';this.onerror=null;">
                            <div class="flex-grow-1">
                                <strong><?php echo htmlspecialchars($it['name']); ?></strong>
                                <small class="d-block text-muted">Qty: <?php echo $it['quantity']; ?> × PKR <?php echo number_format($it['price'],0); ?></small>
                                <?php if ($selectedOrder['status'] === 'Delivered'): ?>
                                    <button class="btn btn-sm btn-warning mt-2 px-3" onclick="openFeedbackModal(<?php echo $it['product_id']; ?>, '<?php echo addslashes($it['name']); ?>')" style="border-radius:20px; font-weight:700;">
                                        <i class="fas fa-star mr-1"></i> Rate & Review
                                    </button>
                                <?php endif; ?>
                            </div>
                            <strong>PKR <?php echo number_format($it['price']*$it['quantity'],0); ?></strong>
                        </div>
                        <?php endforeach; ?>
                        <div class="d-flex justify-content-between mt-2">
                            <strong>Total</strong>
                            <strong style="font-size:18px;background:linear-gradient(135deg,#f59e0b,#ec4899);-webkit-background-clip:text;-webkit-text-fill-color:transparent">PKR <?php echo number_format($selectedOrder['total_amount'],0); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="order-detail-panel">
                    <h6 class="font-weight-bold mb-3"><i class="fas fa-info-circle mr-2" style="color:#f59e0b"></i>Order Info</h6>
                    <p><strong>Status:</strong> <span class="status-badge status-<?php echo $selectedOrder['status']; ?>"><?php echo $selectedOrder['status']; ?></span></p>
                    <p><strong>Date:</strong> <?php echo formatDate($selectedOrder['created_at']); ?></p>
                    <p><strong>Payment:</strong> <?php echo htmlspecialchars($selectedOrder['payment_method']); ?></p>
                    <hr style="border-color:rgba(245,158,11,0.2)">
                    <h6 class="font-weight-bold mb-2"><i class="fas fa-map-marker-alt mr-2" style="color:#f59e0b"></i>Delivery To</h6>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($selectedOrder['shipping_address'])); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($selectedOrder['phone']); ?></p>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- Orders List -->
        <?php if (empty($orders)): ?>
            <div class="card"><div class="card-body text-center py-5">
                <i class="fas fa-box-open" style="font-size:64px;color:#f59e0b;opacity:0.4"></i>
                <h4 class="mt-3">No Orders Yet</h4>
                <p class="text-muted">You haven't placed any orders yet.</p>
                <a href="shop.php" class="btn btn-primary mt-2"><i class="fas fa-store mr-1"></i>Browse Shop</a>
            </div></div>
        <?php else: ?>
            <?php foreach ($orders as $o): ?>
            <div class="card order-card" style="cursor:pointer" onclick="window.location='orders.php?view=<?php echo $o['order_id']; ?>'">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h5 class="mb-1 font-weight-bold">Order #<?php echo $o['order_id']; ?></h5>
                        <small class="text-muted"><i class="fas fa-calendar-alt mr-1"></i><?php echo formatDate($o['created_at']); ?></small>
                    </div>
                    <div class="text-center">
                        <div style="font-size:20px;font-weight:800;background:linear-gradient(135deg,#f59e0b,#ec4899);-webkit-background-clip:text;-webkit-text-fill-color:transparent">PKR <?php echo number_format($o['total_amount'],0); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($o['payment_method']); ?></small>
                    </div>
                    <div class="text-right d-flex flex-column align-items-end">
                        <span class="status-badge status-<?php echo $o['status']; ?> mb-2"><?php echo $o['status']; ?></span>
                        <?php if ($o['status'] === 'Delivered'): ?>
                            <a href="orders.php?view=<?php echo $o['order_id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-star mr-1"></i> Review Products
                            </a>
                        <?php else: ?>
                            <small class="text-muted mt-1 d-block">Click to view details</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div></div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Feedback: <span id="feedbackProductName"></span></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form action="submit_review.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="feedbackProductId">
                    <input type="hidden" name="order_id" value="<?php echo $selectedOrder['order_id'] ?? 0; ?>">
                    
                    <div class="form-group">
                        <label>Rating</label>
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5" required><label for="star5"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star4" name="rating" value="4"><label for="star4"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star3" name="rating" value="3"><label for="star3"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star2" name="rating" value="2"><label for="star2"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star1" name="rating" value="1"><label for="star1"><i class="fas fa-star"></i></label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Comment</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="How was the product? Tell us about the quality..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
<?php include('../includes/scripts.php'); ?>
<script>
function openFeedbackModal(productId, productName) {
    document.getElementById('feedbackProductId').value = productId;
    document.getElementById('feedbackProductName').innerText = productName;
    $('#feedbackModal').modal('show');
}
</script>
</body></html>
