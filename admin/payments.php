<?php
/**
 * View Payments - Admin Panel
 */

require_once('../config/functions.php');
require_once('../config/receipt_generator.php');
requireAdmin();

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_payment_status') {
        try {
            $paymentId = sanitize($_POST['payment_id'] ?? '');
            $status = sanitize($_POST['status'] ?? '');
            $oldStatus = sanitize($_POST['old_status'] ?? '');
            
            $query = "UPDATE payments SET status = ? WHERE payment_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $status, $paymentId);
            
            if ($stmt->execute()) {
                // Get full payment + user details
                $paymentQuery = "SELECT p.*, u.name, u.email, u.user_id FROM payments p LEFT JOIN users u ON p.user_id = u.user_id WHERE p.payment_id = ?";
                $paymentStmt = $conn->prepare($paymentQuery);
                $paymentStmt->bind_param("i", $paymentId);
                $paymentStmt->execute();
                $payment = $paymentStmt->get_result()->fetch_assoc();

                // Auto-generate receipt & notify user when marked Paid
                if ($status === 'Paid' && $oldStatus !== 'Paid' && $payment && isset($payment['user_id']) && $payment['user_id']) {
                    try {
                        // 1. Generate the receipt file
                        $userData    = ['name' => $payment['name'], 'email' => $payment['email']];
                        $receiptInfo = generatePaymentReceipt($payment, $userData);

                        // 2. Save receipt path to payments table
                        $rpStmt = $conn->prepare("UPDATE payments SET receipt_path = ? WHERE payment_id = ?");
                        $rpStmt->bind_param("si", $receiptInfo['public_path'], $paymentId);
                        $rpStmt->execute();

                        // 3. Build receipt URL for the notification
                        $receiptFilename = $receiptInfo['filename'];
                        $receiptUrl      = '../user/receipt.php?file=' . urlencode($receiptFilename);

                        // 4. Send professional in-app notification with receipt link
                        $notificationTitle = "✅ Payment Confirmed — Receipt #" . $receiptInfo['receipt_id'];
                        $notificationMessage =
                            "Hello " . htmlspecialchars($payment['name'] ?? 'Member') . ",\n\n" .
                            "Great news! Your payment of " . formatCurrency($payment['amount']) . " has been verified and confirmed.\n\n" .
                            "Your payment status is now: ✅ PAID & VERIFIED\n" .
                            "Your account is fully active and up to date.\n\n" .
                            "📄 Payment Details:\n" .
                            "• Reference: " . $receiptInfo['receipt_id'] . "\n" .
                            "• Amount: " . formatCurrency($payment['amount']) . "\n" .
                            "• Method: " . htmlspecialchars($payment['payment_method'] ?? 'Cash') . "\n" .
                            "• Date: " . formatDate($payment['payment_date']) . "\n\n" .
                            "🧾 Your digital receipt has been generated. Go to My Payments → View Receipt to download it.\n\n" .
                            "Thank you for being a valued member of Project Rock Ascent!";

                        createNotification($payment['user_id'], $_SESSION['admin_id'], 'Admin', $notificationTitle, $notificationMessage);
                        redirect('payments.php', '✅ Payment confirmed, receipt generated & notification sent to ' . htmlspecialchars($payment['name']), 'success');
                    } catch (Exception $e) {
                        redirect('payments.php', 'Payment updated but receipt/notification failed: ' . $e->getMessage(), 'warning');
                    }
                } else {
                    redirect('payments.php', 'Payment status updated', 'success');
                }
            } else {
                redirect('payments.php', 'Error updating payment status', 'danger');
            }
        } catch (Exception $e) {
            ob_end_clean();
            redirect('payments.php', 'Error: ' . $e->getMessage(), 'danger');
        }
    }
}

