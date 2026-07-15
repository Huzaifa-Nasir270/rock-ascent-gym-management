<?php
$c = new mysqli('localhost', 'root', '', 'gym_management');
$r = $c->query('SHOW CREATE TABLE users');
$row = $r->fetch_assoc();
echo $row['Create Table'];
?>
