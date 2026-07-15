<?php
require_once('../config/functions.php');
requireUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $productId = (int)$_POST['product_id'];
    $rating = (int)$_POST['rating'];
    $comment = sanitize($_POST['comment']);
    $orderId = (int)$_POST['order_id'];

    if ($rating < 1 || $rating > 5) {
        redirect("orders.php?view=$orderId", 'Invalid rating.', 'danger');
    }

    // Check if user actually bought this product in this order and it was delivered
    $stmt = $conn->prepare("
        SELECT o.status 
        FROM shop_orders o 
        JOIN shop_order_items oi ON o.order_id = oi.order_id 
        WHERE o.order_id = ? AND o.user_id = ? AND oi.product_id = ? AND o.status = 'Delivered'
    ");
    $stmt->bind_param("iii", $orderId, $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        redirect("orders.php", 'You can only review delivered products you have purchased.', 'danger');
    }

    // Check if already reviewed
    $stmt = $conn->prepare("SELECT review_id FROM shop_product_reviews WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        // Update existing review or just prevent duplicate
        $stmt = $conn->prepare("UPDATE shop_product_reviews SET rating = ?, comment = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("isii", $rating, $comment, $userId, $productId);
        $stmt->execute();
        redirect("orders.php?view=$orderId", 'Review updated successfully!', 'success');
    } else {
        $stmt = $conn->prepare("INSERT INTO shop_product_reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $productId, $userId, $rating, $comment);
        $stmt->execute();
        redirect("orders.php?view=$orderId", 'Thank you for your feedback!', 'success');
    }
} else {
    redirect('orders.php');
}
?>
