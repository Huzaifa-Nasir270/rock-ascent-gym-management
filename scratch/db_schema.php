<?php
require 'config/db.php';
$conn->query("ALTER TABLE payments ADD COLUMN IF NOT EXISTS receipt_path VARCHAR(500) DEFAULT NULL");
echo $conn->errno ? "Error: " . $conn->error : "receipt_path column: OK";
echo "\n";
