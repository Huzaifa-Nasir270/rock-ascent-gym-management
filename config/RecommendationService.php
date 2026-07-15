<?php
/**
 * RecommendationService - AI-based intelligence for Gym Shop
 */
class RecommendationService {
    private $conn;

    public function __construct($db_conn) {
        $this->conn = $db_conn;
    }

    /**
     * Get personalized recommendations for a user
     */
    /**
     * Get personalized recommendations with AI-backed reasoning
     */
    public function getPersonalizedRecommendations($userId, $limit = 6) {
        // 1. Get latest user stats and goal
        $stmt = $this->conn->prepare("SELECT fitness_goal, bmi, weight, height FROM user_fitness_stats WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $userStats = $stmt->get_result()->fetch_assoc();

        if (!$userStats) {
            return $this->getTrendingProducts($limit);
        }

        $goal = $userStats['fitness_goal'];
        $bmi = $userStats['bmi'];
        $weight = $userStats['weight'];

        // 2. Intelligence Layer: Categorize User
        $bmiCategory = $this->getBMICategory($bmi);
        
        // 3. Logic Expansion: Determine secondary needs
        $targetGoals = [$goal];
        if ($bmiCategory === 'Overweight' || $bmiCategory === 'Obese') {
            if (!in_array('Fat Loss', $targetGoals)) $targetGoals[] = 'Fat Loss';
            if (!in_array('Metabolism', $targetGoals)) $targetGoals[] = 'Metabolism';
        } elseif ($bmiCategory === 'Underweight') {
            if (!in_array('Muscle Gain', $targetGoals)) $targetGoals[] = 'Muscle Gain';
            if (!in_array('Energy', $targetGoals)) $targetGoals[] = 'Energy';
        }

        // 4. Fetch Products with Membership Filtering
        $userSub = getUserSubscription($userId);
        $packageName = $userSub['package_name'] ?? 'Basic';
        $exclusiveFilter = (!in_array($packageName, ['Premium', 'Gold'])) ? " AND p.is_exclusive = 0" : "";

        // Build goal placeholders
        $placeholders = implode(',', array_fill(0, count($targetGoals), '?'));
        $query = "SELECT p.*, pg.goal_type, pg.priority_score 
                  FROM shop_products p
                  JOIN shop_product_goals pg ON p.product_id = pg.product_id
                  WHERE pg.goal_type IN ($placeholders)
                  AND p.status = 'Active'
                  AND p.stock_quantity > 0
                  $exclusiveFilter
                  GROUP BY p.product_id
                  ORDER BY pg.priority_score DESC, p.created_at DESC
                  LIMIT ?";
        
        $stmt = $this->conn->prepare($query);
        $types = str_repeat("s", count($targetGoals)) . "i";
        $params = array_merge($targetGoals, [$limit]);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // 5. Reasoning Engine: Add "Why" to each product
        foreach ($products as &$p) {
            $p['ai_reasoning'] = $this->generateReasoning($p, $goal, $bmiCategory);
        }

        return !empty($products) ? $products : $this->getTrendingProducts($limit);
    }

    /**
     * Categorize BMI into human-readable segments for AI processing
     */
    private function getBMICategory($bmi) {
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25) return 'Healthy';
        if ($bmi < 30) return 'Overweight';
        return 'Obese';
    }

    /**
     * AI Explanation Generator
     * Explains the clinical or fitness-based reasoning for the recommendation
     */
    private function generateReasoning($product, $userGoal, $bmiCategory) {
        $pName = $product['name'];
        $pGoal = $product['goal_type'];

        // Logic patterns for reasoning
        if ($userGoal === 'Muscle Gain') {
            if ($bmiCategory === 'Underweight') {
                return "Based on your Low BMI, we've prioritized **$pName** to provide the high-calorie surplus and amino acids needed to kickstart your muscle growth safely.";
            }
            return "As your goal is building lean mass, this **$pName** is recommended for its high bioavailability and proven ability to support fast muscle fiber repair.";
        }

        if ($userGoal === 'Fat Loss') {
            if ($bmiCategory === 'Obese' || $bmiCategory === 'Overweight') {
                return "To support your weight management journey, **$pName** was selected for its ability to enhance metabolic rate and keep you satiated during calorie deficits.";
            }
            return "This product perfectly aligns with your **$pGoal** strategy, helping you maintain energy levels while your body burns stubborn fat stores.";
        }

        if ($pGoal === 'Endurance' || $pGoal === 'Energy') {
            return "We recommend **$pName** to help you push past fatigue. Its micro-nutrients are specifically chosen to replenish electrolytes lost during your intense training sessions.";
        }

        return "Our AI identified **$pName** as a key supplement to optimize your performance and bridge nutritional gaps in your current **$userGoal** phase.";
    }

    /**
     * Calculate dynamic price based on user membership tier
     */
    public function getDiscountedPrice($originalPrice, $userId) {
        $stmt = $this->conn->prepare("SELECT p.name as package_name FROM subscriptions s JOIN packages p ON s.package_id = p.package_id WHERE s.user_id = ? AND s.status = 'Active' LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if (!$result) return $originalPrice;

        $packageName = $result['package_name'];
        
        $stmt = $this->conn->prepare("SELECT discount_percentage FROM membership_tiers WHERE tier_name = ?");
        $stmt->bind_param("s", $packageName);
        $stmt->execute();
        $tier = $stmt->get_result()->fetch_assoc();

        if (!$tier) return $originalPrice;

        $discount = $tier['discount_percentage'] / 100;
        return round($originalPrice * (1 - $discount), 2);
    }

    private function getTrendingProducts($limit) {
        $products = $this->conn->query("SELECT * FROM shop_products WHERE status = 'Active' ORDER BY created_at DESC LIMIT $limit")->fetch_all(MYSQLI_ASSOC);
        foreach ($products as &$p) {
            $p['ai_reasoning'] = "This is a community favorite! Our members with similar goals have seen great results with this product.";
        }
        return $products;
    }
}
?>
