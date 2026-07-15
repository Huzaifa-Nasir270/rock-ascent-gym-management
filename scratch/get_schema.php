<?php
require 'config/db.php';
$tables = ['shop_products', 'shop_trainer_endorsements', 'user_instructor_assignments', 'users', 'instructors'];
foreach ($tables as $table) {
    $res = $conn->query("SHOW CREATE TABLE $table");
    if ($res) {
        $row = $res->fetch_row();
        echo $row[1] . "\n\n";
    }
}
