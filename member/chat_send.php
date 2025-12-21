<?php
// member/chat_send.php - Send a new message
require '../_base.php';
require_login();
header('Content-Type: application/json');

if (!is_post()) {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user = current_user();
$user_id = $user->id;
$conversation_id = (int)post('conversation_id', 0);
$message = trim(post('message', ''));

// Validation
if ($conversation_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation']);
    exit;
}

if ($message === '') {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty ♡']);
    exit;
}

if (strlen($message) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Message too long (max 1000 characters) ♡']);
    exit;
}

// Check if conversation belongs to this user AND get status
$stm = $_db->prepare("SELECT user_id, status FROM chat_conversations WHERE conversation_id = ?");
$stm->execute([$conversation_id]);
$conv = $stm->fetch();

if (!$conv || $conv->user_id != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Block if conversation is closed
if ($conv->status === 'closed') {
    echo json_encode([
        'success' => false, 
        'message' => 'This conversation is closed. Please refresh the page to start a new conversation ♡',
        'closed' => true  // Signal to reload
    ]);
    exit;
}

try {
    // Insert message
    $stm = $_db->prepare("
        INSERT INTO chat_messages (conversation_id, sender_id, sender_type, message, is_read, created_at)
        VALUES (?, ?, 'member', ?, 0, NOW())
    ");
    $stm->execute([$conversation_id, $user_id, $message]);
    
    // Update conversation timestamp
    $_db->prepare("UPDATE chat_conversations SET updated_at = NOW() WHERE conversation_id = ?")
        ->execute([$conversation_id]);
    
    echo json_encode([
        'success' => true,
        'message_id' => $_db->lastInsertId()
    ]);
    
} catch (Exception $e) {
    error_log('Chat send error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to send message ♡']);
}