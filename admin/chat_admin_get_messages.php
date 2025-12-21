<?php
// admin/chat_admin_get_messages.php - Get messages for admin view
require '../_base.php';
require_login();
require_admin();
header('Content-Type: application/json');

$conversation_id = (int)get('conversation_id', 0);
$last_id = (int)get('last_id', 0);

if ($conversation_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation']);
    exit;
}

try {
    // Get new messages
    $stm = $_db->prepare("
        SELECT m.*, u.username
        FROM chat_messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = ? AND m.message_id > ?
        ORDER BY m.created_at ASC
    ");
    $stm->execute([$conversation_id, $last_id]);
    $messages = $stm->fetchAll(PDO::FETCH_ASSOC);
    
    // Mark member messages as read
    if (!empty($messages)) {
        $_db->prepare("
            UPDATE chat_messages 
            SET is_read = 1 
            WHERE conversation_id = ? 
              AND sender_type = 'member' 
              AND is_read = 0
        ")->execute([$conversation_id]);
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'count' => count($messages)
    ]);
    
} catch (Exception $e) {
    error_log('Admin get messages error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load messages']);
}