ALTER TABLE users ADD COLUMN height DECIMAL(5,2) DEFAULT NULL AFTER gender;
ALTER TABLE users ADD COLUMN weight DECIMAL(5,2) DEFAULT NULL AFTER height;
ALTER TABLE users ADD COLUMN age INT DEFAULT NULL AFTER weight;
-- Align fitness_goal values if needed
ALTER TABLE users MODIFY COLUMN fitness_goal ENUM('Weight Gain', 'Weight Loss', 'Stay Fit', 'General Fitness', 'Muscle Building', 'Fat Loss', 'Strength', 'Endurance') DEFAULT 'Stay Fit';
