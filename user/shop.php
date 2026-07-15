<?php
require_once('../config/functions.php');
require_once('../config/shop_helpers.php');
require_once('../config/RecommendationService.php');
requireUser();

$userId = $_SESSION['user_id'];
$recService = new RecommendationService($conn);

$catFilter = (int) ($_GET['category'] ?? 0);
$search = sanitize($_GET['search'] ?? '');

$where = "WHERE p.status='Active'";
$params = [];
$types = "";
if ($catFilter) {
    $where .= " AND p.category_id=?";
    $params[] = $catFilter;
    $types .= "i";
}
if ($search) {
    $where .= " AND p.name LIKE ?";
    $s = "%$search%";
    $params[] =& $s;
    $types .= "s";
}

// Filter Exclusive Products (Only show to Premium/Gold)
$userSub = getUserSubscription($userId);
$packageName = $userSub['package_name'] ?? 'Basic';
if (!in_array($packageName, ['Premium', 'Gold'])) {
    $where .= " AND p.is_exclusive = 0";
}

$cats = $conn->query("SELECT * FROM shop_categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$sql = "
    SELECT p.*, c.name as cat_name, 
           AVG(r.rating) as avg_rating, 
           COUNT(r.review_id) as review_count 
    FROM shop_products p 
    LEFT JOIN shop_categories c ON p.category_id = c.category_id 
    LEFT JOIN shop_product_reviews r ON p.product_id = r.product_id
    $where 
    GROUP BY p.product_id 
    ORDER BY p.created_at DESC
";
$stmt = $conn->prepare($sql);
if ($params)
    $stmt->bind_param($types, ...$params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0;

// Fetch Instructor Recommendations
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
$recStmt->bind_param("ii", $userId, $userId);
$recStmt->execute();
$recommendedProducts = $recStmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Gym Shop - Project Rock Ascent</title>
    <?php include('../includes/head.php'); ?>
    <style>
        .product-card {
            background: var(--card-glass) !important;
            backdrop-filter: blur(15px) !important;
            border: 1px solid var(--border-glass) !important;
            transition: all 0.4s ease !important;
        }

        .product-card:hover {
            transform: translateY(-10px) !important;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5) !important;
            border-color: var(--primary-color) !important;
        }

        .product-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 20px 20px 0 0;
        }

        .product-price {
            font-size: 24px;
            font-weight: 900;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stock-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
        }

        .btn-cart {
            background: var(--primary-gradient) !important;
            color: white !important;
            border: none !important;
            border-radius: 14px !important;
            padding: 12px !important;
            font-weight: 800 !important;
            width: 100%;
        }
    </style>
</head>

