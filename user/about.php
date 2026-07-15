<?php
require_once('../config/functions.php');
requireUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>About Us - Project Rock Ascent</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>
    <div class="container-fluid px-4"><div class="dashboard-container">
        <!-- Unified Sidebar -->
        <?php include('../includes/user_sidebar.php'); ?>

        <div class="main-content">
            <!-- Premium Page Header -->
            <div class="welcome-section mb-5 fade-in-up">
                <h1 class="mb-2">ℹ️ About <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Our Gym</span></h1>
                <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Learn about our mission, values, and the community we've built.</p>
            </div>
            
            <!-- Stats Section -->
            <div class="row mb-5 fade-in-up" style="animation-delay: 0.1s;">
                <div class="col-md-3 col-6 mb-3">
                    <div class="card text-center py-4" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px;">
                        <h2 class="font-weight-bold mb-0" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">500+</h2>
                        <p class="text-muted small text-uppercase letter-spacing-1 mb-0">Active Members</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card text-center py-4" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px;">
                        <h2 class="font-weight-bold mb-0" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">15+</h2>
                        <p class="text-muted small text-uppercase letter-spacing-1 mb-0">Expert Trainers</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card text-center py-4" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px;">
                        <h2 class="font-weight-bold mb-0" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">50+</h2>
                        <p class="text-muted small text-uppercase letter-spacing-1 mb-0">Premium Machines</p>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="card text-center py-4" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px;">
                        <h2 class="font-weight-bold mb-0" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">24/7</h2>
                        <p class="text-muted small text-uppercase letter-spacing-1 mb-0">Facility Access</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4 fade-in-up" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; animation-delay: 0.2s;">
                        <div class="card-body p-5">
                            <h3 class="mb-4 font-weight-bold">Our <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Story & Vision</span></h3>
                            <p class="lead text-light" style="opacity: 0.9; line-height: 1.8;">
                                Welcome to <strong>Project Rock Ascent</strong>. We started with a simple belief: that greatness is not just a destination, but a continuous, unrelenting journey of self-improvement. Our facility was designed from the ground up to provide an environment that breeds success, discipline, and transformation.
                            </p>
                            <p class="text-muted mb-4" style="line-height: 1.8;">
                                We are more than just a place to work out; we are a community of dedicated individuals pushing their limits every single day. From world-class equipment to personalized smart training systems, we integrate modern technology with raw iron to deliver an unparalleled fitness experience.
                            </p>
                            
                            <hr style="border-color: rgba(255,255,255,0.1); margin: 40px 0;">
                            
                            <h4 class="mb-4 font-weight-bold">Core <span class="text-warning">Values</span></h4>
                            <div class="row mt-4">
                                <div class="col-md-6 mb-4">
                                    <div class="d-flex align-items-start p-3" style="background: rgba(255,255,255,0.02); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="p-3 mr-3 shadow-lg" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(245, 158, 11, 0.05)); border-radius: 12px; color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2);">
                                            <i class="fas fa-dumbbell fa-2x"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-weight-bold text-white">Elite Equipment</h6>
                                            <p class="small text-muted mb-0">State-of-the-art machines and premium free weights imported globally.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="d-flex align-items-start p-3" style="background: rgba(255,255,255,0.02); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="p-3 mr-3 shadow-lg" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(139, 92, 246, 0.05)); border-radius: 12px; color: #8b5cf6; border: 1px solid rgba(139, 92, 246, 0.2);">
                                            <i class="fas fa-brain fa-2x"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-weight-bold text-white">Smart Integration</h6>
                                            <p class="small text-muted mb-0">AI-driven workout & diet recommendations tailored to your goals.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="d-flex align-items-start p-3" style="background: rgba(255,255,255,0.02); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="p-3 mr-3 shadow-lg" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.2), rgba(34, 197, 94, 0.05)); border-radius: 12px; color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2);">
                                            <i class="fas fa-heartbeat fa-2x"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-weight-bold text-white">Holistic Health</h6>
                                            <p class="small text-muted mb-0">Complete wellness tracking including body metrics and attendance.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="d-flex align-items-start p-3" style="background: rgba(255,255,255,0.02); border-radius: 16px; border: 1px solid rgba(255,255,255,0.05); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                                        <div class="p-3 mr-3 shadow-lg" style="background: linear-gradient(135deg, rgba(236, 72, 153, 0.2), rgba(236, 72, 153, 0.05)); border-radius: 12px; color: #ec4899; border: 1px solid rgba(236, 72, 153, 0.2);">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                        <div>
                                            <h6 class="font-weight-bold text-white">Iron Community</h6>
                                            <p class="small text-muted mb-0">A supportive, high-energy environment that keeps you accountable.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Join Us Premium Card -->
                    <div class="card mb-4 fade-in-up" style="background: var(--primary-gradient); border: none; border-radius: 24px; color: white; box-shadow: 0 20px 40px rgba(245, 158, 11, 0.2); animation-delay: 0.3s; position: relative; overflow: hidden;">
                        <!-- Abstract Background Shapes -->
                        <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%; blur(20px);"></div>
                        <div style="position: absolute; bottom: -30px; left: -30px; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%; blur(10px);"></div>
                        
                        <div class="card-body p-5 text-center position-relative z-index-1">
                            <i class="fas fa-gem fa-4x mb-4 text-white" style="filter: drop-shadow(0 0 10px rgba(255,255,255,0.5));"></i>
                            <h3 class="font-weight-bold mb-3">Begin Your Ascent</h3>
                            <p style="opacity: 0.9; font-size: 1.1rem; margin-bottom: 30px;">Take your first step towards a stronger, healthier version of yourself today.</p>
                            <a href="packages.php" class="btn btn-light btn-lg w-100" style="border-radius: 16px; color: #d97706; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; box-shadow: 0 10px 20px rgba(0,0,0,0.2); transition: transform 0.3s;">Explore Memberships</a>
                        </div>
                    </div>
                    
                    <!-- Instructor Spotlight -->
                    <div class="card fade-in-up" style="background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; animation-delay: 0.4s;">
                        <div class="card-body p-4 text-center">
                            <h5 class="font-weight-bold mb-4 text-white border-bottom border-secondary pb-3">Expert Guidance</h5>
                            <i class="fas fa-user-ninja fa-3x mb-3" style="color: #8b5cf6;"></i>
                            <p class="text-muted small mb-3">Our certified instructors are ready to guide you through personalized workout and diet plans via our live 1-on-1 chat system.</p>
                            <a href="chat.php" class="btn btn-outline-info btn-sm rounded-pill px-4">Chat with Instructor</a>
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
