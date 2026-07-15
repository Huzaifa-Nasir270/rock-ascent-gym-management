<?php
require_once('../config/functions.php');
require_once('../config/RecommendationService.php');
requireUser();

$userId = $_SESSION['user_id'];
$recService = new RecommendationService($conn);

// Fetch products grouped by goal and category for the builder
$goals = ['Muscle Gain', 'Fat Loss', 'Endurance', 'Recovery', 'Energy'];
$productsByGoal = [];

foreach ($goals as $goal) {
    $stmt = $conn->prepare("
        SELECT p.*, c.name as cat_name 
        FROM shop_products p
        JOIN shop_product_goals pg ON p.product_id = pg.product_id
        JOIN shop_categories c ON p.category_id = c.category_id
        WHERE pg.goal_type = ? AND p.status = 'Active' AND p.stock_quantity > 0
    ");
    $stmt->bind_param("s", $goal);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Fallback: If no products match this goal, fetch top trending products
    if (empty($results)) {
        $results = $conn->query("SELECT p.*, c.name as cat_name FROM shop_products p JOIN shop_categories c ON p.category_id = c.category_id WHERE p.status = 'Active' AND p.stock_quantity > 0 LIMIT 12")->fetch_all(MYSQLI_ASSOC);
    }
    
    $productsByGoal[$goal] = $results;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Build Your Stack - Gym Shop</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=3.2">
    <style>
        .stack-step { display: none; }
        .stack-step.active { display: block; }
        .product-select-card {
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            background: rgba(30, 41, 59, 0.4);
            border-radius: 15px;
        }
        .product-select-card.selected {
            border-color: #f59e0b;
            background: rgba(245, 158, 11, 0.1);
        }
        .step-indicator {
            width: 30px; height: 30px; border-radius: 50%;
            background: #334155; display: inline-flex;
            align-items: center; justify-content: center;
            margin-right: 10px; font-weight: bold;
        }
        .step-indicator.active { background: #f59e0b; color: #1e293b; }
    </style>
</head>
<body class="dark-theme">
<?php include('../includes/header.php'); ?>
<div class="container-fluid px-4"><div class="dashboard-container">
<?php include('../includes/user_sidebar.php'); ?>
<div class="main-content">
    
    <div class="mb-4">
        <h2>⚒️ Build Your Own Supplement Stack</h2>
        <p class="text-muted">Create a personalized bundle and get a **10% Stack Discount**!</p>
    </div>

    <div class="card" style="border-radius: 20px; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(20px);">
        <div class="card-body p-5">
            
            <!-- Step Indicators -->
            <div class="d-flex justify-content-center mb-5">
                <div class="mx-3"><span class="step-indicator active" id="ind-1">1</span> Choose Goal</div>
                <div class="mx-3"><span class="step-indicator" id="ind-2">2</span> Select Products</div>
                <div class="mx-3"><span class="step-indicator" id="ind-3">3</span> Review & Save</div>
            </div>

            <form id="stackForm" action="cart_action.php" method="POST">
                <input type="hidden" name="action" value="add_stack">
                
                <!-- Step 1: Goal -->
                <div class="stack-step active" id="step-1">
                    <h4 class="text-center mb-4">What is your primary fitness goal?</h4>
                    <div class="row justify-content-center">
                        <?php foreach($goals as $g): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card product-select-card p-4 text-center goal-opt" data-goal="<?php echo $g; ?>">
                                <i class="fas fa-bullseye mb-3 text-warning" style="font-size: 2rem;"></i>
                                <h5><?php echo $g; ?></h5>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Step 2: Product Selection -->
                <div class="stack-step" id="step-2">
                    <h4 class="mb-4 text-center">Select 3 products for your <span id="display-goal" class="text-warning"></span> stack</h4>
                    <div id="product-grid" class="row">
                        <!-- Products injected here via JS -->
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-secondary mr-2 prev-step">Back</button>
                        <button type="button" class="btn btn-warning next-step" id="to-step-3" disabled>Next Step</button>
                    </div>
                </div>

                <!-- Step 3: Summary -->
                <div class="stack-step" id="step-3">
                    <h4 class="text-center mb-4">Your Custom Stack Summary</h4>
                    <div id="stack-summary" class="row justify-content-center mb-4"></div>
                    <div class="text-center">
                        <h3 class="mb-4">Total Stack Price: <span class="text-warning" id="total-price">0</span> PKR</h3>
                        <button type="button" class="btn btn-secondary mr-2 prev-step">Back</button>
                        <button type="submit" class="btn btn-success btn-lg px-5">Add Stack to Cart</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

</div></div></div>

<script>
    const productsByGoal = <?php echo json_encode($productsByGoal); ?>;
    let selectedGoal = '';
    let selectedProducts = [];
    let currentStep = 1;

    // Goal Selection (Step 1)
    document.querySelectorAll('.goal-opt').forEach(opt => {
        opt.addEventListener('click', function() {
            selectedGoal = this.dataset.goal;
            document.getElementById('display-goal').innerText = selectedGoal;
            
            // Highlight selected goal
            document.querySelectorAll('.goal-opt').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            
            renderProducts();
            goToStep(2);
        });
    });

    // Navigation Buttons (Next/Prev)
    document.querySelectorAll('.next-step').forEach(btn => {
        btn.addEventListener('click', () => goToStep(currentStep + 1));
    });
    document.querySelectorAll('.prev-step').forEach(btn => {
        btn.addEventListener('click', () => goToStep(currentStep - 1));
    });

    function goToStep(step) {
        if (step < 1 || step > 3) return;
        
        document.querySelectorAll('.stack-step').forEach(s => s.classList.remove('active'));
        document.getElementById('step-' + step).classList.add('active');
        
        // Update indicators
        document.querySelectorAll('.step-indicator').forEach(ind => ind.classList.remove('active'));
        for(let i=1; i<=step; i++) {
            document.getElementById('ind-' + i).classList.add('active');
        }
        
        currentStep = step;
        if(step === 3) renderSummary();
        
        // Scroll to top of card
        document.querySelector('.card').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function renderProducts() {
        const grid = document.getElementById('product-grid');
        grid.innerHTML = '';
        
        if (!productsByGoal[selectedGoal]) return;

        productsByGoal[selectedGoal].forEach(p => {
            const isSelected = selectedProducts.includes(p.product_id.toString());
            const col = document.createElement('div');
            col.className = 'col-md-4 mb-4';
            col.innerHTML = `
                <div class="card product-select-card p-3 h-100 prod-opt ${isSelected ? 'selected' : ''}" 
                     data-id="${p.product_id}" data-name="${p.name}" data-price="${p.price}">
                    <div class="text-center mb-3" style="background: rgba(255,255,255,0.05); border-radius: 15px; padding: 15px; overflow: hidden;">
                        <img src="../assets/images/shop/${p.image || 'default.webp'}" 
                             onerror="this.src='https://images.unsplash.com/photo-1593095191070-9a0439888920?q=80&w=500'"
                             style="height:140px; width: 100%; object-fit:contain;" class="product-img">
                    </div>
                    <h5 class="mb-1 text-white">${p.name}</h5>
                    <div class="badge badge-pill badge-outline-warning mb-2" style="border: 1px solid #f59e0b; color: #f59e0b; font-size: 10px;">${p.cat_name}</div>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <div class="text-warning font-weight-bold h5 mb-0">PKR ${parseFloat(p.price).toLocaleString()}</div>
                        <i class="fas fa-check-circle check-icon ${isSelected ? 'text-warning' : 'text-muted'}" style="font-size: 1.2rem; opacity: ${isSelected ? '1' : '0.2'}"></i>
                    </div>
                </div>
            `;
            grid.appendChild(col);
        });

        // Re-attach selection logic after rendering
        grid.querySelectorAll('.prod-opt').forEach(opt => {
            opt.addEventListener('click', function() {
                const id = this.dataset.id;
                if (selectedProducts.includes(id)) {
                    selectedProducts = selectedProducts.filter(i => i !== id);
                    this.classList.remove('selected');
                    const icon = this.querySelector('.check-icon');
                    icon.classList.replace('text-warning', 'text-muted');
                    icon.style.opacity = '0.2';
                } else {
                    if (selectedProducts.length < 3) {
                        selectedProducts.push(id);
                        this.classList.add('selected');
                        const icon = this.querySelector('.check-icon');
                        icon.classList.replace('text-muted', 'text-warning');
                        icon.style.opacity = '1';
                    } else {
                        alert("Please select a maximum of 3 products for your stack.");
                    }
                }
                const nextBtn = document.getElementById('to-step-3');
                if (nextBtn) {
                    nextBtn.disabled = selectedProducts.length < 2;
                }
            });
        });
    }

    function renderSummary() {
        const summary = document.getElementById('stack-summary');
        summary.innerHTML = '';
        let total = 0;
        
        if (selectedProducts.length === 0) {
            summary.innerHTML = '<div class="col-12 text-center text-muted">No products selected. Go back to choose items.</div>';
            return;
        }

        selectedProducts.forEach(id => {
            const p = productsByGoal[selectedGoal].find(i => i.product_id == id);
            if (!p) return;
            total += parseFloat(p.price);
            summary.innerHTML += `
                <div class="col-md-3 mb-3 mx-2 card p-3 text-center" style="background:rgba(255,255,255,0.05); border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);">
                    <input type="hidden" name="product_ids[]" value="${p.product_id}">
                    <img src="../assets/images/shop/${p.image || 'default.webp'}" 
                         onerror="this.src='https://images.unsplash.com/photo-1593095191070-9a0439888920?q=80&w=500'"
                         style="height:80px; object-fit:contain;" class="mb-2">
                    <h6 class="text-white mb-1">${p.name}</h6>
                    <div class="text-warning small font-weight-bold">PKR ${parseFloat(p.price).toLocaleString()}</div>
                </div>
            `;
        });
        
        // 10% Stack Discount
        const discountedTotal = total * 0.9;
        const discountAmount = total * 0.1;
        
        document.getElementById('total-price').innerText = Math.round(discountedTotal).toLocaleString();
        
        // Add breakdown
        const breakdown = document.createElement('div');
        breakdown.className = 'col-12 text-center mt-3';
        breakdown.innerHTML = `
            <div class="text-muted small">Subtotal: PKR ${total.toLocaleString()}</div>
            <div class="text-success small">Stack Discount (10%): -PKR ${discountAmount.toLocaleString()}</div>
        `;
        summary.appendChild(breakdown);
    }
</script>

<?php include('../includes/footer.php'); ?>
</body>
</html>
