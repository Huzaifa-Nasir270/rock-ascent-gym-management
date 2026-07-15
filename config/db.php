<?php
/**
 * Database Configuration File
 * Handles database connection for the Gym Management System
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials
define('DB_HOST', 'localhost'); // Database host
define('DB_USER', 'root');      // Database username (default for XAMPP)
define('DB_PASS', '');          // Database password (empty by default in XAMPP)
define('DB_NAME', 'gym_management'); // Database name

// Create connection using mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>
