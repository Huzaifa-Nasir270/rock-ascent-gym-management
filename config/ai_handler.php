<?php
header('Content-Type: application/json');
require_once('functions.php');

// Allow User, Admin, or Instructor
if (!isLoggedIn()) {
    echo json_encode(['reply' => 'Please log in to use the AI Assistant.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'How can I help you today?']);
    exit;
}

$lowerMsg = strtolower($userMessage);
$reply = "";

// Role-based logic
$role = $_SESSION['role'] ?? 'user';

if ($role === 'admin') {
    // Admin specific responses
    if (strpos($lowerMsg, 'user') !== false || strpos($lowerMsg, 'member') !== false) {
        $reply = "As an Admin, you can manage members in the 'Manage Users' section. You can activate, deactivate, or convert them to instructors.";
    } elseif (strpos($lowerMsg, 'report') !== false || strpos($lowerMsg, 'stat') !== false) {
        $reply = "You can view detailed system reports and analytics in the 'Reports' section. It includes membership trends and payment summaries.";
    }
} elseif ($role === 'instructor') {
    // Instructor specific responses
    if (strpos($lowerMsg, 'plan') !== false || strpos($lowerMsg, 'workout') !== false) {
        $reply = "Instructors can create and assign personalized workout and diet plans to their members through the 'Workout Plans' and 'Diet Plans' modules.";
    } elseif (strpos($lowerMsg, 'attendance') !== false) {
        $reply = "You can mark and track member attendance in the 'Attendance' section to ensure they stay on track with their goals.";
    }
}

if (empty($reply)) {
    // Standard Fitness Knowledge Base (Shared)
    $responses = [
        'muscle' => "To build muscle, focus on compound movements and a protein intake of 1.6-2.2g per kg of body weight.",
        'weight' => "Weight loss requires a consistent calorie deficit and high protein to preserve muscle.",
        'protein' => "Excellent protein sources include chicken, eggs, lentils, whey, and Greek yogurt.",
        'water' => "Aim for 3-4 liters of water daily. Hydration is key for recovery and metabolism.",
        'sleep' => "7-9 hours of quality sleep is essential for muscle repair and hormonal balance.",
    ];

    if (strpos($lowerMsg, 'muscle') !== false || strpos($lowerMsg, 'gain') !== false) {
        $reply = $responses['muscle'];
    } elseif (strpos($lowerMsg, 'weight') !== false || strpos($lowerMsg, 'fat') !== false) {
        $reply = $responses['weight'];
    } elseif (strpos($lowerMsg, 'protein') !== false) {
        $reply = $responses['protein'];
    } elseif (strpos($lowerMsg, 'water') !== false || strpos($lowerMsg, 'drink') !== false) {
        $reply = $responses['water'];
    } elseif (strpos($lowerMsg, 'sleep') !== false || strpos($lowerMsg, 'rest') !== false) {
        $reply = $responses['sleep'];
    } elseif (strpos($lowerMsg, 'hi') !== false || strpos($lowerMsg, 'hello') !== false) {
        $reply = "Hello! I'm RockBot. How can I assist with your fitness goals today?";
    } else {
        $reply = "That's interesting! I recommend focusing on consistency and checking your personalized plans in the dashboard for more specific guidance.";
    }
}

// Simulate processing
usleep(600000);

echo json_encode(['reply' => $reply]);
