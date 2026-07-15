<?php
require 'config/db.php';

$res = $conn->query("SELECT package_id, features FROM packages");
while($row = $res->fetch_assoc()) {
    $f = $row['features'];
    // Replace literal '\r\n' (which is actually in the DB as string "\r\n") with comma
    // Also handle all the backslashes
    $f = str_replace(['\r\n', '\n', '\r'], ',', $f);
    $f = str_replace('\\', '', $f);
    
    // Replace actual newlines with comma
    $f = str_replace(["\r\n", "\n", "\r"], ',', $f);
    
    // Clean up multiple commas and trim
    $f = preg_replace('/,+/', ',', $f);
    $f = trim($f, ',');
    
    $stmt = $conn->prepare("UPDATE packages SET features = ? WHERE package_id = ?");
    $stmt->bind_param("si", $f, $row['package_id']);
    $stmt->execute();
    echo "Updated package " . $row['package_id'] . " to: " . $f . "\n";
}
echo "Done";
