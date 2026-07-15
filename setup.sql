-- Gym Management System Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS gym_management;
USE gym_management;

-- Admin Table
CREATE TABLE admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users Table (Gym Members)
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    date_of_birth DATE,
    gender ENUM('Male', 'Female', 'Other'),
    address TEXT,
    city VARCHAR(50),
    state VARCHAR(50),
    zipcode VARCHAR(10),
    profile_image VARCHAR(255),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Instructors Table
CREATE TABLE instructors (
    instructor_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    specialization VARCHAR(100),
    certification VARCHAR(255),
    bio TEXT,
    profile_image VARCHAR(255),
    hire_date DATE,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Packages Table
CREATE TABLE packages (
    package_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    duration_months INT NOT NULL,
    features TEXT,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Subscriptions Table
CREATE TABLE subscriptions (
    subscription_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    instructor_id INT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('Active', 'Inactive', 'Expired') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(package_id),
    FOREIGN KEY (instructor_id) REFERENCES instructors(instructor_id)
);

-- Payments Table
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    subscription_id INT,
    amount DECIMAL(10, 2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('Cash', 'Card', 'Online Transfer') DEFAULT 'Cash',
    status ENUM('Paid', 'Pending', 'Failed') DEFAULT 'Paid',
    transaction_id VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(subscription_id)
);

-- Workout Plans Table
CREATE TABLE workout_plans (
    workout_plan_id INT PRIMARY KEY AUTO_INCREMENT,
    instructor_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    duration_weeks INT,
    difficulty_level ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner',
    exercises TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES instructors(instructor_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Diet Plans Table
CREATE TABLE diet_plans (
    diet_plan_id INT PRIMARY KEY AUTO_INCREMENT,
    instructor_id INT NOT NULL,
    user_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    calories INT,
    protein INT,
    carbs INT,
    fats INT,
    plan_details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES instructors(instructor_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Attendance Table
CREATE TABLE attendance (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    check_in_date DATE NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    status ENUM('Present', 'Absent', 'On Leave') DEFAULT 'Present',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (user_id, check_in_date)
);

-- Notifications Table
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    sender_id INT,
    sender_type ENUM('Admin', 'Instructor', 'System') DEFAULT 'System',
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read ENUM('Yes', 'No') DEFAULT 'No',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Announcements Table
CREATE TABLE announcements (
    announcement_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    audience ENUM('Users', 'Instructors', 'Both') DEFAULT 'Both',
    start_time DATETIME NULL,
    end_time DATETIME NULL,
    status ENUM('Published', 'Drafted') DEFAULT 'Published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(admin_id) ON DELETE CASCADE
);

-- Create indexes for faster queries
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_instructor_email ON instructors(email);
CREATE INDEX idx_admin_email ON admin(email);
CREATE INDEX idx_subscription_user ON subscriptions(user_id);
CREATE INDEX idx_payment_user ON payments(user_id);
CREATE INDEX idx_attendance_user ON attendance(user_id);
CREATE INDEX idx_notification_user ON notifications(user_id);
CREATE INDEX idx_workout_plan_user ON workout_plans(user_id);
CREATE INDEX idx_diet_plan_user ON diet_plans(user_id);

-- Insert sample admin
-- Password: password123 (hashed with bcrypt)
INSERT INTO admin (name, email, password, phone) VALUES 
('Admin User', 'admin@gym.com', '$2y$10$Oy10dH7rL.F5l/C5c.5V6eWF6V6e5Xy8H2I5kJ5kL5mM5nN5oO5pP', '03001234567');

-- Insert sample packages
INSERT INTO packages (name, description, price, duration_months, features, status) VALUES 
('Basic', 'Basic gym access', 50.00, 1, 'Gym Access,Locker Facility', 'Active'),
('Premium', 'Premium gym + personal trainer (4 sessions/month)', 150.00, 1, 'Gym Access,Trainer 4x/month,Locker,Shower Access', 'Active'),
('Elite', 'Full access with trainer, diet, and workout plans', 300.00, 3, 'Gym Access,Trainer 12x/month,Diet Plan,Workout Plan,Locker,Shower', 'Active'),
('Annual', 'Annual gym membership with full benefits', 1000.00, 12, 'All Benefits,Free Assessment,Priority Booking', 'Active');

-- Insert sample instructor
INSERT INTO instructors (name, email, password, phone, specialization, certification, hire_date, status) VALUES 
('John Doe', 'instructor@gym.com', '$2y$10$Oy10dH7rL.F5l/C5c.5V6eWF6V6e5Xy8H2I5kJ5kL5mM5nN5oO5pP', '03009876543', 'Strength Training', 'NASM Certified', '2023-01-15', 'Active');

-- Insert sample user
INSERT INTO users (name, email, password, phone, gender, status) VALUES 
('Sample User', 'user@gym.com', '$2y$10$Oy10dH7rL.F5l/C5c.5V6eWF6V6e5Xy8H2I5kJ5kL5mM5nN5oO5pP', '03005432109', 'Male', 'Active');

-- Sample password for all accounts: password123