// Get filter
$filter = sanitize($_GET['filter'] ?? 'all');
try {
    $query = "SELECT p.*, u.name FROM payments p LEFT JOIN users u ON p.user_id = u.user_id";
    if ($filter !== 'all') {
        $query .= " WHERE p.status = ?";
        $stmt = $conn->prepare($query . " ORDER BY p.created_at DESC");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $filter);
    } else {
        $stmt = $conn->prepare($query . " ORDER BY p.created_at DESC");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
    }
    $stmt->execute();
    $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $payments = [];
    $error_message = "Error loading payments: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payments - Admin</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/admin_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">💳 Manage Payments</h2>
                    <div class="d-flex gap-2">
                        <a href="?filter=all" class="btn btn-sm btn-outline-light <?php echo $filter == 'all' ? 'active' : ''; ?>" style="border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">All</a>
                        <a href="?filter=Paid" class="btn btn-sm btn-outline-success <?php echo $filter == 'Paid' ? 'active' : ''; ?>" style="border-radius: 10px; border: 1px solid rgba(40, 167, 69, 0.3);">Paid</a>
                        <a href="?filter=Pending" class="btn btn-sm btn-outline-warning <?php echo $filter == 'Pending' ? 'active' : ''; ?>" style="border-radius: 10px; border: 1px solid rgba(255, 193, 7, 0.3);">Pending</a>
                    </div>
                </div>

                <?php displayMessage(); ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">💰 Payment Transactions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">ID</th>
                                        <th>Member Details</th>
                                        <th>Transaction</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th style="text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (!empty($payments)) {
                                        foreach ($payments as $payment) {
                                            $statusBadge = $payment['status'] === 'Paid' ? 'badge-success' : 
                                                          ($payment['status'] === 'Pending' ? 'badge-warning' : 'badge-danger');
                                            
                                            $paymentId = htmlspecialchars($payment['payment_id'], ENT_QUOTES, 'UTF-8');
                                            $memberName = htmlspecialchars($payment['name'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
                                            $amountVal = $payment['amount'];
                                            $method = htmlspecialchars($payment['payment_method'], ENT_QUOTES, 'UTF-8');
                                            $status = htmlspecialchars($payment['status'], ENT_QUOTES, 'UTF-8');
                                            $paymentDate = htmlspecialchars($payment['payment_date'], ENT_QUOTES, 'UTF-8');
                                            $transactionId = htmlspecialchars($payment['transaction_id'] ?? '', ENT_QUOTES, 'UTF-8');
                                            
                                            echo "
                                            <tr>
                                                <td class='text-center'><strong>#" . $paymentId . "</strong></td>
                                                <td>
                                                    <div class='font-weight-bold'>" . $memberName . "</div>
                                                    <div class='text-muted small'>" . formatDate($payment['payment_date']) . "</div>
                                                </td>
                                                <td>
                                                    <div class='font-weight-bold text-success'>" . formatCurrency($amountVal) . "</div>
                                                    <div class='text-muted small'>" . $transactionId . "</div>
                                                </td>
                                                <td><span class='badge badge-dark p-2' style='border: 1px solid rgba(255,255,255,0.1);'><i class='fas fa-credit-card mr-1'></i> " . $method . "</span></td>
                                                <td><span class='badge $statusBadge'>" . $status . "</span></td>
                                                <td class='text-center'>
                                                    <div class='dropdown'>
                                                        <button class='btn btn-sm btn-outline-light dropdown-toggle px-3' type='button' data-toggle='dropdown' style='border-radius: 12px; border: 1px solid rgba(255,255,255,0.2);'>
                                                            <i class='fas fa-cog mr-1'></i> Actions
                                                        </button>
                                                        <div class='dropdown-menu dropdown-menu-right'>";
                                            
                                            if ($payment['status'] !== 'Paid') {
                                                echo "
                                                            <a class='dropdown-item py-2' href='#' onclick=\"setPaymentData('$paymentId', '$memberName', '$amountVal', '$status')\" data-toggle='modal' data-target='#confirmPaidModal' style='color: #cbd5e1;'>
                                                                <i class='fas fa-check mr-2 text-success'></i> Mark as Paid
                                                            </a>";
                                            }
                                            
                                            echo "
                                                            <a class='dropdown-item py-2' href='#' onclick=\"showPaymentDetails('$paymentId', '$memberName', '$amountVal', '$method', '$status', '$paymentDate', '$transactionId')\" data-toggle='modal' data-target='#paymentDetailsModal' style='color: #cbd5e1;'>
                                                                <i class='fas fa-eye mr-2 text-info'></i> View Details
                                                            </a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            ";
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

    <!-- Confirm Payment Received Modal -->
    <div class="modal fade" id="confirmPaidModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">✅ Mark Payment as Received</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to mark this payment as <strong>Paid</strong>?</p>
                    <div class="p-3 rounded mb-3" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                        <p><strong>Member:</strong> <span id="paymentUserName"></span></p>
                        <p><strong>Amount:</strong> <span id="paymentAmount"></span></p>
                        <p><strong>Current Status:</strong> <span id="paymentCurrentStatus" class="badge badge-warning"></span></p>
                    </div>
                    <p class="text-muted"><small><i class="fas fa-bell"></i> A confirmation notification will be automatically sent to the member.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="update_payment_status">
                        <input type="hidden" name="payment_id" id="confirmPaymentId">
                        <input type="hidden" name="status" value="Paid">
                        <input type="hidden" name="old_status" id="confirmOldStatus">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Confirm & Send Notification
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Details Modal -->
    <div class="modal fade" id="paymentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">💳 Payment Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Member Name:</strong> <span id="detailMemberName"></span></p>
                            <p><strong>Payment Amount:</strong> <span id="detailAmount"></span></p>
                            <p><strong>Payment Method:</strong> <span id="detailMethod"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> <span id="detailStatus"></span></p>
                            <p><strong>Payment Date:</strong> <span id="detailDate"></span></p>
                            <p><strong>Transaction ID:</strong> <span id="detailTransactionId" class="text-muted"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/scripts.php'); ?>
    <style>
        .btn-xs {
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }
        .table td {
            vertical-align: middle;
        }
        @media (max-width: 768px) {
            .btn-xs {
                display: block;
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
    <script>
        function setPaymentData(paymentId, memberName, amount, oldStatus) {
            document.getElementById('confirmPaymentId').value = paymentId;
            document.getElementById('confirmOldStatus').value = oldStatus;
            document.getElementById('paymentUserName').textContent = memberName;
            document.getElementById('paymentAmount').textContent = amount;
            document.getElementById('paymentCurrentStatus').textContent = oldStatus;
        }

        function showPaymentDetails(paymentId, memberName, amount, method, status, paymentDate, transactionId) {
            document.getElementById('detailMemberName').textContent = memberName;
            document.getElementById('detailAmount').textContent = amount;
            document.getElementById('detailMethod').textContent = method;
            document.getElementById('detailStatus').textContent = status;
            document.getElementById('detailDate').textContent = paymentDate;
            document.getElementById('detailTransactionId').textContent = transactionId || 'N/A';
            
            // Add status badge styling
            const statusElement = document.getElementById('detailStatus');
            if (status === 'Paid') {
                statusElement.className = 'badge badge-success';
            } else if (status === 'Pending') {
                statusElement.className = 'badge badge-warning';
            } else {
                statusElement.className = 'badge badge-danger';
            }
        }
    </script>

</body>
</html>
