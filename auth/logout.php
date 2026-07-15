<?php
/**
 * Logout Processing
 * Handles user logout
 */

ob_start();
session_start();

// Destroy session
session_destroy();

// Redirect to login
ob_end_clean();
header("Location: login.php?message=Logged out successfully");
exit();

?>
