<?php
require_once('../config/functions.php');
requireAdmin();

// 1. Sales Overview
$totalSales = $conn->query("SELECT SUM(total_amount) as total FROM shop_orders WHERE status != 'Cancelled'")->fetch_assoc()['total'] ?? 0;
$orderCount = $conn->query("SELECT COUNT(*) as count FROM shop_orders")->fetch_assoc()['count'] ?? 0;

// 2. Top Fitness Goals among Users
$goalTrends = $conn->query("
    SELECT fitness_goal, COUNT(*) as count 
    FROM user_fitness_stats 
    GROUP BY fitness_goal 
    ORDER BY count DESC
")->fetch_all(MYSQLI_ASSOC);

// 3. Top Recommended Products (by AI Priority)
$topAIGoals = $conn->query("
    SELECT p.name, pg.goal_type, pg.priority_score 
    FROM shop_product_goals pg 
    JOIN shop_products p ON pg.product_id = p.product_id 
    ORDER BY pg.priority_score DESC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// 4. Subscription Health
$activeSubs = $conn->query("SELECT COUNT(*) as count FROM shop_subscriptions WHERE status = 'Active'")->fetch_assoc()['count'] ?? 0;
$recurringRevenue = $conn->query("SELECT SUM(price_per_month) as total FROM shop_subscriptions WHERE status = 'Active'")->fetch_assoc()['total'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shop Intelligence - Admin</title>
    <?php include('../includes/head.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .intel-card {
            background: rgba(30, 41, 59, 0.4);
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.1);
            padding: 25px;
            height: 100%;
        }
        .stat-value { font-size: 2rem; font-weight: 800; color: #f59e0b; }
    </style>
</head>
<body>
<?php include('../includes/header.php'); ?>
<div class="container-fluid px-4"><div class="dashboard-container">
<?php include('../includes/admin_sidebar.php'); ?>
<div class="main-content">
    
    <div class="mb-4">
        <h2>📊 Shop Intelligence & Analytics</h2>
        <p class="text-muted">Data-driven insights into your gym's e-commerce performance.</p>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="intel-card text-center">
                <h6>Total Revenue</h6>
                <div class="stat-value">PKR <?php echo number_format($totalSales, 0); ?></div>
                <small class="text-success"><i class="fas fa-arrow-up"></i> All time</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="intel-card text-center">
                <h6>Active Subscriptions</h6>
                <div class="stat-value"><?php echo $activeSubs; ?></div>
                <small class="text-info">Recurring Customers</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="intel-card text-center">
                <h6>Monthly Recurring Revenue</h6>
                <div class="stat-value">PKR <?php echo number_format($recurringRevenue, 0); ?></div>
                <small class="text-warning">Projected Income</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="intel-card text-center">
                <h6>Total Orders</h6>
                <div class="stat-value"><?php echo $orderCount; ?></div>
                <small class="text-muted">Order Volume</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="intel-card">
                <h5>User Fitness Goal Trends</h5>
                <canvas id="goalChart" class="mt-4"></canvas>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="intel-card">
                <h5>AI Engine: Top Recommendations</h5>
                <div class="table-responsive mt-3">
                    <table class="table text-white">
                        <thead><tr><th>Product</th><th>Goal</th><th>AI Priority</th></tr></thead>
                        <tbody>
                            <?php foreach($topAIGoals as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><span class="badge badge-info"><?php echo $row['goal_type']; ?></span></td>
                                <td><div class="progress" style="height:10px;"><div class="progress-bar bg-warning" style="width:<?php echo $row['priority_score']*10; ?>%"></div></div></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div></div></div>

<script>
    // Goal Trends Chart
    const goalData = {
        labels: <?php echo json_encode(array_column($goalTrends, 'fitness_goal')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($goalTrends, 'count')); ?>,
            backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#8b5cf6'],
            borderWidth: 0
        }]
    };

    new Chart(document.getElementById('goalChart'), {
        type: 'doughnut',
        data: goalData,
        options: {
            plugins: {
                legend: { position: 'bottom', labels: { color: 'white' } }
            }
        }
    });
</script>

<?php include('../includes/footer.php'); ?>
<?php include('../includes/scripts.php'); ?>
</body>
</html>
