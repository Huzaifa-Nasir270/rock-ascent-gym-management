<?php
require_once('config/db.php');
$query = "ALTER TABLE users ADD COLUMN fitness_level ENUM('Beginner', 'Intermediate', 'Advanced') DEFAULT 'Beginner' AFTER fitness_goal";
if ($conn->query($query)) {
    echo "Column fitness_level added successfully\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
?>
