<?php
header('Content-Type: application/json');
require_once('../config/functions.php');
requireUser();

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['reply' => 'Please ask a question.']);
    exit;
}

/**
 * ENHANCED AI CONTEXT ENGINE
 * We fetch the user's real-time stats and shop data to make the AI 100% accurate.
 */
$userId = $_SESSION['user_id'];

// 1. Fetch User Stats
$stmt = $conn->prepare("SELECT fitness_goal, bmi, weight, height FROM user_fitness_stats WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

$userContext = "User Stats: ";
if ($stats) {
    $userContext .= "Goal: {$stats['fitness_goal']}, BMI: {$stats['bmi']}, Weight: {$stats['weight']}kg, Height: {$stats['height']}cm.";
} else {
    $userContext .= "No specific stats provided yet.";
}

// 2. Fetch Shop Products Summary (so AI can recommend real items)
$shopItems = $conn->query("SELECT name, description, price FROM shop_products WHERE status = 'Active' LIMIT 15")->fetch_all(MYSQLI_ASSOC);
$shopContext = "Available Shop Products: ";
foreach ($shopItems as $item) {
    $shopContext .= "{$item['name']} (PKR {$item['price']}), ";
}

/**
 * AI INTEGRATION (Gemini API)
 */
$geminiApiKey = 'AIzaSyAtnTobcXh0ywzsGZYoaNtMSX9FE7v75OY'; 

if (empty($geminiApiKey)) {
    // Fallback Mock System if no API key is provided
    $lowerMsg = strtolower($userMessage);
    if (strpos($lowerMsg, 'muscle') !== false) {
        $reply = "Since your goal is **{$stats['fitness_goal']}**, you should focus on a 200-300 calorie surplus and aim for 1.6g of protein per kg of body weight.";
    } else {
        $reply = "That's a great question! However, my AI is currently offline. Please ensure the API key is active.";
    }
    echo json_encode(['reply' => $reply]);
    exit;
}

// Prepare Gemini API request with High-Detail Instructions
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $geminiApiKey;

$systemInstruction = "You are RockBot, an elite Health & Fitness Specialist for 'Project Rock Ascent Gym'. 
CRITICAL DATA FOR THIS USER:
$userContext
$shopContext

YOUR MISSION:
1. Provide extremely detailed, scientifically-backed explanations for every piece of advice.
2. Be efficient but comprehensive—do not give one-sentence answers.
3. If a user asks for product recommendations, use the 'Available Shop Products' listed above to suggest REAL items we sell.
4. Always relate your advice back to the user's specific BMI and Fitness Goal.
5. Use a motivating, professional tone. Use bullet points for readability when explaining complex topics.";

$data = [
    'contents' => [
        [
            'role' => 'user',
            'parts' => [
                ['text' => $systemInstruction . "\n\nUser Question: " . $userMessage]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.8,
        'maxOutputTokens' => 800, // Increased for detailed explanations
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
unset($ch);

if ($httpCode == 200 && $response) {
    $responseData = json_decode($response, true);
    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $reply = $responseData['candidates'][0]['content']['parts'][0]['text'];
        
        // Advanced formatting for a premium look
        $reply = preg_replace('/\*\*(.*?)\*\*/', '<strong class="text-warning">$1</strong>', $reply);
        $reply = preg_replace('/^- (.*)/m', '<li class="mb-2"><i class="fas fa-check-circle text-success mr-2"></i> $1</li>', $reply);
        $reply = str_replace("\n\n", "</p><p>", $reply);
        $reply = "<p>" . $reply . "</p>";
        
        echo json_encode(['reply' => $reply]);
    } else {
        echo json_encode(['reply' => 'Brain processing error. Please try again.']);
    }
} else {
    echo json_encode(['reply' => 'Connection issue with the AI core. Error: ' . $httpCode]);
}
