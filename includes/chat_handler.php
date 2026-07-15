<?php
/**
 * Professional Chat Handler - Integrated with Socket.IO & Conversations
 */

require_once('../config/functions.php');

$userId = $_SESSION['user_id'] ?? 0;
$instructorId = $_SESSION['instructor_id'] ?? 0;

// Use explicit role from request if provided, otherwise detect
$requestedRole = $_GET['role'] ?? '';
if ($requestedRole === 'instructor' && $instructorId) {
    $myId = $instructorId;
    $role = 'instructor';
} elseif ($requestedRole === 'user' && $userId) {
    $myId = $userId;
    $role = 'user';
} else {
    $myId = $userId ?: $instructorId;
    $role = $userId ? 'user' : 'instructor';
}

if (!$myId) {
    die(json_encode(['error' => 'Unauthorized']));
}

$action = $_GET['action'] ?? '';

if ($action === 'get_partners') {
    if ($role === 'user') {
        // Users see their assigned instructor
        $query = "
            SELECT i.instructor_id as id, i.name, 'instructor' as role 
            FROM instructors i
            INNER JOIN user_instructor_assignments uia ON i.instructor_id = uia.instructor_id
            WHERE uia.user_id = ? AND uia.status = 'Active'
            GROUP BY i.instructor_id
        ";
    } else {
        // Instructors see their assigned members (Exclude themselves by name for extra safety)
        $query = "
            SELECT u.user_id as id, u.name, 'user' as role 
            FROM users u
            INNER JOIN user_instructor_assignments uia ON u.user_id = uia.user_id
            WHERE uia.instructor_id = ? AND uia.status = 'Active' 
            AND u.name != (SELECT name FROM instructors WHERE instructor_id = ?)
            GROUP BY u.user_id
        ";
    }
    
    $stmt = $conn->prepare($query);
    if ($role === 'user') {
        $stmt->bind_param("i", $myId);
    } else {
        $stmt->bind_param("ii", $myId, $myId);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $partners = [];
    while ($row = $result->fetch_assoc()) {
        $pId = $row['id'];
        $pRole = $row['role'];
        
        // Get conversation_id for unread count
        $cQuery = ($role === 'user') 
            ? "SELECT conversation_id FROM conversations WHERE user_id = ? AND instructor_id = ?" 
            : "SELECT conversation_id FROM conversations WHERE user_id = ? AND instructor_id = ?";
        
        $cStmt = $conn->prepare($cQuery);
        if ($role === 'user') $cStmt->bind_param("ii", $myId, $pId);
        else $cStmt->bind_param("ii", $pId, $myId);
        $cStmt->execute();
        $cRes = $cStmt->get_result()->fetch_assoc();
        
        $row['unread'] = 0;
        if ($cRes) {
            $convId = $cRes['conversation_id'];
            $unread = $conn->query("SELECT COUNT(*) as count FROM chat_messages WHERE conversation_id = $convId AND sender_id != $myId AND status != 'seen'")->fetch_assoc();
            $row['unread'] = $unread['count'];
        }
        
        $partners[] = $row;
    }
    
    echo json_encode($partners);
}
