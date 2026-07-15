<?php
/**
 * User - View Payment Receipt
 */
require_once('../config/functions.php');
requireUser();

$userId = $_SESSION['user_id'];
$file   = basename($_GET['file'] ?? '');

if (empty($file)) {
    redirect('payments.php', 'No receipt specified.', 'danger');
}

// Security: ensure the receipt belongs to this user by checking DB
$stmt = $conn->prepare("SELECT p.*, u.name, u.email FROM payments p LEFT JOIN users u ON p.user_id = u.user_id WHERE p.user_id = ? AND p.receipt_path LIKE ?");
$like = '%' . $file . '%';
$stmt->bind_param("is", $userId, $like);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment) {
    redirect('payments.php', 'Receipt not found or access denied.', 'danger');
}

$receiptPath = __DIR__ . '/../assets/receipts/' . $file;
if (!file_exists($receiptPath)) {
    redirect('payments.php', 'Receipt file not found. It may have been cleared.', 'warning');
}

// Read the receipt HTML and serve it inline
$receiptHtml = file_get_contents($receiptPath);
echo $receiptHtml;
exit;
