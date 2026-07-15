<?php
require_once('config/db.php');
$result = $conn->query("SHOW COLUMNS FROM users");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
