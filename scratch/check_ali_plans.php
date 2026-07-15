<?php
require_once('config/db.php');
$userId = 36;
echo "--- Workout Plans ---\n";
$r = $conn->query("SELECT * FROM workout_plans WHERE user_id = $userId");
while($row = $r->fetch_assoc()) { print_r($row); }

echo "\n--- Diet Plans ---\n";
$r = $conn->query("SELECT * FROM diet_plans WHERE user_id = $userId");
while($row = $r->fetch_assoc()) { print_r($row); }
?>
