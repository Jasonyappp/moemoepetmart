<?php
// member/chat_get_messages.php - Get messages for a conversation
require '../_base.php';
require_login();
header('Content-Type: application/json');

$user = current_user();
$user_id = $user->id;
$conversation_id = (int)get('conversation_id', 0);
$last_id = (int)get('last_id', 0);

if ($conversation_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation']);
    exit;
}

// Check if conversation belongs to this user
$stm = $_db->prepare("SELECT user_id FROM chat_conversations WHERE conversation_id = ?");
$stm->execute([$conversation_id]);
$conv = $stm->fetch();

if (!$conv || $conv->user_id != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Get new messages (only those after last_id)
    $stm = $_db->prepare("
        SELECT m.*, u.username
        FROM chat_messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = ? AND m.message_id > ?
        ORDER BY m.created_at ASC
    ");
    $stm->execute([$conversation_id, $last_id]);
    $messages = $stm->fetchAll(PDO::FETCH_ASSOC);
    
    // Mark admin messages as read
    if (!empty($messages)) {
        $_db->prepare("
            UPDATE chat_messages 
            SET is_read = 1 
            WHERE conversation_id = ? 
              AND sender_type = 'admin' 
              AND is_read = 0
        ")->execute([$conversation_id]);
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages,
        'count' => count($messages)
    ]);
    
} catch (Exception $e) {
    error_log('Chat get messages error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to load messages ♡']);
}