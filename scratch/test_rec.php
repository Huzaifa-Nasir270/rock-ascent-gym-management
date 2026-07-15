<?php
require 'config/db.php';
$userId = 1; // Assuming we want to test for some user. Wait, I should find a user who has a recommendation.
$res = $conn->query("SELECT * FROM instructor_product_recommendations");
$recs = $res->fetch_all(MYSQLI_ASSOC);
echo "Recommendations:\n";
print_r($recs);

if (!empty($recs)) {
    $userId = $recs[0]['user_id'] ?: 1; // Try to use the actual user
}

$recSql = "
    SELECT r.message, p.*, c.name as cat_name, i.name as instructor_name,
           AVG(rev.rating) as avg_rating, COUNT(rev.review_id) as review_count
    FROM instructor_product_recommendations r
    JOIN shop_products p ON r.product_id = p.product_id
    LEFT JOIN shop_categories c ON p.category_id = c.category_id
    LEFT JOIN shop_product_reviews rev ON p.product_id = rev.product_id
    JOIN instructors i ON r.instructor_id = i.instructor_id
    JOIN user_instructor_assignments uia ON r.instructor_id = uia.instructor_id AND uia.user_id = ? AND uia.status = 'Active'
    WHERE (r.user_id = ? OR r.user_id IS NULL) AND p.status = 'Active'
    GROUP BY p.product_id
    ORDER BY r.created_at DESC
";
$recStmt = $conn->prepare($recSql);
if(!$recStmt) {
    echo "Prepare Error: " . $conn->error . "\n";
} else {
    $recStmt->bind_param("ii", $userId, $userId);
    $recStmt->execute();
    $recommendedProducts = $recStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo "\nResults for User $userId:\n";
    print_r($recommendedProducts);
}
