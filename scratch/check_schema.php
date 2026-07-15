<?php
require_once('config/functions.php');

$tables = ['instructors', 'packages', 'subscriptions', 'package_videos'];

foreach ($tables as $table) {
    echo "\n--- $table ---\n";
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    if ($res->num_rows > 0) {
        $res = $conn->query("DESCRIBE $table");
        while($row = $res->fetch_assoc()) {
            echo "{$row['Field']} - {$row['Type']}\n";
        }
    } else {
        echo "Table does not exist.\n";
    }
}
?>
