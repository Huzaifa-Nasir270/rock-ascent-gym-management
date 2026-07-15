<?php
require 'config/db.php';

$res = $conn->query("SELECT package_id, description FROM packages");
while($row = $res->fetch_assoc()) {
    $desc = $row['description'];
    
    // Replace literal '\r\n' (which is actually in the DB as string "\r\n") with real newlines
    $desc = str_replace(['\r\n', '\n', '\r'], "\n", $desc);
    
    // Clean up multiple slashes
    $desc = str_replace('\\', '', $desc);
    
    $stmt = $conn->prepare("UPDATE packages SET description = ? WHERE package_id = ?");
    $stmt->bind_param("si", $desc, $row['package_id']);
    $stmt->execute();
    echo "Updated description for package " . $row['package_id'] . "\n";
}
echo "Done";
