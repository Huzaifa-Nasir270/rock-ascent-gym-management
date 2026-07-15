-- Advanced Shop Features - Database Extension
USE gym_management;

-- 1. User Fitness Stats (For Progress-based Recommendations)
CREATE TABLE IF NOT EXISTS user_fitness_stats (
    stat_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    weight DECIMAL(5, 2),
    height DECIMAL(5, 2),
    bmi DECIMAL(5, 2),
    fitness_goal ENUM('Muscle Gain', 'Fat Loss', 'Endurance', 'General Fitness') DEFAULT 'General Fitness',
    activity_level ENUM('Sedentary', 'Light', 'Moderate', 'Active', 'Very Active') DEFAULT 'Moderate',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 2. Product Goals Mapping (Linking products to fitness objectives)
CREATE TABLE IF NOT EXISTS shop_product_goals (
    goal_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    goal_type ENUM('Muscle Gain', 'Fat Loss', 'Endurance', 'Recovery', 'Energy') NOT NULL,
    priority_score INT DEFAULT 1, -- Higher score means more recommended for this goal
    FOREIGN KEY (product_id) REFERENCES shop_products(product_id) ON DELETE CASCADE
);

-- 3. Membership Tiers & Benefits
CREATE TABLE IF NOT EXISTS membership_tiers (
    tier_id INT PRIMARY KEY AUTO_INCREMENT,
    tier_name VARCHAR(50) UNIQUE NOT NULL, -- 'Basic', 'Premium', 'Gold'
    discount_percentage DECIMAL(5, 2) DEFAULT 0.00,
    exclusive_access BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Shop Subscriptions (Recurring Supplement Boxes)
CREATE TABLE IF NOT EXISTS shop_subscriptions (
    subscription_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    bundle_name VARCHAR(100) NOT NULL,
    price_per_month DECIMAL(10, 2) NOT NULL,
    status ENUM('Active', 'Paused', 'Cancelled') DEFAULT 'Active',
    next_billing_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 5. Trainer Endorsements
CREATE TABLE IF NOT EXISTS shop_trainer_endorsements (
    endorsement_id INT PRIMARY KEY AUTO_INCREMENT,
    instructor_id INT NOT NULL,
    product_id INT NOT NULL,
    reasoning TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES instructors(instructor_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES shop_products(product_id) ON DELETE CASCADE
);

-- Seed Membership Tiers
INSERT INTO membership_tiers (tier_name, discount_percentage, exclusive_access) VALUES
('Basic', 0.00, FALSE),
('Premium', 5.00, FALSE),
('Gold', 12.00, TRUE)
ON DUPLICATE KEY UPDATE discount_percentage=VALUES(discount_percentage);
