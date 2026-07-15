<?php
/**
 * Professional Receipt Generator
 * Generates a fintech-style HTML receipt and saves it to assets/receipts/
 */

function generatePaymentReceipt($paymentData, $userData) {
    $receiptId  = 'RCT-' . strtoupper(substr(md5($paymentData['payment_id'] . time()), 0, 10));
    $filename   = 'receipt_' . $paymentData['payment_id'] . '_' . time() . '.html';
    $savePath   = __DIR__ . '/../assets/receipts/' . $filename;
    $publicPath = 'assets/receipts/' . $filename;

    $amount      = number_format($paymentData['amount'], 2);
    $memberName  = htmlspecialchars($userData['name'] ?? 'Member');
    $memberEmail = htmlspecialchars($userData['email'] ?? 'N/A');
    $method      = htmlspecialchars($paymentData['payment_method'] ?? 'Cash');
    $date        = date('d M Y, h:i A', strtotime($paymentData['updated_at'] ?? $paymentData['payment_date']));
    $gymName     = 'Project Rock Ascent';
    $gymTagline  = 'Premium Fitness & Wellness';

    // Build barcode bars HTML
    $barcodesHtml = '';
    foreach ([3,1,2,1,3,2,1,3,1,2,3,1,2,1,3,2,1,2,3,1] as $w) {
        $barcodesHtml .= "<div class='bar' style='width:{$w}px;'></div>";
    }

    // Use output buffering — avoids heredoc indentation issues entirely
    ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt #<?php echo $receiptId; ?> &mdash; <?php echo $gymName; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'Inter',sans-serif; background:#060b18; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:40px 20px; }
.page-wrap { width:100%; max-width:500px; margin:0 auto; }
.gym-header { text-align:center; margin-bottom:28px; }
.gym-logo-ring { width:64px; height:64px; border-radius:50%; background:linear-gradient(135deg,#f59e0b,#ec4899); display:flex; align-items:center; justify-content:center; margin:0 auto 12px; font-size:28px; box-shadow:0 0 30px rgba(245,158,11,0.4); }
.gym-name { color:#fff; font-size:20px; font-weight:800; }
.gym-tagline { color:#64748b; font-size:12px; font-weight:500; margin-top:3px; }
.receipt-card { background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%); border-radius:28px; border:1px solid rgba(255,255,255,0.07); overflow:hidden; box-shadow:0 40px 80px rgba(0,0,0,0.6); position:relative; }
.success-header { padding:44px 40px 32px; text-align:center; border-bottom:1px solid rgba(255,255,255,0.06); }
.success-ring { width:80px; height:80px; border-radius:50%; background:rgba(34,197,94,0.12); border:2px solid rgba(34,197,94,0.3); display:flex; align-items:center; justify-content:center; margin:0 auto 20px; }
.checkmark { width:36px; height:36px; background:linear-gradient(135deg,#22c55e,#16a34a); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:18px; box-shadow:0 8px 20px rgba(34,197,94,0.4); }
.paid-label { display:inline-flex; align-items:center; gap:6px; background:rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.25); color:#4ade80; font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; padding:5px 14px; border-radius:30px; margin-bottom:16px; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }
.paid-dot { width:6px; height:6px; border-radius:50%; background:#4ade80; display:inline-block; animation:blink 1.5s infinite; }
.amount-display { font-size:52px; font-weight:900; background:linear-gradient(135deg,#f8fafc,#cbd5e1); -webkit-background-clip:text; -webkit-text-fill-color:transparent; letter-spacing:-2px; line-height:1; margin-bottom:6px; }
.amount-label { color:#64748b; font-size:13px; font-weight:500; }
.divider-row { position:relative; height:1px; background:rgba(255,255,255,0.06); margin:0; }
.circle-left { position:absolute; left:-16px; top:50%; transform:translateY(-50%); width:32px; height:32px; background:#060b18; border-radius:50%; }
.circle-right { position:absolute; right:-16px; top:50%; transform:translateY(-50%); width:32px; height:32px; background:#060b18; border-radius:50%; }
.dashed-line { position:absolute; top:50%; left:20px; right:20px; height:0; border-top:1.5px dashed rgba(255,255,255,0.08); transform:translateY(-50%); }
.details-section { padding:28px 36px 32px; }
.section-title { font-size:10px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#475569; margin-bottom:18px; }
.detail-row { display:flex; justify-content:space-between; align-items:center; padding:13px 0; border-bottom:1px solid rgba(255,255,255,0.04); }
.detail-row:last-child { border-bottom:none; }
.detail-label { font-size:12px; color:#64748b; font-weight:500; display:flex; align-items:center; gap:8px; }
.detail-icon { width:28px; height:28px; border-radius:8px; background:rgba(255,255,255,0.04); display:flex; align-items:center; justify-content:center; font-size:13px; }
.detail-value { font-size:13px; color:#e2e8f0; font-weight:600; text-align:right; }
.highlight { color:#4ade80; background:rgba(34,197,94,0.08); padding:4px 10px; border-radius:20px; font-size:12px; }
.ref-value { font-family:'Courier New',monospace; font-size:12px; color:#f59e0b; font-weight:700; }
.barcode-wrap { margin:0 36px 28px; padding:18px 20px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.05); border-radius:16px; display:flex; align-items:center; gap:16px; }
.barcode-lines { display:flex; align-items:stretch; gap:2px; height:40px; flex-shrink:0; }
.bar { background:rgba(255,255,255,0.15); border-radius:1px; }
.barcode-ref { font-family:monospace; font-size:10px; color:#475569; letter-spacing:1px; }
.barcode-sub { font-size:11px; color:#64748b; margin-top:3px; }
.receipt-footer { padding:20px 36px 28px; border-top:1px solid rgba(255,255,255,0.05); text-align:center; }
.footer-msg { font-size:13px; color:#64748b; line-height:1.6; margin-bottom:16px; }
.footer-msg strong { color:#94a3b8; }
.print-btn { display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,#f59e0b,#ec4899); color:white; font-weight:700; font-size:13px; padding:12px 28px; border-radius:30px; border:none; cursor:pointer; font-family:'Inter',sans-serif; box-shadow:0 8px 20px rgba(245,158,11,0.3); }
.powered { margin-top:16px; font-size:10px; color:#334155; letter-spacing:1px; text-transform:uppercase; }
@media print { .print-btn { display:none; } }
</style>
</head>
<body>
<div class="page-wrap">
  <div class="gym-header">
    <div class="gym-logo-ring">&#127947;</div>
    <div class="gym-name"><?php echo $gymName; ?></div>
    <div class="gym-tagline"><?php echo $gymTagline; ?></div>
  </div>
  <div class="receipt-card">
    <div class="success-header">
      <div class="success-ring">
        <div class="checkmark">&#10003;</div>
      </div>
      <div class="paid-label"><span class="paid-dot"></span> Payment Successful</div>
      <div class="amount-display">PKR <?php echo $amount; ?></div>
      <div class="amount-label">Total Amount Paid</div>
    </div>
    <div class="divider-row">
      <div class="circle-left"></div>
      <div class="circle-right"></div>
      <div class="dashed-line"></div>
    </div>
    <div class="details-section">
      <div class="section-title">Transaction Details</div>
      <div class="detail-row">
        <div class="detail-label"><div class="detail-icon">&#128222;</div> Reference No.</div>
        <div class="ref-value"><?php echo $receiptId; ?></div>
      </div>
      <div class="detail-row">
        <div class="detail-label"><div class="detail-icon">&#128100;</div> Member Name</div>
        <div class="detail-value"><?php echo $memberName; ?></div>
      </div>
      <div class="detail-row">
        <div class="detail-label"><div class="detail-icon">&#128231;</div> Email</div>
        <div class="detail-value"><?php echo $memberEmail; ?></div>
      </div>
      <div class="detail-row">
        <div class="detail-label"><div class="detail-icon">&#128179;</div> Payment Method</div>
        <div class="detail-value"><?php echo $method; ?></div>
      </div>
      <div class="detail-row">
        <div class="detail-label"><div class="detail-icon">&#128197;</div> Payment Date</div>
        <div class="detail-value"><?php echo $date; ?></div>
      </div>
      <div class="detail-row">
        <div class="detail-label"><div class="detail-icon">&#9989;</div> Status</div>
        <div class="detail-value highlight">&#9679; Paid &amp; Verified</div>
      </div>
    </div>
    <div class="barcode-wrap">
      <div class="barcode-lines"><?php echo $barcodesHtml; ?></div>
      <div class="barcode-info">
        <div class="barcode-ref"><?php echo $receiptId; ?></div>
        <div class="barcode-sub"><?php echo $gymName; ?> &middot; Payment Confirmation</div>
      </div>
    </div>
    <div class="receipt-footer">
      <div class="footer-msg">
        Thank you, <strong><?php echo $memberName; ?></strong>! Your payment has been verified and your account is fully active.<br>
        Keep this receipt for your records.
      </div>
      <button class="print-btn" onclick="window.print()">&#128424; Print / Save as PDF</button>
      <div class="powered">Secured by <?php echo $gymName; ?> &nbsp;&bull;&nbsp; Generated <?php echo $date; ?></div>
    </div>
  </div>
</div>
</body>
</html>
<?php
    $html = ob_get_clean();

    // Save the receipt file
    if (!is_dir(dirname($savePath))) {
        mkdir(dirname($savePath), 0755, true);
    }
    file_put_contents($savePath, $html);

    return [
        'receipt_id'  => $receiptId,
        'filename'    => $filename,
        'public_path' => $publicPath,
        'full_path'   => $savePath,
    ];
}
