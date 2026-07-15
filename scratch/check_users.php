<?php
require_once('config/db.php');
$result = $conn->query("SELECT user_id, name, fitness_goal FROM users LIMIT 10");
while($row = $result->fetch_assoc()) {
    echo "ID: " . $row['user_id'] . " | Name: " . $row['name'] . " | Goal: '" . $row['fitness_goal'] . "'\n";
}
?>
