<?php
require 'config/functions.php';
$res = $conn->query("DESCRIBE announcements");
$cols = [];
while($row = $res->fetch_assoc()){
    $cols[] = $row;
}
echo json_encode($cols, JSON_PRETTY_PRINT);
