<?php
require 'config/db.php';
$admins = $conn->query("SELECT * FROM admin")->fetch_all(MYSQLI_ASSOC);
foreach($admins as $a) {
    echo "Admin: " . $a['email'] . "\n";
    echo "Hash: " . $a['password'] . "\n";
    echo "Verifies password123: " . (password_verify('password123', $a['password']) ? 'YES' : 'NO') . "\n";
    echo "\n";
}