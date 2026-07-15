<?php
/**
 * Instructor - Manage Videos
 * Instructors can add training/guidance videos to packages they are assigned to.
 */

require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];

// Fetch packages this instructor is associated with (either through templates or direct assignments)
// For simplicity, we'll allow them to add videos to ANY active package, but label them as their videos.
$packages = $conn->query("SELECT * FROM packages WHERE status = 'Active'")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_video') {
        $packageId = (int)$_POST['package_id'];
        $title = sanitize($_POST['title']);
        $description = sanitize($_POST['description']);
        $videoUrl = sanitize($_POST['video_url']);
        $category = sanitize($_POST['category']);

        $stmt = $conn->prepare("INSERT INTO package_videos (package_id, instructor_id, title, description, video_url, category) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $packageId, $instructorId, $title, $description, $videoUrl, $category);
        
        if ($stmt->execute()) {
            redirect('manage_videos.php', 'Video added successfully!', 'success');
        } else {
            redirect('manage_videos.php', 'Failed to add video.', 'danger');
        }
    }

    if ($action === 'delete_video') {
        $videoId = (int)$_POST['video_id'];
        $stmt = $conn->prepare("DELETE FROM package_videos WHERE video_id = ? AND instructor_id = ?");
        $stmt->bind_param("ii", $videoId, $instructorId);
        $stmt->execute();
        redirect('manage_videos.php', 'Video deleted.', 'success');
    }
}

// Get videos by this instructor
$videos = $conn->query("
    SELECT v.*, p.name as package_name 
    FROM package_videos v 
    JOIN packages p ON v.package_id = p.package_id 
    WHERE v.instructor_id = $instructorId 
    ORDER BY v.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Videos - Instructor</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/instructor_sidebar.php'); ?>

            <div class="main-content">
                <div class="welcome-section mb-5 fade-in-up">
                    <h1 class="mb-2">🎥 Manage <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Training Videos</span></h1>
                    <p class="text-muted">Upload guidance and workout videos for your members.</p>
                </div>

                <?php displayMessage(); ?>

                <div class="card mb-5">
                    <div class="card-header">
                        <h5 class="mb-0">Add New Video</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_video">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Associated Package</label>
                                        <select name="package_id" class="form-control" required>
                                            <option value="">-- Select Package --</option>
                                            <?php foreach ($packages as $p): ?>
                                                <option value="<?php echo $p['package_id']; ?>"><?php echo htmlspecialchars($p['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Video Title</label>
                                        <input type="text" name="title" class="form-control" required placeholder="e.g. Morning Yoga Flow">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Video URL (YouTube/Vimeo)</label>
                                        <input type="url" name="video_url" class="form-control" required placeholder="https://www.youtube.com/watch?v=...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select name="category" class="form-control" required>
                                            <option value="Workout">Workout</option>
                                            <option value="Nutrition">Nutrition</option>
                                            <option value="Guidance">Guidance</option>
                                            <option value="Motivation">Motivation</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Video</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Your Video Library</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Package</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($videos)): ?>
                                        <tr><td colspan="5" class="text-center text-muted">No videos uploaded yet.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($videos as $v): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($v['title']); ?></td>
                                                <td><span class="badge badge-info"><?php echo htmlspecialchars($v['package_name']); ?></span></td>
                                                <td><span class="badge badge-secondary"><?php echo htmlspecialchars($v['category']); ?></span></td>
                                                <td><?php echo formatDate($v['created_at']); ?></td>
                                                <td>
                                                    <a href="<?php echo htmlspecialchars($v['video_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-play mr-1"></i> View</a>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this video?')">
                                                        <input type="hidden" name="action" value="delete_video">
                                                        <input type="hidden" name="video_id" value="<?php echo $v['video_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
