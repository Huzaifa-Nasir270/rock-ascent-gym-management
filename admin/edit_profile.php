<?php
/**
 * Admin - Edit Profile
 */
require_once('../config/functions.php');
requireAdmin();

$adminId = $_SESSION['admin_id'];

// Get admin details
$stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');

    // Handle Profile Image
    $uploadDir = '../assets/images/profiles/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $profileImage = $admin['profile_image'];

    if (!empty($_POST['camera_image'])) {
        $imgData = $_POST['camera_image'];
        $imgData = str_replace('data:image/png;base64,', '', $imgData);
        $imgData = str_replace(' ', '+', $imgData);
        $data = base64_decode($imgData);
        $fileName = 'admin_' . $adminId . '_' . time() . '.png';
        file_put_contents($uploadDir . $fileName, $data);
        $profileImage = $fileName;
    } elseif (!empty($_FILES['profile_image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $fileName = 'admin_' . $adminId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $fileName)) {
                $profileImage = $fileName;
            }
        }
    }

    $query = "UPDATE admin SET name = ?, phone = ?, profile_image = ? WHERE admin_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $name, $phone, $profileImage, $adminId);

    if ($stmt->execute()) {
        $_SESSION['admin_name'] = $name;
        $_SESSION['profile_image'] = $profileImage; // Optional: update session if stored
        redirect('edit_profile.php', 'Profile updated successfully', 'success');
    } else {
        redirect('edit_profile.php', 'Failed to update profile', 'danger');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Profile - Admin</title>
    <?php include('../includes/head.php'); ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid px-4">
        <div class="dashboard-container">
            <?php include('../includes/admin_sidebar.php'); ?>

            <div class="main-content">
                <div class="welcome-section mb-4 fade-in">
                    <h2 class="mb-0">👨‍💼 Edit Profile</h2>
                </div>

                <?php displayMessage(); ?>

                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header"><h5 class="mb-0">Profile Information</h5></div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="text-center mb-4">
                                        <?php 
                                        $imgSrc = !empty($admin['profile_image']) ? '../assets/images/profiles/' . htmlspecialchars($admin['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($admin['name']) . '&background=f59e0b&color=fff';
                                        ?>
                                        <img src="<?php echo $imgSrc; ?>" id="profilePreview" class="rounded-circle mb-3 border border-warning" style="width: 150px; height: 150px; object-fit: cover;">
                                        
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-outline-warning btn-sm mx-1" onclick="document.getElementById('profileFile').click()"><i class="fas fa-upload mr-1"></i> Upload Image</button>
                                            <button type="button" class="btn btn-outline-info btn-sm mx-1" onclick="openCameraModal()"><i class="fas fa-camera mr-1"></i> Use Camera</button>
                                        </div>
                                        <input type="file" name="profile_image" id="profileFile" style="display:none;" accept="image/*" onchange="previewFile(this)">
                                        <input type="hidden" name="camera_image" id="cameraImage">
                                    </div>

                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email (Read-only)</label>
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($admin['email']); ?>" disabled>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>">
                                    </div>
                                    
                                    <div class="text-right mt-4">
                                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

    <?php include('../includes/footer.php'); ?>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
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
