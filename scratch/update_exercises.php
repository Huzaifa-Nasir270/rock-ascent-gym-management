<?php
require_once('config/functions.php');

$updates = [
    'Bench Press' => 'assets/images/bench press.png',
    'Lat Pulldown' => 'assets/images/Lat Pulldown.png'
];

foreach ($updates as $name => $path) {
    $stmt = $conn->prepare("UPDATE exercises SET media_path = ?, instructor_id = NULL WHERE name = ?");
    $stmt->bind_param("ss", $path, $name);
    $stmt->execute();
    echo "Updated $name to $path\n";
}
echo "Done updating existing exercises.\n";
