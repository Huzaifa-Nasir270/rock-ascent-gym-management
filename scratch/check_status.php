<?php
require 'config/db.php';
$res = $conn->query("SELECT product_id, name, status, is_exclusive, category_id FROM shop_products");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
