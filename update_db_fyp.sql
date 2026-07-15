USE gym_management;

-- Create Messages Table for Live Chat
CREATE TABLE IF NOT EXISTS messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    sender_type ENUM('User', 'Instructor') NOT NULL,
    receiver_id INT NOT NULL,
    receiver_type ENUM('User', 'Instructor') NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Exercises Table
CREATE TABLE IF NOT EXISTS exercises (
    exercise_id INT PRIMARY KEY AUTO_INCREMENT,
    instructor_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    media_path VARCHAR(255),
    media_type ENUM('image', 'video') DEFAULT 'image',
    difficulty_level ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES instructors(instructor_id) ON DELETE SET NULL
);

-- Create Package Templates Table
CREATE TABLE IF NOT EXISTS package_templates (
    template_id INT PRIMARY KEY AUTO_INCREMENT,
    package_id INT NOT NULL,
    default_instructor_id INT,
    workout_title VARCHAR(100) NOT NULL,
    workout_description TEXT,
    workout_exercises TEXT, -- comma separated exercise IDs or JSON
    diet_title VARCHAR(100) NOT NULL,
    diet_description TEXT,
    calories INT,
    protein INT,
    carbs INT,
    fats INT,
    plan_details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(package_id) ON DELETE CASCADE,
    FOREIGN KEY (default_instructor_id) REFERENCES instructors(instructor_id) ON DELETE SET NULL
);

-- Update some existing packages with sample templates
INSERT INTO package_templates (package_id, workout_title, workout_description, diet_title, diet_description, calories, protein, carbs, fats, plan_details)
VALUES 
((SELECT package_id FROM packages WHERE name = 'Basic' LIMIT 1), 'Basic Full Body', 'A 3-day full body workout plan.', 'Maintenance Diet', 'Basic maintenance calories', 2000, 150, 200, 60, 'Breakfast: Eggs\nLunch: Chicken\nDinner: Fish'),
((SELECT package_id FROM packages WHERE name = 'Premium' LIMIT 1), 'Premium Split', '4-day upper/lower split.', 'Muscle Gain Diet', 'Caloric surplus for muscle gain', 2800, 180, 300, 80, 'Breakfast: Oats & Whey\nLunch: Beef & Rice\nDinner: Salmon & Potatoes'),
((SELECT package_id FROM packages WHERE name = 'Elite' LIMIT 1), 'Elite Performance', '6-day Push/Pull/Legs program.', 'Elite Shred Diet', 'High protein cutting diet', 1800, 200, 150, 50, 'Breakfast: Egg whites\nLunch: Chicken Breast & Broccoli\nDinner: White Fish & Asparagus');

-- Add fitness level to users
ALTER TABLE users ADD COLUMN IF NOT EXISTS fitness_level ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner' AFTER fitness_goal;