<body>
    <?php include('../includes/header.php'); ?>
    <div class="container-fluid px-4">
        <div class="dashboard-container">
            <!-- User Sidebar -->
            <?php include('../includes/user_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <h2><i class="fas fa-store mr-2" style="color:#f59e0b"></i>Gym Shop</h2>
                    <div>
                        <a href="cart.php" class="btn btn-primary">
                            <i class="fas fa-shopping-cart mr-1"></i> Cart (<?php echo $cartCount; ?>)
                        </a>
                    </div>
                </div>
                <?php displayMessage(); ?>

                <!-- Search & Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form class="form-row align-items-center" method="GET">
                            <div class="col-md-6 mb-2">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control"
                                        placeholder="Search products..."
                                        value="<?php echo htmlspecialchars($search); ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <select name="category" class="form-control" onchange="this.form.submit()">
                                    <option value="0">All Categories</option>
                                    <?php foreach ($cats as $c): ?>
                                        <option value="<?php echo $c['category_id']; ?>" <?php echo $catFilter == $c['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <a href="shop.php" class="btn btn-secondary w-100">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Recommended Products Section (FEAT-REC-001) -->
                <?php if (!empty($recommendedProducts)): ?>
                    <div class="mb-5 fade-in-up">
                        <h3 class="mb-4 d-flex align-items-center">
                            <i class="fas fa-certificate text-warning mr-3"></i>
                            Recommended Products
                        </h3>
                        <div class="row">
                            <?php foreach ($recommendedProducts as $rp):
                                $imgSrc = !empty($rp['image']) ? '../assets/images/shop/' . htmlspecialchars($rp['image']) : 'https://placehold.co/300x200/1e293b/f59e0b?text=' . urlencode(substr($rp['name'], 0, 8));
                                $inStock = $rp['stock_quantity'] > 0;
                                ?>
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card product-card h-100"
                                        style="border: 2px solid var(--primary-color) !important; position:relative; overflow:hidden; cursor:pointer;"
                                        onclick="showRecommendationModal('<?php echo addslashes($rp['name']); ?>', '<?php echo $imgSrc; ?>', '<?php echo formatCurrency($rp['price']); ?>', '<?php echo addslashes($rp['cat_name'] ?? 'General'); ?>', '<?php echo addslashes($rp['message'] ?: 'This product will support your goals.'); ?>', '<?php echo addslashes($rp['instructor_name']); ?>')">
                                        <!-- Special Badge -->
                                        <div
                                            style="position: absolute; top: 15px; left: -35px; background: var(--primary-gradient); color: white; padding: 5px 40px; transform: rotate(-45deg); font-size: 12px; font-weight: bold; z-index: 10; box-shadow: 0 4px 10px rgba(0,0,0,0.5);">
                                            RECOMMENDED
                                        </div>
                                        <?php if (!$inStock): ?><span class="stock-badge badge badge-danger"
                                                style="right: 15px; top: 15px;">Out of Stock</span><?php endif; ?>

                                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($rp['name']); ?>"
                                            class="product-img" style="height: 200px; object-fit: cover;"
                                            onerror="this.src='https://placehold.co/300x200/1e293b/f59e0b?text=No+Image';this.onerror=null;">
                                        <div class="card-body d-flex flex-column" style="background: rgba(15, 23, 42, 0.9);">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="mb-0 text-white font-weight-bold">
                                                    <?php echo htmlspecialchars($rp['name']); ?></h5>
                                                <h5 class="text-primary mb-0 font-weight-bold">
                                                    <?php echo formatCurrency($rp['price']); ?></h5>
                                            </div>
                                            <small class="text-muted mb-3 d-block"
                                                style="max-width: 100%; white-space: normal;"><i
                                                    class="fas fa-tag mr-1"></i><?php echo htmlspecialchars($rp['cat_name'] ?? 'General'); ?></small>

                                            <div class="alert alert-info py-2 px-3 mb-3"
                                                style="background: rgba(56, 189, 248, 0.1); border-left: 3px solid #38bdf8; font-size: 0.85rem;">
                                                <strong><i class="fas fa-comment-dots mr-1"></i>
                                                    <?php echo htmlspecialchars($rp['instructor_name']); ?> says:</strong><br>
                                                "<?php echo htmlspecialchars($rp['message'] ?: 'This product will perfectly support your current fitness goals.'); ?>"
                                            </div>

                                            <div
                                                class="mt-auto pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                                                <div class="rating text-warning small">
                                                    <?php
                                                    $rating = round($rp['avg_rating'] ?? 0);
                                                    for ($i = 1; $i <= 5; $i++)
                                                        echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star text-muted"></i>';
                                                    echo " <span class='text-muted'>({$rp['review_count']})</span>";
                                                    ?>
                                                </div>
                                                <form method="POST" action="cart.php" class="m-0">
                                                    <input type="hidden" name="action" value="add">
                                                    <input type="hidden" name="product_id"
                                                        value="<?php echo $rp['product_id']; ?>">
                                                    <input type="hidden" name="qty" value="1">
                                                    <button type="submit" class="btn btn-primary btn-sm px-3 rounded-pill" <?php echo !$inStock ? 'disabled' : ''; ?>>
                                                        <i class="fas fa-cart-plus mr-1"></i> Add
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <hr style="border-color: rgba(255,255,255,0.1); margin: 40px 0;">
                    </div>
                <?php endif; ?>

                <!-- Products Grid -->
                <?php if (empty($products)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-box-open" style="font-size:60px;color:#f59e0b;opacity:0.5"></i>
                            <h4 class="mt-3">No Products Found</h4>
                            <p class="text-muted">Try a different search or category.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($products as $p):
                            $imgSrc = !empty($p['image']) ? '../assets/images/shop/' . htmlspecialchars($p['image']) : 'https://placehold.co/300x200/1e293b/f59e0b?text=' . urlencode(substr($p['name'], 0, 8));
                            $inStock = $p['stock_quantity'] > 0;
                            ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                <div class="card product-card h-100" style="position:relative">
                                    <?php if (!$inStock): ?><span class="stock-badge badge badge-danger">Out of
                                            Stock</span><?php endif; ?>
                                    <?php if ($inStock && $p['stock_quantity'] <= 5): ?><span
                                            class="stock-badge badge badge-warning">Low Stock</span><?php endif; ?>
                                    <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"
                                        class="product-img"
                                        onerror="this.src='https://placehold.co/300x200/1e293b/f59e0b?text=No+Image';this.onerror=null;">
                                    <div class="card-body d-flex flex-column">
                                        <span class="badge badge-info mb-2"
                                            style="max-width: 100%; white-space: normal; text-align: left; line-height: 1.4; padding: 5px 10px; display: inline-block;"><?php echo htmlspecialchars($p['cat_name'] ?? ''); ?></span>
                                        <h6 class="font-weight-bold mb-1"><?php echo htmlspecialchars($p['name']); ?></h6>
                                        <div class="mb-2">
                                            <?php
                                            $rating = round($p['avg_rating'] ?? 0);
                                            for ($i = 1; $i <= 5; $i++):
                                                ?>
                                                <i class="fas fa-star <?php echo $i <= $rating ? 'text-warning' : 'text-muted'; ?>"
                                                    style="font-size:12px"></i>
                                            <?php endfor; ?>
                                            <small class="text-muted ml-1">(<?php echo $p['review_count']; ?>)</small>
                                        </div>
                                        <p class="text-muted small mb-2" style="flex:1">
                                            <?php echo htmlspecialchars(substr($p['description'] ?? '', 0, 80)); ?></p>

                                        <?php
                                        // Check for New Personalized Instructor Recommendations (FEAT-REC-001)
                                        $isPersonalRecommended = false;
                                        if (!empty($recommendedProducts)) {
                                            foreach ($recommendedProducts as $rp) {
                                                if ($rp['product_id'] == $p['product_id']) {
                                                    $isPersonalRecommended = true;
                                                    break;
                                                }
                                            }
                                        }

                                        if ($isPersonalRecommended):
                                            ?>
                                            <div class="mb-2 p-2"
                                                style="background: rgba(56, 189, 248, 0.1); border-radius: 10px; border-left: 3px solid #38bdf8; cursor: pointer;"
                                                onclick="showRecommendationModal('<?php echo addslashes($p['name']); ?>', '<?php echo $imgSrc; ?>', '<?php echo formatCurrency($p['price']); ?>', '<?php echo addslashes($p['cat_name'] ?? 'General'); ?>', 'Your instructor has specifically chosen this for you.', 'Your Instructor')">
                                                <small style="color: #38bdf8;"><i class="fas fa-star mr-1"></i> <strong>Instructor
                                                        Recommended</strong></small>
                                            </div>
                                        <?php endif; ?>

                                        <?php
                                        // Check for Trainer Endorsements (Old system)
                                        $endorsement = $conn->query("SELECT e.*, i.name as trainer_name FROM shop_trainer_endorsements e JOIN instructors i ON e.instructor_id = i.instructor_id WHERE e.product_id = {$p['product_id']} LIMIT 1")->fetch_assoc();
                                        if ($endorsement):
                                            ?>
                                            <div class="mb-2 p-2"
                                                style="background: rgba(245, 158, 11, 0.1); border-radius: 10px; border-left: 3px solid #f59e0b;">
                                                <small class="text-warning"><i class="fas fa-award mr-1"></i> Recommended by
                                                    <strong><?php echo htmlspecialchars($endorsement['trainer_name']); ?></strong></small>
                                            </div>
                                        <?php endif; ?>

                                        <div class="mb-2">
                                            <?php
                                            $discountedPrice = $recService->getDiscountedPrice($p['price'], $userId);
                                            if ($discountedPrice < $p['price']):
                                                ?>
                                                <span class="text-muted small" style="text-decoration: line-through;">PKR
                                                    <?php echo number_format($p['price'], 0); ?></span><br>
                                                <div class="product-price">PKR <?php echo number_format($discountedPrice, 0); ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="product-price">PKR <?php echo number_format($p['price'], 0); ?></div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (hasUserPurchasedProduct($conn, $userId, $p['product_id'])): ?>
                                            <a href="orders.php" class="btn btn-sm btn-outline-warning mb-2 w-100">
                                                <i class="fas fa-star mr-1"></i> Write a Review
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($inStock): ?>
                                            <form method="POST" action="cart_action.php">
                                                <input type="hidden" name="action" value="add">
                                                <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                                                <input type="hidden" name="redirect" value="shop.php">
                                                <div class="input-group mb-2">
                                                    <input type="number" name="qty" class="form-control" value="1" min="1"
                                                        max="<?php echo $p['stock_quantity']; ?>" style="max-width:70px">
                                                    <div class="input-group-append">
                                                        <button class="btn-cart btn"><i
                                                                class="fas fa-cart-plus mr-1"></i>Add</button>
                                                    </div>
                                                </div>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn-cart btn w-100" disabled>Out of Stock</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include('../includes/footer.php'); ?>

    <!-- Instructor Recommendation Modal (FEAT-REC-001) -->
    <div class="modal fade" id="recommendationModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"
                style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(20px); border: 1px solid var(--primary-color);">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white font-weight-bold" id="recProductName">Product Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <img id="recProductImg" src="" class="img-fluid rounded mb-4"
                        style="max-height: 250px; width: 100%; object-fit: cover; border: 1px solid var(--border-glass);">
                    <h4 class="text-primary font-weight-bold mb-2" id="recProductPrice"></h4>
                    <p class="text-muted small mb-4" id="recProductCat"></p>

                    <div class="alert alert-warning py-3 px-4 mb-0"
                        style="background: rgba(245, 158, 11, 0.1); border: 1px dashed var(--primary-color); border-radius: 20px;">
                        <i class="fas fa-quote-left mr-2 opacity-50"></i>
                        <span class="text-white font-weight-bold" style="font-size: 1.1rem;">Your instructor recommends
                            this product to you.</span>
                        <hr style="border-color: rgba(255,255,255,0.1);">
                        <p class="mb-0 text-muted italic" id="recInstructorMsg"></p>
                        <small class="d-block mt-2 text-primary font-weight-bold">— <span
                                id="recInstructorName"></span></small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/scripts.php'); ?>
    <script>
        function showRecommendationModal(name, img, price, cat, msg, instructor) {
            $('#recProductName').text(name);
            $('#recProductImg').attr('src', img);
            $('#recProductPrice').text(price);
            $('#recProductCat').text(cat);
            $('#recInstructorMsg').text('"' + msg + '"');
            $('#recInstructorName').text(instructor);
            $('#recommendationModal').modal('show');
        }
    </script>
</body>

</html>