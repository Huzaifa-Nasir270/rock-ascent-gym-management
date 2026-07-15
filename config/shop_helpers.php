<?php
/**
 * Shop Helper Functions
 */

/**
 * Get image src for a shop product
 * Returns the web-accessible path if file exists, else a generated placeholder
 */
function getProductImageSrc(string $image, string $productName = 'Product'): string {
    if (!empty($image)) {
        $absPath = dirname(__DIR__) . '/assets/images/shop/' . $image;
        if (file_exists($absPath)) {
            return '../assets/images/shop/' . $image;
        }
    }
    $label = urlencode(substr($productName, 0, 8));
    return "https://placehold.co/300x200/1e293b/f59e0b?text={$label}";
}

/**
 * Get image src relative to user/ folder
 */
function getUserProductImageSrc(string $image, string $productName = 'Product'): string {
    if (!empty($image)) {
        $absPath = dirname(__DIR__) . '/assets/images/shop/' . $image;
        if (file_exists($absPath)) {
            return '../assets/images/shop/' . $image;
        }
    }
    $label = urlencode(substr($productName, 0, 8));
    return "https://placehold.co/300x200/1e293b/f59e0b?text={$label}";
}

// Fallback onerror JS snippet for inline use
define('IMG_ONERROR', "onerror=\"this.src='https://placehold.co/300x200/1e293b/f59e0b?text=No+Image';this.onerror=null;\"");

/**
 * Check if a user has purchased a product (Delivered)
 */
function hasUserPurchasedProduct($conn, $userId, $productId) {
    $stmt = $conn->prepare("
        SELECT 1 FROM shop_orders o 
        JOIN shop_order_items oi ON o.order_id = oi.order_id 
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'Delivered'
        LIMIT 1
    ");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}
