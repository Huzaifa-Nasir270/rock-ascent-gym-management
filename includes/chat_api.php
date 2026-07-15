<?php
require_once('../config/functions.php');
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'] ?? 0;
$instructorId = $_SESSION['instructor_id'] ?? 0;

if ($userId) {
    $myId = $userId;
    $role = 'user';
} elseif ($instructorId) {
    $myId = $instructorId;
    $role = 'instructor';
} else {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Map userId to the local variable used in the script
$currentId = $myId;

switch ($action) {
    case 'get_conversation':
        $targetId = (int)$_GET['target_id'];
        // Check if conversation exists, if not create it
        if ($role === 'user') {
            $uId = $currentId; $iId = $targetId;
        } else {
            $uId = $targetId; $iId = $currentId;
        }

        $stmt = $conn->prepare("SELECT conversation_id FROM conversations WHERE user_id = ? AND instructor_id = ?");
        $stmt->bind_param("ii", $uId, $iId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if ($res) {
            $convId = $res['conversation_id'];
        } else {
            $stmt = $conn->prepare("INSERT INTO conversations (user_id, instructor_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $uId, $iId);
            $stmt->execute();
            $convId = $stmt->insert_id;
        }

        // Fetch History
        $stmt = $conn->prepare("SELECT * FROM chat_messages WHERE conversation_id = ? ORDER BY created_at ASC LIMIT 50");
        $stmt->bind_param("i", $convId);
        $stmt->execute();
        $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['conversation_id' => $convId, 'history' => $messages]);
        break;

    case 'save_message':
        $data = json_decode(file_get_contents('php://input'), true);
        $convId = isset($data['conversation_id']) ? (int)$data['conversation_id'] : 0;
        $targetId = isset($data['target_id']) ? (int)$data['target_id'] : 0;
        $msg = sanitize($data['message'] ?? '');

        // Fallback: If conversation_id is missing, find or create it
        if ($convId === 0 && $targetId > 0) {
            if ($role === 'user') { $uId = $currentId; $iId = $targetId; }
            else { $uId = $targetId; $iId = $currentId; }
            
            $stmt = $conn->prepare("SELECT conversation_id FROM conversations WHERE user_id = ? AND instructor_id = ?");
            $stmt->bind_param("ii", $uId, $iId);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            
            if ($res) {
                $convId = $res['conversation_id'];
            } else {
                $stmt = $conn->prepare("INSERT INTO conversations (user_id, instructor_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $uId, $iId);
                $stmt->execute();
                $convId = $stmt->insert_id;
            }
        }

        if ($convId > 0 && !empty($msg)) {
            $stmt = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_id, sender_role, message) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $convId, $currentId, $role, $msg);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message_id' => $stmt->insert_id, 'conversation_id' => $convId]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid data: ' . json_encode($data)]);
        }
        break;
}
