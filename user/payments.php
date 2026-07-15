<?php
require_once('../config/functions.php');
requireUser();

$userId = $_SESSION['user_id'];
$payments = $conn->query("SELECT * FROM payments WHERE user_id = $userId ORDER BY payment_date DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payments - Member</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Unified Sidebar -->
            <?php include('../includes/user_sidebar.php'); ?>

            <div class="main-content">
                <!-- Premium Page Header -->
                <div class="welcome-section mb-5 fade-in-up">
                    <h1 class="mb-2">💳 Payment <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">History</span></h1>
                    <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Review your invoices, memberships, and transaction history.</p>
                </div>

                <?php displayMessage(); ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Payments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table tr table-hover">
                                 <thead>
                                    <tr>
                                        <th>Payment ID</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($payments)) {
                                        foreach ($payments as $payment) {
                                            $badge = $payment['status'] === 'Paid' ? 'badge-success' : 
                                                    ($payment['status'] === 'Pending' ? 'badge-warning' : 'badge-danger');
                                            
                                            // Receipt button
                                            $receiptBtn = '';
                                            if ($payment['status'] === 'Paid' && !empty($payment['receipt_path'])) {
                                                $fname = basename($payment['receipt_path']);
                                                $receiptBtn = "<a href='receipt.php?file=" . urlencode($fname) . "' target='_blank' 
                                                    class='btn btn-sm' style='background:linear-gradient(135deg,#f59e0b,#ec4899);color:white;border-radius:8px;font-size:11px;padding:4px 10px;font-weight:600;'>
                                                    🧾 View Receipt</a>";
                                            } elseif ($payment['status'] === 'Pending') {
                                                $receiptBtn = "<span class='badge badge-secondary' style='font-size:10px;'>Awaiting Confirmation</span>";
                                            } else {
                                                $receiptBtn = "<span class='text-muted' style='font-size:11px;'>—</span>";
                                            }
                                            
                                            echo "<tr>
                                                <td>#" . $payment['payment_id'] . "</td>
                                                <td>" . formatCurrency($payment['amount']) . "</td>
                                                <td>" . formatDate($payment['payment_date']) . "</td>
                                                <td>" . $payment['payment_method'] . "</td>
                                                <td><span class='badge $badge'>" . $payment['status'] . "</span></td>
                                                <td>$receiptBtn</td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='6' class='text-center text-muted'>No payments found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
</body>
</html>
