<?php
require_once('../config/functions.php');
requireUser();

$whatsappNumber = "03265198797";
$formattedNumber = "923265198797"; // For WhatsApp API
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Contact Us - Project Rock Ascent</title>
    <?php include('../includes/head.php'); ?>
    <style>
        .contact-btn {
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(15, 23, 42, 0.4);
            color: white !important;
            text-decoration: none !important;
            display: block;
            margin-bottom: 20px;
        }
        .contact-btn:hover {
            transform: translateY(-5px);
            border-color: var(--primary-color);
            box-shadow: 0 15px 30px rgba(0,0,0,0.4);
        }
        .contact-btn i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            display: block;
        }
        .btn-whatsapp:hover { background: rgba(37, 211, 102, 0.15) !important; color: #25d366 !important; }
        .btn-call:hover { background: rgba(59, 130, 246, 0.15) !important; color: #3b82f6 !important; }
        .btn-sms:hover { background: rgba(245, 158, 11, 0.15) !important; color: #f59e0b !important; }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    <div class="container-fluid px-4"><div class="dashboard-container">
        <!-- Unified Sidebar -->
        <?php include('../includes/user_sidebar.php'); ?>

        <div class="main-content">
            <!-- Premium Page Header -->
            <div class="welcome-section mb-5 fade-in-up">
                <h1 class="mb-2">📞 Contact <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Support</span></h1>
                <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Get in touch with us for any questions, support, or feedback.</p>
            </div>
            
            <div class="row mt-4">
                <!-- Contact Methods Column -->
                <div class="col-lg-5 mb-4">
                    <div class="card h-100 fade-in-up" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; animation-delay: 0.1s;">
                        <div class="card-body p-4 p-md-5">
                            <h3 class="font-weight-bold mb-4">Get in <span class="text-warning">Touch</span></h3>
                            <p class="text-light mb-5" style="opacity: 0.8; font-size: 0.95rem;">Have questions about memberships, training plans, or our facilities? Reach out to us through any of the channels below.</p>
                            
                            <a href="https://wa.me/<?php echo $formattedNumber; ?>" target="_blank" class="contact-btn btn-whatsapp d-flex align-items-center text-left p-3 mb-3" style="background: rgba(37, 211, 102, 0.1); border: 1px solid rgba(37, 211, 102, 0.2);">
                                <div class="icon-wrapper mr-4" style="background: rgba(37, 211, 102, 0.2); padding: 15px; border-radius: 15px;">
                                    <i class="fab fa-whatsapp mb-0" style="color: #25d366; font-size: 1.8rem;"></i>
                                </div>
                                <div>
                                    <h5 class="font-weight-bold text-white mb-1">WhatsApp Chat</h5>
                                    <p class="text-muted small mb-0">Instant messaging support</p>
                                </div>
                            </a>

                            <a href="tel:<?php echo $whatsappNumber; ?>" class="contact-btn btn-call d-flex align-items-center text-left p-3 mb-3" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);">
                                <div class="icon-wrapper mr-4" style="background: rgba(59, 130, 246, 0.2); padding: 15px; border-radius: 15px;">
                                    <i class="fas fa-phone-volume mb-0" style="color: #3b82f6; font-size: 1.8rem;"></i>
                                </div>
                                <div>
                                    <h5 class="font-weight-bold text-white mb-1">Direct Call</h5>
                                    <p class="text-muted small mb-0">Speak with our front desk</p>
                                </div>
                            </a>

                            <a href="sms:<?php echo $whatsappNumber; ?>" class="contact-btn btn-sms d-flex align-items-center text-left p-3 mb-4" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2);">
                                <div class="icon-wrapper mr-4" style="background: rgba(245, 158, 11, 0.2); padding: 15px; border-radius: 15px;">
                                    <i class="fas fa-comment-sms mb-0" style="color: #f59e0b; font-size: 1.8rem;"></i>
                                </div>
                                <div>
                                    <h5 class="font-weight-bold text-white mb-1">Send SMS</h5>
                                    <p class="text-muted small mb-0">Quick text inquiries</p>
                                </div>
                            </a>

                            <hr style="border-color: rgba(255,255,255,0.1); margin: 30px 0;">

                            <!-- Social Links -->
                            <h6 class="font-weight-bold text-uppercase text-muted mb-3" style="letter-spacing: 1px;">Follow Us</h6>
                            <div class="d-flex gap-3">
                                <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-light rounded-circle p-0 d-flex align-items-center justify-content-center mr-2" style="width: 45px; height: 45px; transition: all 0.3s;" onmouseover="this.style.background='#1877f2'; this.style.borderColor='#1877f2'" onmouseout="this.style.background='transparent'; this.style.borderColor='rgba(255,255,255,0.5)'">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-light rounded-circle p-0 d-flex align-items-center justify-content-center mr-2" style="width: 45px; height: 45px; transition: all 0.3s;" onmouseover="this.style.background='#e4405f'; this.style.borderColor='#e4405f'" onmouseout="this.style.background='transparent'; this.style.borderColor='rgba(255,255,255,0.5)'">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="https://www.twitter.com" target="_blank" rel="noopener noreferrer" class="btn btn-outline-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; transition: all 0.3s;" onmouseover="this.style.background='#1da1f2'; this.style.borderColor='#1da1f2'" onmouseout="this.style.background='transparent'; this.style.borderColor='rgba(255,255,255,0.5)'">
                                    <i class="fab fa-twitter"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form & Map Column -->
                <div class="col-lg-7 mb-4">
                    <!-- Quick Message Form -->
                    <div class="card mb-4 fade-in-up" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; animation-delay: 0.2s;">
                        <div class="card-body p-4 p-md-5">
                            <h4 class="font-weight-bold mb-4">Send a <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Quick Message</span></h4>
                            <form action="feedback.php" method="GET">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="small text-muted font-weight-bold text-uppercase letter-spacing-1">Your Name</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" readonly style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 12px; padding: 12px 15px;">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="small text-muted font-weight-bold text-uppercase letter-spacing-1">Inquiry Type</label>
                                        <select class="form-control" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 12px; height: 50px;">
                                            <option style="background: #1e293b;">Membership</option>
                                            <option style="background: #1e293b;">Personal Training</option>
                                            <option style="background: #1e293b;">Billing Support</option>
                                            <option style="background: #1e293b;">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="small text-muted font-weight-bold text-uppercase letter-spacing-1">Message</label>
                                    <textarea class="form-control" rows="4" placeholder="How can we help you today?" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 12px; padding: 15px;"></textarea>
                                </div>
                                <button type="button" onclick="window.location.href='feedback.php'" class="btn btn-primary btn-lg w-100" style="border-radius: 12px; background: var(--primary-gradient); border: none; font-weight: 800; box-shadow: 0 10px 20px rgba(245, 158, 11, 0.2);">
                                    <i class="fas fa-paper-plane mr-2"></i> Go to Official Feedback Form
                                </button>
                                <small class="d-block text-center mt-3 text-muted">For official support tickets, please use the dedicated feedback system.</small>
                            </form>
                        </div>
                    </div>

                    <!-- Location Card -->
                    <div class="card fade-in-up" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; animation-delay: 0.3s; overflow: hidden;">
                        <div class="row no-gutters">
                            <div class="col-md-5 p-4 p-md-5 d-flex flex-column justify-content-center">
                                <h5 class="font-weight-bold mb-4 text-white">Our Location</h5>
                                <div class="d-flex align-items-start mb-3">
                                    <i class="fas fa-map-marker-alt mt-1 mr-3 text-warning"></i>
                                    <div>
                                        <h6 class="font-weight-bold text-white mb-1">Headquarters</h6>
                                        <p class="small text-muted mb-0">123 Fitness Boulevard,<br>Downtown Gym District,<br>City 45678</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-clock mt-1 mr-3 text-warning"></i>
                                    <div>
                                        <h6 class="font-weight-bold text-white mb-1">Working Hours</h6>
                                        <p class="small text-muted mb-0">Mon-Sat: 6 AM - 10 PM<br>Sun: 8 AM - 12 PM</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7 position-relative" style="min-height: 250px;">
                                <!-- Styled Map Placeholder -->
                                <div style="position: absolute; top:0; left:0; width:100%; height:100%; background: url('https://placehold.co/600x400/1e293b/f59e0b?text=Interactive+Map+Area') center/cover; opacity: 0.8; border-left: 1px solid rgba(255,255,255,0.1);"></div>
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 20px; height: 20px; background: #f59e0b; border-radius: 50%; box-shadow: 0 0 0 10px rgba(245, 158, 11, 0.3); animation: pulse 2s infinite;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div></div>
    <?php include('../includes/footer.php'); ?>
    <?php include('../includes/scripts.php'); ?>
</body>
</html>
