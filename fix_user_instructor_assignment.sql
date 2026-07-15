-- Add new table for user-instructor assignments (independent of subscriptions)
CREATE TABLE IF NOT EXISTS user_instructor_assignments (
    assignment_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    instructor_id INT NOT NULL,
    assigned_by INT DEFAULT NULL, -- admin_id who assigned
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    notes TEXT,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (instructor_id) REFERENCES instructors(instructor_id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (user_id, instructor_id)
);

-- Add column to track primary instructor in users table
ALTER TABLE users ADD COLUMN IF NOT EXISTS assigned_instructor_id INT DEFAULT NULL;