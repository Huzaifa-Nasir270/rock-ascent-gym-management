<?php
require_once('../config/functions.php');
requireUser();

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$action    = $_POST['action'] ?? $_GET['action'] ?? '';
$productId = (int)($_POST['product_id'] ?? $_GET['product_id'] ?? 0);
$qty       = max(1,(int)($_POST['qty'] ?? 1));
$redirect  = $_POST['redirect'] ?? $_GET['redirect'] ?? 'cart.php';

if ($action === 'add' && $productId > 0) {
    $stmt = $conn->prepare("SELECT * FROM shop_products WHERE product_id=? AND status='Active'");
    $stmt->bind_param("i",$productId); $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if ($product && $product['stock_quantity'] >= $qty) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['qty'] = min($_SESSION['cart'][$productId]['qty'] + $qty, $product['stock_quantity']);
        } else {
            $_SESSION['cart'][$productId] = ['product_id'=>$productId,'name'=>$product['name'],'price'=>$product['price'],'qty'=>$qty,'image'=>$product['image'],'stock'=>$product['stock_quantity']];
        }
        redirect($redirect, 'Item added to cart!', 'success');
    } else {
        redirect($redirect, 'Product not available or insufficient stock.', 'danger');
    }
}

// Add Custom Stack
if ($action === 'add_stack' && !empty($_POST['product_ids'])) {
    $productIds = $_POST['product_ids'];
    $successCount = 0;
    
    foreach ($productIds as $pid) {
        $stmt = $conn->prepare("SELECT * FROM shop_products WHERE product_id=? AND status='Active'");
        $stmt->bind_param("i", $pid); $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        
        if ($product && $product['stock_quantity'] > 0) {
            // Apply 10% Stack Discount to the original price
            $stackPrice = $product['price'] * 0.9;
            
            $_SESSION['cart'][$pid] = [
                'product_id' => $pid,
                'name' => $product['name'] . ' (Stack)',
                'price' => $stackPrice,
                'qty' => 1,
                'image' => $product['image'],
                'stock' => $product['stock_quantity']
            ];
            $successCount++;
        }
    }
    
    if ($successCount > 0) {
        redirect('cart.php', 'Your custom stack has been added with a 10% discount!', 'success');
    } else {
        redirect('build_stack.php', 'Failed to add stack. Check stock levels.', 'danger');
    }
}
if ($action === 'remove' && $productId > 0) {
    unset($_SESSION['cart'][$productId]);
    redirect('cart.php','Item removed from cart.','success');
}
if ($action === 'update' && $productId > 0) {
    if ($qty > 0) $_SESSION['cart'][$productId]['qty'] = $qty;
    else unset($_SESSION['cart'][$productId]);
    redirect('cart.php','Cart updated.','success');
}
if ($action === 'clear') {
    $_SESSION['cart'] = [];
    redirect('cart.php','Cart cleared.','success');
}
redirect('cart.php');
