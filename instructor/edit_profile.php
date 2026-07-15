<?php
/**
 * Instructor - Edit Profile
 */

require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];

// Fetch instructor details
$instructor = getInstructorDetails($instructorId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $specialization = sanitize($_POST['specialization'] ?? '');
    $skills = sanitize($_POST['skills'] ?? '');
    $experience = sanitize($_POST['experience'] ?? '');
    $certification = sanitize($_POST['certification'] ?? '');
    $bio = sanitize($_POST['bio'] ?? '');

    // Handle Profile Image
    $uploadDir = '../assets/images/profiles/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $profileImage = $instructor['profile_image'];

    if (!empty($_POST['camera_image'])) {
        $imgData = $_POST['camera_image'];
        $imgData = str_replace('data:image/png;base64,', '', $imgData);
        $imgData = str_replace(' ', '+', $imgData);
        $data = base64_decode($imgData);
        $fileName = 'instructor_' . $instructorId . '_' . time() . '.png';
        file_put_contents($uploadDir . $fileName, $data);
        $profileImage = $fileName;
    } elseif (!empty($_FILES['profile_image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $fileName = 'instructor_' . $instructorId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $fileName)) {
                $profileImage = $fileName;
            }
        }
    }

    // Update instructor details
    $query = "UPDATE instructors SET name = ?, email = ?, phone = ?, specialization = ?, skills = ?, experience = ?, certification = ?, bio = ?, profile_image = ? WHERE instructor_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssssssi", $name, $email, $phone, $specialization, $skills, $experience, $certification, $bio, $profileImage, $instructorId);

    if ($stmt->execute()) {
        redirect('dashboard.php', 'Profile updated successfully', 'success');
    } else {
        redirect('edit_profile.php', 'Failed to update profile', 'danger');
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Profile</title>
    <?php include('../includes/head.php'); ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Edit Profile</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <?php 
                                $imgSrc = !empty($instructor['profile_image']) ? '../assets/images/profiles/' . htmlspecialchars($instructor['profile_image']) : 'https://ui-avatars.com/api/?name=' . urlencode($instructor['name']) . '&background=f59e0b&color=fff';
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
                                <label for="name">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($instructor['name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($instructor['email'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($instructor['phone'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="specialization">Specialization</label>
                                <input type="text" class="form-control" id="specialization" name="specialization" value="<?php echo htmlspecialchars($instructor['specialization'] ?? ''); ?>" placeholder="e.g. Bodybuilding, Yoga, CrossFit">
                            </div>
                            <div class="form-group">
                                <label for="skills">Professional Skills</label>
                                <input type="text" class="form-control" id="skills" name="skills" value="<?php echo htmlspecialchars($instructor['skills'] ?? ''); ?>" placeholder="e.g. Nutrition, Weight Loss, Strength Training">
                            </div>
                            <div class="form-group">
                                <label for="experience">Experience</label>
                                <input type="text" class="form-control" id="experience" name="experience" value="<?php echo htmlspecialchars($instructor['experience'] ?? ''); ?>" placeholder="e.g. 5+ years in Personal Training">
                            </div>
                            <div class="form-group">
                                <label for="certification">Certifications</label>
                                <input type="text" class="form-control" id="certification" name="certification" value="<?php echo htmlspecialchars($instructor['certification'] ?? ''); ?>" placeholder="e.g. ACE Certified, NASM">
                            </div>
                            <div class="form-group">
                                <label for="bio">Professional Bio</label>
                                <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="Tell members about your journey and how you can help them..."><?php echo htmlspecialchars($instructor['bio'] ?? ''); ?></textarea>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
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