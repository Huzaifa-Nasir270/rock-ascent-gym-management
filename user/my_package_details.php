<?php
/**
 * User - My Package Details
 * Show active package, instructor profile, and video library.
 */

require_once('../config/functions.php');
requireUser();

$userId = $_SESSION['user_id'];
$currentSub = getUserSubscription($userId);

if (!$currentSub) {
    redirect('packages.php', 'You do not have an active package subscription.', 'warning');
}

$instructorId = $currentSub['instructor_id'];
$packageId = $currentSub['package_id'];

// Get instructor details
$instructor = getInstructorDetails($instructorId);

// Get videos for this package and instructor
$videosResult = $conn->query("
    SELECT * FROM package_videos 
    WHERE package_id = $packageId 
    ORDER BY created_at DESC
");
$videos = [];
if ($videosResult) {
    while ($row = $videosResult->fetch_assoc()) {
        $videos[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Package - Member</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/user_sidebar.php'); ?>

            <div class="main-content">
                <div class="welcome-section mb-5 fade-in-up">
                    <h1 class="mb-2">🏆 Active <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Package Details</span></h1>
                    <p class="text-muted">Manage your subscription and access exclusive training content.</p>
                </div>

                <?php displayMessage(); ?>

                <div class="row">
                    <!-- Package Info Card -->
                    <div class="col-lg-5 mb-4">
                        <div class="glass-card h-100" style="padding: 0; overflow: hidden;">
                            <div class="p-5 text-center" style="background: linear-gradient(135deg, #f59e0b 0%, #ec4899 100%);">
                                <i class="fas fa-gem fa-3x text-white mb-3"></i>
                                <h2 class="mb-0 text-white font-weight-bold" style="letter-spacing: -1px;"><?php echo htmlspecialchars($currentSub['package_name']); ?></h2>
                                <span class="badge badge-light mt-3 px-4 py-2" style="border-radius: 50px; color: #ec4899; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 2px;">Active Subscription</span>
                            </div>
                            <div class="p-5">
                                <div class="subscription-metric d-flex justify-content-between align-items-center mb-4">
                                    <div class="metric-label">
                                        <i class="fas fa-calendar-alt text-warning mr-2"></i>
                                        <span class="text-muted font-weight-bold">START DATE</span>
                                    </div>
                                    <div class="metric-value font-weight-bold text-white"><?php echo formatDate($currentSub['start_date']); ?></div>
                                </div>
                                <div class="subscription-metric d-flex justify-content-between align-items-center mb-4">
                                    <div class="metric-label">
                                        <i class="fas fa-clock text-warning mr-2"></i>
                                        <span class="text-muted font-weight-bold">EXPIRY DATE</span>
                                    </div>
                                    <div class="metric-value font-weight-bold text-white"><?php echo formatDate($currentSub['end_date']); ?></div>
                                </div>
                                <div class="subscription-metric d-flex justify-content-between align-items-center mb-5">
                                    <div class="metric-label">
                                        <i class="fas fa-tag text-success mr-2"></i>
                                        <span class="text-muted font-weight-bold">PRICE PAID</span>
                                    </div>
                                    <div class="metric-value font-weight-bold text-success" style="font-size: 1.5rem;"><?php echo formatCurrency($currentSub['package_price'] ?? 0); ?></div>
                                </div>
                                
                                <div class="package-description p-4" style="background: rgba(255,255,255,0.03); border-radius: 20px; border: 1px solid rgba(255,255,255,0.05);">
                                    <h6 class="text-white font-weight-bold mb-2">Package Details</h6>
                                    <p class="text-muted small mb-0" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($currentSub['package_description'] ?? 'No description provided.')); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructor Profile Card -->
                    <div class="col-lg-7 mb-4">
                        <div class="glass-card h-100 p-5">
                            <div class="row align-items-center">
                                <div class="col-md-5 text-center mb-4 mb-md-0">
                                    <div class="profile-avatar-wrapper mb-4" style="position: relative; display: inline-block;">
                                        <img src="<?php echo !empty($instructor['profile_image']) ? "../assets/images/profiles/" . htmlspecialchars($instructor['profile_image']) : "https://ui-avatars.com/api/?name=" . urlencode($instructor['name']) . "&background=f59e0b&color=fff"; ?>" 
                                             class="rounded-circle border-4 border-warning" style="width: 180px; height: 180px; object-fit: cover; box-shadow: 0 20px 40px rgba(0,0,0,0.4); border: 4px solid #f59e0b !important;">
                                        <div class="status-indicator active" style="position: absolute; bottom: 15px; right: 15px; width: 25px; height: 25px; background: #22c55e; border: 4px solid #0f172a; border-radius: 50%;"></div>
                                    </div>
                                    <h3 class="mb-1 text-white font-weight-bold"><?php echo htmlspecialchars($instructor['name']); ?></h3>
                                    <p class="text-warning font-weight-bold small text-uppercase mb-0" style="letter-spacing: 2px;"><?php echo htmlspecialchars($instructor['specialization']); ?></p>
                                </div>
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="icon-box mr-3" style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                                            <i class="fas fa-id-card"></i>
                                        </div>
                                        <h5 class="font-weight-bold text-white mb-0">Instructor Portfolio</h5>
                                    </div>
                                    
                                    <p class="text-muted mb-4" style="line-height: 1.8; font-size: 0.95rem;"><?php echo !empty($instructor['bio']) ? nl2br(htmlspecialchars($instructor['bio'])) : "This instructor has not shared a professional bio yet. They will guide you through your fitness journey with expertise."; ?></p>
                                    
                                    <div class="row mb-4">
                                        <div class="col-6">
                                            <div class="info-block">
                                                <label class="text-muted small font-weight-bold text-uppercase mb-1" style="display: block; letter-spacing: 1px;">Experience</label>
                                                <span class="text-white"><?php echo htmlspecialchars($instructor['experience'] ?? '5+ Years'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="info-block">
                                                <label class="text-muted small font-weight-bold text-uppercase mb-1" style="display: block; letter-spacing: 1px;">Certification</label>
                                                <span class="text-white"><?php echo htmlspecialchars($instructor['certification'] ?? 'Professional'); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="text-white font-weight-bold mb-3">Core Expertise</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php 
                                        if (!empty($instructor['skills'])) {
                                            $skills = explode(',', $instructor['skills']);
                                            foreach ($skills as $skill) {
                                                echo '<span class="skill-tag">' . trim($skill) . '</span>';
                                            }
                                        } else {
                                            echo '<span class="skill-tag">Fitness Training</span><span class="skill-tag">Nutrition</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Video Gallery Section -->
                <div class="mt-5 fade-in-up">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <h2 class="mb-0 font-weight-bold">🎥 Training <span class="text-primary">Content Library</span></h2>
                            <p class="text-muted mb-0">Exclusive video guidance for your package.</p>
                        </div>
                    </div>

                    <?php if (empty($videos)): ?>
                        <div class="card text-center p-5" style="border-radius: 30px; background: rgba(255,255,255,0.02);">
                            <i class="fas fa-video-slash fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No videos available for this package yet.</h4>
                            <p class="text-muted mb-0">Your instructor will add training content soon. Stay tuned!</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($videos as $video): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 video-card" style="border-radius: 20px; overflow: hidden; background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s;">
                                        <div class="video-thumbnail-wrapper" style="position: relative; height: 180px; background: #000;">
                                            <?php 
                                            // Simple logic to get youtube thumbnail if it's a youtube link
                                            $thumb = 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=800&q=80';
                                            $videoUrl = $video['video_url'];
                                            if (strpos($videoUrl, 'youtube.com') !== false || strpos($videoUrl, 'youtu.be') !== false) {
                                                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $videoUrl, $match);
                                                if (isset($match[1])) {
                                                    $thumb = "https://img.youtube.com/vi/{$match[1]}/mqdefault.jpg";
                                                }
                                            }
                                            ?>
                                            <img src="<?php echo $thumb; ?>" class="card-img-top" style="height: 100%; object-fit: cover; opacity: 0.7;">
                                            <a href="javascript:void(0)" onclick="openVideoPlayer('<?php echo addslashes($videoUrl); ?>', '<?php echo addslashes($video['title']); ?>')" class="play-btn">
                                                <i class="fas fa-play"></i>
                                            </a>
                                            <span class="badge badge-primary" style="position: absolute; top: 15px; right: 15px; border-radius: 8px;"><?php echo htmlspecialchars($video['category']); ?></span>
                                        </div>
                                        <div class="card-body p-4">
                                            <h5 class="card-title font-weight-bold mb-2 text-white"><?php echo htmlspecialchars($video['title']); ?></h5>
                                            <p class="card-text text-muted small mb-0"><?php echo nl2br(htmlspecialchars($video['description'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Player Modal -->
    <div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="background: #0f172a; border-radius: 24px; border: 1px solid rgba(255,255,255,0.1); overflow: hidden;">
                <div class="modal-header border-0 bg-dark p-3">
                    <h5 class="modal-title text-white font-weight-bold" id="videoModalLabel">Video Training</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" onclick="closeVideoPlayer()">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="embed-responsive embed-responsive-16by9" id="videoContainer">
                        <!-- Video will be injected here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .glass-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border-radius: 40px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }
        .subscription-metric {
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .subscription-metric:last-of-type {
            border-bottom: none;
        }
        .metric-label span {
            font-size: 11px;
            letter-spacing: 1px;
        }
        .skill-tag {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            padding: 6px 16px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            margin-right: 10px;
            margin-bottom: 10px;
            border: 1px solid rgba(245, 158, 11, 0.2);
            display: inline-block;
        }
        .video-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            border-color: rgba(245, 158, 11, 0.3);
        }
        .play-btn {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background: #f59e0b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.5);
        }
        .play-btn:hover {
            transform: translate(-50%, -50%) scale(1.1);
            background: #fff;
            color: #f59e0b;
        }
    </style>

    <?php include('../includes/footer.php'); ?>
    <?php include('../includes/scripts.php'); ?>
    <script>
        function openVideoPlayer(url, title) {
            let embedHtml = '';
            url = url.trim();
            console.log("Opening Video URL:", url);

            // Even more robust YouTube ID extraction
            function getYoutubeId(url) {
                const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|shorts\/)([^#\&\?]*).*/;
                const match = url.match(regExp);
                return (match && match[2].length === 11) ? match[2] : null;
            }

            const youtubeId = getYoutubeId(url);

            // Vimeo ID extraction
            const vimeoRegex = /(?:vimeo\.com\/|player\.vimeo\.com\/video\/)([0-9]+)/;
            const vimeoMatch = url.match(vimeoRegex);
            const vimeoId = (vimeoMatch) ? vimeoMatch[1] : null;

            if (youtubeId) {
                embedHtml = `<iframe class="embed-responsive-item" src="https://www.youtube.com/embed/${youtubeId}?autoplay=1&rel=0" allowfullscreen allow="autoplay; encrypted-media"></iframe>`;
            } else if (vimeoId) {
                embedHtml = `<iframe class="embed-responsive-item" src="https://player.vimeo.com/video/${vimeoId}?autoplay=1" allowfullscreen allow="autoplay; encrypted-media"></iframe>`;
            } else {
                // Check if it's likely a direct video file
                if (url.match(/\.(mp4|webm|ogg)$/i) || url.toLowerCase().includes('video')) {
                    embedHtml = `<video class="embed-responsive-item" controls autoplay><source src="${url}" type="video/mp4">Your browser does not support the video tag.</video>`;
                } else {
                    // Fallback for unknown links
                    embedHtml = `
                        <div class="p-5 text-center d-flex flex-column align-items-center justify-content-center" style="height: 100%;">
                            <i class="fas fa-external-link-alt fa-3x mb-4 text-muted"></i>
                            <p class="text-white mb-2">This video source may require direct viewing due to security policies.</p>
                            <p class="text-muted small mb-4">Source URL: <span class="text-info">${url}</span></p>
                            <a href="${url}" target="_blank" class="btn btn-primary px-5 py-3" style="border-radius: 50px; background: var(--primary-gradient); border: none; font-weight: bold; box-shadow: 0 10px 20px rgba(236, 72, 153, 0.3);">
                                WATCH ON ORIGINAL SITE
                            </a>
                        </div>`;
                }
            }

            $('#videoModalLabel').text(title);
            $('#videoContainer').html(embedHtml);
            $('#videoModal').modal('show');
        }

        function closeVideoPlayer() {
            $('#videoContainer').html('');
        }

        // Also stop video when modal is closed via clicking outside or Esc
        $('#videoModal').on('hidden.bs.modal', function () {
            closeVideoPlayer();
        });
    </script>
</body>
</html>
