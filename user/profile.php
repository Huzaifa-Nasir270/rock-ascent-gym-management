<?php
/**
 * User Profile
 */

require_once('../config/functions.php');
require_once('../config/fitness_plans.php');
requireUser();

$userId = $_SESSION['user_id'];
$user = getUserDetails($userId);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $date_of_birth = sanitize($_POST['date_of_birth'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $state = sanitize($_POST['state'] ?? '');
    $zipcode = sanitize($_POST['zipcode'] ?? '');
    $fitness_goal = sanitize($_POST['fitness_goal'] ?? 'Stay Fit');
    $fitness_level = sanitize($_POST['fitness_level'] ?? 'Beginner');
    $height = sanitize($_POST['height'] ?? '');
    $weight = sanitize($_POST['weight'] ?? '');
    $age = sanitize($_POST['age'] ?? '');

    // Handle Profile Image
    $uploadDir = '../assets/images/profiles/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $profileImage = $user['profile_image'];

    if (!empty($_POST['camera_image'])) {
        $imgData = $_POST['camera_image'];
        $imgData = str_replace('data:image/png;base64,', '', $imgData);
        $imgData = str_replace(' ', '+', $imgData);
        $data = base64_decode($imgData);
        $fileName = 'user_' . $userId . '_' . time() . '.png';
        file_put_contents($uploadDir . $fileName, $data);
        $profileImage = $fileName;
    } elseif (!empty($_FILES['profile_image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $fileName = 'user_' . $userId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $fileName)) {
                $profileImage = $fileName;
            }
        }
    }

    $query = "UPDATE users SET name = ?, phone = ?, date_of_birth = ?, address = ?, city = ?, state = ?, zipcode = ?, fitness_goal = ?, fitness_level = ?, height = ?, weight = ?, age = ?, profile_image = ? WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssssddisi", $name, $phone, $date_of_birth, $address, $city, $state, $zipcode, $fitness_goal, $fitness_level, $height, $weight, $age, $profileImage, $userId);

    if ($stmt->execute()) {
        $_SESSION['user_name'] = $name;
        redirect('profile.php', 'Profile updated successfully', 'success');
    } else {
        redirect('profile.php', 'Error updating profile', 'danger');
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile - Member</title>
    <?php include('../includes/head.php'); ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
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
                    <h1 class="mb-2">👤 My <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Profile</span></h1>
                    <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Manage your account settings and personal information.</p>
                </div>

                <?php displayMessage(); ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            
                            <div class="text-center mb-4">
                                <?php 
                                $imgSrc = !empty($user['profile_image']) ? '../assets/images/profiles/' . htmlspecialchars($user['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=f59e0b&color=fff';
                                ?>
                                <img src="<?php echo $imgSrc; ?>" id="profilePreview" class="rounded-circle mb-3 border border-warning" style="width: 150px; height: 150px; object-fit: cover;">
                                
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="button" class="btn btn-outline-warning btn-sm mx-1" onclick="document.getElementById('profileFile').click()"><i class="fas fa-upload mr-1"></i> Upload Image</button>
                                    <button type="button" class="btn btn-outline-info btn-sm mx-1" onclick="openCameraModal()"><i class="fas fa-camera mr-1"></i> Use Camera</button>
                                </div>
                                <input type="file" name="profile_image" id="profileFile" style="display:none;" accept="image/*" onchange="previewFile(this)">
                                <input type="hidden" name="camera_image" id="cameraImage">
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Full Name</label>
                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Email (Read-only)</label>
                                    <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Phone</label>
                                    <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Date of Birth</label>
                                    <input type="date" class="form-control" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Height (cm)</label>
                                    <input type="number" step="0.1" class="form-control" name="height" value="<?php echo htmlspecialchars($user['height'] ?? ''); ?>" placeholder="e.g. 175">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Weight (kg)</label>
                                    <input type="number" step="0.1" class="form-control" name="weight" value="<?php echo htmlspecialchars($user['weight'] ?? ''); ?>" placeholder="e.g. 70">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Age</label>
                                    <input type="number" class="form-control" name="age" value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>" placeholder="e.g. 25">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>City</label>
                                    <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>State</label>
                                    <input type="text" class="form-control" name="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Zip Code</label>
                                    <input type="text" class="form-control" name="zipcode" value="<?php echo htmlspecialchars($user['zipcode'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Fitness Goal</label>
                                    <select name="fitness_goal" class="form-control" style="background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1);">
                                        <option value="Weight Gain" <?php echo ($user['fitness_goal'] == 'Weight Gain') ? 'selected' : ''; ?>>Weight Gain</option>
                                        <option value="Weight Loss" <?php echo ($user['fitness_goal'] == 'Weight Loss') ? 'selected' : ''; ?>>Weight Loss</option>
                                        <option value="Stay Fit" <?php echo ($user['fitness_goal'] == 'Stay Fit') ? 'selected' : ''; ?>>Stay Fit</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Fitness Level</label>
                                    <select name="fitness_level" class="form-control" style="background: rgba(255,255,255,0.05); color: white; border: 1px solid rgba(255,255,255,0.1);">
                                        <option value="Beginner" <?php echo (($user['fitness_level'] ?? 'Beginner') == 'Beginner') ? 'selected' : ''; ?>>Beginner</option>
                                        <option value="Intermediate" <?php echo (($user['fitness_level'] ?? '') == 'Intermediate') ? 'selected' : ''; ?>>Intermediate</option>
                                        <option value="Advanced" <?php echo (($user['fitness_level'] ?? '') == 'Advanced') ? 'selected' : ''; ?>>Advanced</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" style="border-radius: 12px; font-weight: bold; padding: 10px 30px;">Save Changes</button>
                            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Account Details</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Account Status:</strong> <span class="badge badge-success"><?php echo $user['status']; ?></span></p>
                        <p><strong>Gender:</strong> <?php echo htmlspecialchars($user['gender'] ?? 'Not specified'); ?></p>
                        <p><strong>Member Since:</strong> <?php echo formatDate($user['created_at']); ?></p>
                        <p><strong>Last Updated:</strong> <?php echo formatDate($user['updated_at']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <!-- Camera Modal -->
    <div class="modal fade" id="cameraModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Take Profile Picture</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" onclick="stopCamera()">&times;</button>
                </div>
                <div class="modal-body text-center">
                    <video id="cameraVideo" width="100%" autoplay playsinline style="border-radius: 10px; background: #000;"></video>
                    <canvas id="cameraCanvas" style="display:none;"></canvas>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="stopCamera()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="takeSnapshot()"><i class="fas fa-camera mr-1"></i> Capture</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Crop Modal -->
    <div class="modal fade" id="cropModal" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Adjust Profile Picture</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body text-center bg-dark">
                    <div style="max-height: 500px; overflow: hidden;">
                        <img id="cropImage" src="" style="max-width: 100%;">
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="applyCrop()"><i class="fas fa-crop mr-1"></i> Crop & Save</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/scripts.php'); ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

    <script>
        let videoStream = null;
        let cropper = null;

        function previewFile(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#cropImage').attr('src', e.target.result);
                    $('#cropModal').modal('show');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function openCameraModal() {
            $('#cameraModal').modal('show');
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) {
                    videoStream = stream;
                    var video = document.getElementById('cameraVideo');
                    video.srcObject = stream;
                    video.play();
                })
                .catch(function(err) {
                    console.error("Camera error: ", err);
                    alert("Could not access camera. Please ensure permissions are granted.");
                    $('#cameraModal').modal('hide');
                });
        }

        function stopCamera() {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
                videoStream = null;
            }
        }

        function takeSnapshot() {
            var video = document.getElementById('cameraVideo');
            var canvas = document.getElementById('cameraCanvas');
            var context = canvas.getContext('2d');
            
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            var dataUrl = canvas.toDataURL('image/png');
            
            stopCamera();
            $('#cameraModal').modal('hide');
            
            $('#cropImage').attr('src', dataUrl);
            $('#cropModal').modal('show');
        }

        // Cropper Logic
        $('#cropModal').on('shown.bs.modal', function () {
            cropper = new Cropper(document.getElementById('cropImage'), {
                aspectRatio: 1,
                viewMode: 1,
                autoCropArea: 1,
            });
        }).on('hidden.bs.modal', function () {
            if (cropper) {
                cropper.destroy();
                cropper = null;
            }
            $('#profileFile').val(''); // Clear file input if cancelled
        });

        function applyCrop() {
            if (cropper) {
                var canvas = cropper.getCroppedCanvas({
                    width: 400,
                    height: 400
                });
                var dataUrl = canvas.toDataURL('image/png');
                
                $('#profilePreview').attr('src', dataUrl);
                $('#cameraImage').val(dataUrl);
                $('#profileFile').val(''); // Clear file input so backend uses base64
                
                $('#cropModal').modal('hide');
            }
        }
    </script>
</body>
</html>
