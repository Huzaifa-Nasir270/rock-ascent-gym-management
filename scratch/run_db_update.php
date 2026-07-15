<?php
require_once('config/db.php');

$queries = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS height DECIMAL(5,2) DEFAULT NULL AFTER gender",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS weight DECIMAL(5,2) DEFAULT NULL AFTER height",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS age INT DEFAULT NULL AFTER weight",
    "ALTER TABLE users MODIFY COLUMN fitness_goal VARCHAR(100) DEFAULT 'Stay Fit'"
];

foreach ($queries as $query) {
    if ($conn->query($query)) {
        echo "Successfully executed: $query\n";
    } else {
        echo "Error executing query: " . $conn->error . "\n";
    }
}
?>
