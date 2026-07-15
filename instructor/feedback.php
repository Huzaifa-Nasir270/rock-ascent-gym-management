<?php
/**
 * Instructor Feedback Page
 * Allows instructors to submit feedback that is visible to admin
 */

require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];
$instructorName = $_SESSION['instructor_name'];
$instructorEmail = $_SESSION['instructor_email'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating'] ?? 0);
    $subject = sanitize($_POST['subject'] ?? '');
    $feedbackMessage = sanitize($_POST['message'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating (1-5 stars)';
    } elseif (empty($subject)) {
        $error = 'Please enter a subject';
    } elseif (empty($feedbackMessage)) {
        $error = 'Please enter your feedback message';
    } else {
        $query = "INSERT INTO feedback (user_id, user_name, user_email, user_role, rating, subject, message, status) VALUES (?, ?, ?, 'instructor', ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ississ", $instructorId, $instructorName, $instructorEmail, $rating, $subject, $feedbackMessage);
        
        if ($stmt->execute()) {
            $message = 'Thank you for your feedback! We appreciate your input.';
        } else {
            $error = 'Error submitting feedback. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Submit Feedback - Project Rock Ascent</title>
    <?php include('../includes/head.php'); ?>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Instructor Sidebar -->
            <?php include('../includes/instructor_sidebar.php'); ?>

            <div class="main-content">
                <h2 class="mb-4">💬 Submit Feedback</h2>

                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">We Value Your Feedback</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Help us improve Project Rock Ascent by sharing your thoughts, suggestions, or reporting any issues.
                            Your feedback is directly visible to the admin team.
                        </p>

                        <form method="POST" action="feedback.php">
                            <div class="form-group">
                                <label>Rating <span class="text-danger">*</span></label>
                                <div class="rating-stars">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" <?php echo (isset($_POST['rating']) && $_POST['rating'] == $i) ? 'checked' : ''; ?> required>
                                        <label for="star<?php echo $i; ?>" class="star-label">
                                            <i class="fas fa-star"></i>
                                        </label>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted">Click to rate (5 stars = excellent)</small>
                            </div>

                            <div class="form-group">
                                <label for="subject">Subject <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" placeholder="Brief summary of your feedback" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="message">Your Feedback <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="6" placeholder="Share your detailed feedback, suggestions, or report any issues..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane mr-2"></i> Submit Feedback
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Previous Feedback -->
                <?php
                $prevFeedback = $conn->query("SELECT * FROM feedback WHERE user_id = $instructorId ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
                if (!empty($prevFeedback)):
                ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Your Previous Feedback</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($prevFeedback as $fb): ?>
                            <div class="feedback-card">
                                <div class="feedback-header">
                                    <div class="feedback-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo $i <= $fb['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="badge badge-<?php echo $fb['status'] === 'resolved' ? 'success' : ($fb['status'] === 'reviewed' ? 'info' : 'warning'); ?>">
                                        <?php echo ucfirst($fb['status']); ?>
                                    </span>
                                </div>
                                <h6 class="text-light"><?php echo htmlspecialchars($fb['subject']); ?></h6>
                                <p class="text-muted mb-2"><?php echo nl2br(htmlspecialchars($fb['message'])); ?></p>
                                <small class="text-muted">Submitted: <?php echo formatDate($fb['created_at']); ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
    <style>
        .rating-stars {
            display: flex;
            flex-direction: row-reverse;
            gap: 8px;
            margin-bottom: 8px;
        }
        .rating-stars input {
            display: none;
        }
        .star-label {
            font-size: 28px;
            color: #4a4a4a;
            cursor: pointer;
            transition: color 0.2s;
        }
        .rating-stars input:checked ~ label,
        .rating-stars label:hover,
        .rating-stars label:hover ~ label {
            color: #f59e0b;
        }
    </style>
</body>
</html>