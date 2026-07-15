<?php
/**
 * User - Subscription Checkout Payment
 */

require_once('../config/functions.php');
requireUser();

$userId = $_SESSION['user_id'];

if (!isset($_SESSION['checkout_payment_id'])) {
    redirect('packages.php', 'No pending payment found.', 'warning');
}

$paymentId = $_SESSION['checkout_payment_id'];

// Get payment details
$query = "SELECT p.*, sub.package_id, pkg.name as package_name FROM payments p 
          JOIN subscriptions sub ON p.subscription_id = sub.subscription_id 
          JOIN packages pkg ON sub.package_id = pkg.package_id 
          WHERE p.payment_id = ? AND p.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $paymentId, $userId);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();

if (!$payment || $payment['status'] === 'Paid') {
    unset($_SESSION['checkout_payment_id']);
    redirect('packages.php', 'Payment already processed or invalid.', 'warning');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pay_now'])) {
    // Simulate successful payment
    $transactionId = 'TXN-' . strtoupper(uniqid());
    
    $updateQ = "UPDATE payments SET status = 'Paid', payment_method = 'Card', transaction_id = ? WHERE payment_id = ?";
    $ustmt = $conn->prepare($updateQ);
    $ustmt->bind_param("si", $transactionId, $paymentId);
    
    if ($ustmt->execute()) {
        unset($_SESSION['checkout_payment_id']);
        redirect('payments.php', 'Payment successful! Your subscription is now fully active. Transaction ID: ' . $transactionId, 'success');
    } else {
        $error = "Payment failed. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .checkout-container {
            max-width: 600px;
            margin: 50px auto;
            background: var(--card-glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 40px;
            box-shadow: var(--glass-shadow);
        }
        .form-control {
            background: rgba(255,255,255,0.05) !important;
            border: 1px solid rgba(255,255,255,0.1) !important;
            color: white !important;
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container">
        <div class="checkout-container">
            <h3 class="text-center mb-4"><i class="fas fa-lock text-success mr-2"></i>Secure Checkout</h3>
            
            <?php displayMessage(); ?>
            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <div class="card bg-dark mb-4" style="border: 1px solid rgba(255,255,255,0.1);">
                <div class="card-body">
                    <h5 class="card-title text-warning"><?php echo htmlspecialchars($payment['package_name']); ?> Package</h5>
                    <p class="card-text mb-0">Total Amount Due:</p>
                    <h2 class="font-weight-bold"><?php echo formatCurrency($payment['amount']); ?></h2>
                </div>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Cardholder Name</label>
                    <input type="text" class="form-control" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label>Card Number</label>
                    <div class="input-group">
                        <input type="text" class="form-control" required placeholder="0000 0000 0000 0000" maxlength="19">
                        <div class="input-group-append">
                            <span class="input-group-text bg-dark border-0"><i class="fab fa-cc-visa text-light"></i></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="text" class="form-control" required placeholder="MM/YY" maxlength="5">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>CVC</label>
                            <input type="password" class="form-control" required placeholder="123" maxlength="3">
                        </div>
                    </div>
                </div>
                <button type="submit" name="pay_now" class="btn btn-primary btn-block btn-lg mt-4" style="border-radius: 12px; font-weight: bold;">
                    Pay <?php echo formatCurrency($payment['amount']); ?>
                </button>
                <a href="packages.php" class="btn btn-outline-secondary btn-block mt-2" style="border-radius: 12px;">Cancel</a>
            </form>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>
</body>
</html>
