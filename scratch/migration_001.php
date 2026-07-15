<?php
require_once('config/functions.php');

// 1. Update instructors table
$check_skills = $conn->query("SHOW COLUMNS FROM instructors LIKE 'skills'");
if ($check_skills->num_rows == 0) {
    $conn->query("ALTER TABLE instructors ADD COLUMN skills TEXT AFTER specialization, ADD COLUMN experience TEXT AFTER skills");
    echo "Added skills and experience columns to instructors.\n";
}

// 2. Create package_videos table
$conn->query("CREATE TABLE IF NOT EXISTS package_videos (
    video_id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT,
    instructor_id INT,
    title VARCHAR(255),
    description TEXT,
    video_url VARCHAR(255),
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
echo "Ensured package_videos table exists.\n";
?>
