<?php
require_once('../config/functions.php');
requireUser();
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$cart = $_SESSION['cart'];
$total = 0;
foreach ($cart as $item) $total += $item['price'] * $item['qty'];
$cartCount = array_sum(array_column($cart, 'qty'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>My Cart - Gym Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .cart-item-img { width:80px; height:80px; border-radius:18px; object-fit:cover; }
        .qty-input { width:70px; text-align:center; border-radius: 12px !important; }
        .cart-summary { 
            background: var(--card-glass) !important; 
            backdrop-filter: blur(20px) !important;
            border: 1px solid var(--border-glass) !important; 
            border-radius:28px; 
            padding:35px;
            box-shadow: var(--glass-shadow);
        }
        .total-price { font-size:32px; font-weight:900; background: var(--primary-gradient); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .btn-checkout { 
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
        .btn-checkout:hover { transform:translateY(-5px); box-shadow: 0 20px 45px rgba(236, 72, 153, 0.5) !important; }
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
            <h1 class="mb-2">🛒 My <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Shopping Cart</span></h1>
            <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Review your items and proceed to secure checkout.</p>
        </div>
        <?php displayMessage(); ?>

        <?php if (empty($cart)): ?>
        <div class="card"><div class="card-body text-center py-5">
            <i class="fas fa-shopping-cart" style="font-size:64px;color:#f59e0b;opacity:0.4"></i>
            <h4 class="mt-3">Your Cart is Empty</h4>
            <p class="text-muted">Browse our shop and add some products!</p>
            <a href="shop.php" class="btn btn-primary mt-2"><i class="fas fa-store mr-1"></i>Go to Shop</a>
        </div></div>
        <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Cart Items (<?php echo $cartCount; ?> items)</h5>
                        <a href="cart_action.php?action=clear" class="btn btn-sm btn-danger" onclick="return confirm('Clear cart?')">
                            <i class="fas fa-trash mr-1"></i>Clear All
                        </a>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cart as $pid => $item):
                            $imgSrc  = !empty($item['image']) ? '../assets/images/shop/'.htmlspecialchars($item['image']) : 'https://placehold.co/70x70/1e293b/f59e0b?text=P';
                            $subtotal = $item['price'] * $item['qty'];
                        ?>
                        <div class="d-flex align-items-center mb-3 pb-3" style="border-bottom:1px solid rgba(255,255,255,0.06)">
                            <img src="<?php echo $imgSrc; ?>" alt="" class="cart-item-img mr-3" onerror="this.src='https://placehold.co/70x70/1e293b/f59e0b?text=P';this.onerror=null;">
                            <div class="flex-grow-1">
                                <h6 class="mb-0 font-weight-bold"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <small class="text-muted">PKR <?php echo number_format($item['price'],0); ?> each</small>
                            </div>
                            <form method="POST" action="cart_action.php" class="d-flex align-items-center mr-3">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $pid; ?>">
                                <input type="number" name="qty" value="<?php echo $item['qty']; ?>" min="0" max="<?php echo $item['stock']; ?>" class="form-control qty-input mr-2" style="width:70px">
                                <button class="btn btn-sm btn-primary"><i class="fas fa-sync"></i></button>
                            </form>
                            <div class="text-right mr-3">
                                <strong style="font-size:16px">PKR <?php echo number_format($subtotal,0); ?></strong>
                            </div>
                            <a href="cart_action.php?action=remove&product_id=<?php echo $pid; ?>" class="btn btn-sm btn-danger">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="shop.php" class="btn btn-secondary mt-2"><i class="fas fa-arrow-left mr-1"></i>Continue Shopping</a>
            </div>
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h5 class="mb-4 font-weight-bold">Order Summary</h5>
                    <?php foreach ($cart as $item): ?>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['qty']; ?></span>
                        <span>PKR <?php echo number_format($item['price']*$item['qty'],0); ?></span>
                    </div>
                    <?php endforeach; ?>
                    <hr style="border-color:rgba(245,158,11,0.3)">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>Subtotal</span><strong>PKR <?php echo number_format($total,0); ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1 text-muted small">
                        <span>Shipping</span><span>Calculated at checkout</span>
                    </div>
                    <hr style="border-color:rgba(245,158,11,0.3)">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="font-weight-bold">Total</span>
                        <div class="total-price">PKR <?php echo number_format($total,0); ?></div>
                    </div>
                    <a href="checkout.php" class="btn-checkout btn">
                        <i class="fas fa-lock mr-2"></i>Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div></div>
<?php include('../includes/footer.php'); ?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
