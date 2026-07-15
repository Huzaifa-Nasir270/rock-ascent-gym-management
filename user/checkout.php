<?php
require_once('../config/functions.php');
requireUser();
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    redirect('shop.php', 'Your cart is empty. Please add items first.', 'danger');
}

$userId = $_SESSION['user_id'];
$user   = getUserDetails($userId);
$cart   = $_SESSION['cart'];
$total  = 0;
foreach ($cart as $item) $total += $item['price'] * $item['qty'];

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = sanitize($_POST['address'] ?? '');
    $phone   = sanitize($_POST['phone'] ?? '');
    $payment = sanitize($_POST['payment_method'] ?? 'Cash on Delivery');

    if (empty($address) || empty($phone)) {
        $_SESSION['message'] = 'Please fill in all required fields.';
        $_SESSION['message_type'] = 'danger';
    } else {
        $conn->begin_transaction();
        try {
            // Create order
            $stmt = $conn->prepare("INSERT INTO shop_orders (user_id,total_amount,status,shipping_address,phone,payment_method) VALUES (?,?,'Pending',?,?,?)");
            $stmt->bind_param("idsss", $userId, $total, $address, $phone, $payment);
            $stmt->execute();
            $orderId = $conn->insert_id;

            // Insert order items & deduct stock
            foreach ($cart as $pid => $item) {
                $stmt2 = $conn->prepare("INSERT INTO shop_order_items (order_id,product_id,quantity,price) VALUES (?,?,?,?)");
                $stmt2->bind_param("iiid", $orderId, $pid, $item['qty'], $item['price']);
                $stmt2->execute();
                $stmt3 = $conn->prepare("UPDATE shop_products SET stock_quantity=stock_quantity-? WHERE product_id=?");
                $stmt3->bind_param("ii", $item['qty'], $pid);
                $stmt3->execute();
            }

            $conn->commit();
            $_SESSION['cart'] = [];
            redirect('orders.php', "🎉 Order #$orderId placed successfully! We'll process it soon.", 'success');
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = 'Order failed: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Checkout - Gym Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .checkout-card { 
            background: var(--card-glass) !important; 
            backdrop-filter: blur(20px) !important;
            border: 1px solid var(--border-glass) !important; 
            border-radius:28px; 
            padding:35px;
            box-shadow: var(--glass-shadow);
        }
        .order-item-row { display:flex; justify-content:space-between; padding:15px 0; border-bottom:1px solid rgba(255,255,255,0.06); }
        .total-row { font-size:28px; font-weight:900; background: var(--primary-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .btn-place-order { 
            background: var(--primary-gradient) !important; 
            color: white !important; 
            border: none !important; 
            border-radius: 18px !important; 
            padding:18px !important; 
            font-weight: 800 !important; 
            font-size:16px; 
            width:100%; 
            transition:all 0.3s;
            box-shadow: 0 15px 35px rgba(236, 72, 153, 0.3) !important;
        }
        .btn-place-order:hover { transform:translateY(-5px); box-shadow: 0 20px 45px rgba(236, 72, 153, 0.5) !important; }
        .payment-option { border:1px solid var(--border-glass); border-radius:18px; padding:20px; cursor:pointer; transition:all 0.3s; margin-bottom:15px; background: rgba(255,255,255,0.02); }
        .payment-option:hover { background: rgba(255,255,255,0.05); }
        .payment-option input:checked + div { color: var(--primary-color); }
        .payment-option:has(input:checked) { border-color: var(--primary-color); background: rgba(245,158,11,0.05); }
    </style>
</head>
<body>
<?php include('../includes/header.php'); ?>
<div class="container-fluid px-4"><div class="dashboard-container">
    <!-- Unified Sidebar -->
    <?php include('../includes/user_sidebar.php'); ?>

    <div class="main-content">
        <!-- Premium Page Header -->
        <div class="welcome-section mb-5 fade-in-up">
            <h1 class="mb-2">💳 Secure <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Checkout</span></h1>
            <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Complete your order and join the elite community of Project Rock Ascent.</p>
        </div>
        <?php displayMessage(); ?>
        <form method="POST">
        <div class="row">
            <div class="col-lg-7">
                <div class="checkout-card mb-4">
                    <h5 class="mb-4 font-weight-bold"><i class="fas fa-map-marker-alt mr-2" style="color:#f59e0b"></i>Shipping Details</h5>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Full Name</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Phone Number *</label>
                            <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']??''); ?>" required placeholder="03xxxxxxxxx">
                        </div>
                        <div class="form-group col-12">
                            <label>Delivery Address *</label>
                            <textarea name="address" class="form-control" rows="3" required placeholder="House/Flat No., Street, City, Province"><?php echo htmlspecialchars($user['address']??''); ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="checkout-card">
                    <h5 class="mb-4 font-weight-bold"><i class="fas fa-wallet mr-2" style="color:#f59e0b"></i>Payment Method</h5>
                    <input type="hidden" name="payment_method" value="Cash on Delivery">
                    <div class="payment-option d-flex align-items-center" style="border-color: var(--primary-color); background: rgba(245,158,11,0.05); cursor: default;">
                        <div class="mr-3" style="width: 20px; height: 20px; background: var(--primary-gradient); border-radius: 50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <i class="fas fa-check" style="color:white; font-size:10px;"></i>
                        </div>
                        <div>
                            <strong>Cash on Delivery</strong><br>
                            <small class="text-muted">Pay when you receive your order. Our delivery agent will collect the cash.</small>
                        </div>
                    </div>
                    <div class="mt-3 p-3 rounded" style="background: rgba(245,158,11,0.08); border: 1px solid rgba(245,158,11,0.2); border-radius: 12px;">
                        <small style="color: #f59e0b;"><i class="fas fa-info-circle mr-1"></i> <strong>How it works:</strong> Place your order, and our team will contact you to confirm delivery. Have the exact amount ready when the delivery arrives.</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="checkout-card">
                    <h5 class="mb-4 font-weight-bold"><i class="fas fa-receipt mr-2" style="color:#f59e0b"></i>Order Summary</h5>
                    <?php foreach ($cart as $item): ?>
                    <div class="order-item-row">
                        <div>
                            <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                            <small class="text-muted">x<?php echo $item['qty']; ?> × PKR <?php echo number_format($item['price'],0); ?></small>
                        </div>
                        <strong>PKR <?php echo number_format($item['price']*$item['qty'],0); ?></strong>
                    </div>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-between mt-3 mb-1">
                        <span>Subtotal</span><span>PKR <?php echo number_format($total,0); ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small mb-2">
                        <span>Shipping</span><span>Free</span>
                    </div>
                    <hr style="border-color:rgba(245,158,11,0.3)">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="font-weight-bold font-size-lg">Total</span>
                        <div class="total-row">PKR <?php echo number_format($total,0); ?></div>
                    </div>
                    <div class="mb-4">
                        <label class="remember-me" style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 13px; color: #94a3b8;">
                            <input type="checkbox" id="termsCheckout" style="width: 18px; height: 18px; margin-top: 2px;" required>
                            <span>I have read and agree to the <a href="#" data-toggle="modal" data-target="#termsModal" style="color: #f59e0b; font-weight: 700;">Terms and Conditions</a> of the Gym Shop.</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-place-order btn" id="placeOrderBtn" disabled>
                        <i class="fas fa-check-circle mr-2"></i>Place Order
                    </button>
                    <a href="cart.php" class="btn btn-secondary w-100 mt-2" style="border-radius: 14px; padding: 12px;">
                        <i class="fas fa-arrow-left mr-1"></i>Back to Cart
                    </a>
                </div>
            </div>
        </div>
        </form>
    </div>
</div></div>

<!-- Terms Modal (Same as Login for consistency) -->
<div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="background: #0f172a; border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; color: white;">
            <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" style="font-size: 14px; line-height: 1.6; color: #94a3b8;">
                <p>By placing an order on the Project Rock Ascent Gym Shop, you agree to:</p>
                <ul>
                    <li>All sales are final once processed.</li>
                    <li>Delivery times may vary depending on location.</li>
                    <li>Payment for Cash on Delivery must be ready at the time of arrival.</li>
                    <li>Personal information is used only for processing your order.</li>
                </ul>
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.1);">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const termsCheck = document.getElementById('termsCheckout');
    const placeOrderBtn = document.getElementById('placeOrderBtn');

    termsCheck.addEventListener('change', function() {
        placeOrderBtn.disabled = !this.checked;
    });
</script>
</body></html>
