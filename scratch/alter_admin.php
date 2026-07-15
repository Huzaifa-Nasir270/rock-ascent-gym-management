<?php
require __DIR__ . '/../config/functions.php';
$conn->query("ALTER TABLE admin ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");
echo "Done";
?>
