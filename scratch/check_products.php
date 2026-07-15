<?php
require 'config/db.php';
$res = $conn->query("SELECT product_id, name, image FROM shop_products ORDER BY created_at DESC");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
