<?php
/**
 * System Reports - Admin Panel
 */

require_once('../config/functions.php');
requireAdmin();

// Get report data
$monthlyRevenue = $conn->query("
    SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total 
    FROM payments WHERE status = 'Paid' 
    GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
")->fetch_all(MYSQLI_ASSOC);

$monthlyAttendance = $conn->query("
    SELECT DATE_FORMAT(check_in_date, '%Y-%m') as month, COUNT(*) as count 
    FROM attendance 
    GROUP BY DATE_FORMAT(check_in_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
")->fetch_all(MYSQLI_ASSOC);

$toplMembers = $conn->query("
    SELECT users.name, users.email, COUNT(attendance.attendance_id) as days_visited 
    FROM users 
    LEFT JOIN attendance ON users.user_id = attendance.user_id 
    GROUP BY users.user_id 
    ORDER BY days_visited DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

$membershipStats = $conn->query("
    SELECT packages.name, COUNT(subscriptions.subscription_id) as count 
    FROM packages 
    LEFT JOIN subscriptions ON packages.package_id = subscriptions.package_id 
    WHERE subscriptions.status = 'Active'
    GROUP BY packages.package_id
")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reports - Admin</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/admin_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0">📊 System Reports</h2>
                        <div class="small text-muted mt-1">Insights and analytics for gym performance</div>
                    </div>
                    <button id="downloadPdfBtn" class="btn btn-primary px-4" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-file-pdf mr-2"></i> Download PDF
                    </button>
                </div>

                <div id="reportContent">
                    <!-- Monthly Revenue -->
                    <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Monthly Revenue</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Month</th>
                                        <th>Total Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($monthlyRevenue as $revenue) {
                                        echo "<tr><td>" . $revenue['month'] . "</td><td>" . formatCurrency($revenue['total']) . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Top Members -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Top Active Members</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Visits</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($toplMembers as $member) {
                                                echo "<tr><td>" . htmlspecialchars($member['name']) . "</td><td>" . $member['days_visited'] . "</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Membership Distribution -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Active Subscriptions by Package</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Package</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($membershipStats as $stat) {
                                                echo "<tr><td>" . htmlspecialchars($stat['name']) . "</td><td>" . $stat['count'] . "</td></tr>";
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
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
    
    <!-- html2pdf CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        document.getElementById('downloadPdfBtn').addEventListener('click', function () {
            const btn = this;
            const originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Generating...';
            btn.disabled = true;

            const element = document.getElementById('reportContent');
            
            // Temporary styling for better PDF output
            const originalPadding = element.style.padding;
            const originalBg = element.style.background;
            element.style.padding = '20px';
            element.style.background = '#0f172a'; // Match dark theme

            const opt = {
                margin:       10,
                filename:     'System_Report_Project_Rock_Ascent.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, backgroundColor: '#0f172a' },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save().then(() => {
                btn.innerHTML = originalContent;
                btn.disabled = false;
                element.style.padding = originalPadding;
                element.style.background = originalBg;
            }).catch(err => {
                console.error('PDF Generation Error:', err);
                alert('An error occurred while generating the PDF.');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            });
        });
    </script>
</body>
</html>
