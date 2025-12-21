<?php
// admin/chat_admin_send.php - Admin sends message to customer
require '../_base.php';
require_login();
require_admin();
header('Content-Type: application/json');

if (!is_post()) {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$admin = current_user();
$admin_id = $admin->id;
$conversation_id = (int)post('conversation_id', 0);
$message = trim(post('message', ''));

// Validation
if ($conversation_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation']);
    exit;
}

if ($message === '') {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

if (strlen($message) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Message too long (max 1000 characters)']);
    exit;
}

// Check if conversation exists
$stm = $_db->prepare("SELECT conversation_id, status FROM chat_conversations WHERE conversation_id = ?");
$stm->execute([$conversation_id]);
$conv = $stm->fetch();

if (!$conv) {
    echo json_encode(['success' => false, 'message' => 'Conversation not found']);
    exit;
}

if ($conv->status === 'closed') {
    echo json_encode(['success' => false, 'message' => 'This conversation is closed']);
    exit;
}

try {
    // Insert message
    $stm = $_db->prepare("
        INSERT INTO chat_messages (conversation_id, sender_id, sender_type, message, is_read, created_at)
        VALUES (?, ?, 'admin', ?, 0, NOW())
    ");
    $stm->execute([$conversation_id, $admin_id, $message]);
    
    // Update conversation timestamp
    $_db->prepare("UPDATE chat_conversations SET updated_at = NOW(), admin_id = ? WHERE conversation_id = ?")
        ->execute([$admin_id, $conversation_id]);
    
    echo json_encode([
        'success' => true,
        'message_id' => $_db->lastInsertId()
    ]);
    
} catch (Exception $e) {
    error_log('Admin chat send error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}