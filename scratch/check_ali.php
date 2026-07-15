<?php
require_once('config/db.php');
$email = 'ali@gmail.com';
$result = $conn->query("SELECT user_id, name, fitness_goal FROM users WHERE email = '$email'");
$row = $result->fetch_assoc();
if ($row) {
    echo "Found user: " . $row['name'] . " (ID: " . $row['user_id'] . ") with goal: '" . $row['fitness_goal'] . "'\n";
} else {
    echo "User not found: $email\n";
}
?>
