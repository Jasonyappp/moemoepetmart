<?php
// admin/chat_close.php - Close a chat conversation
require '../_base.php';
require_login();
require_admin();
header('Content-Type: application/json');

if (!is_post()) {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$conversation_id = (int)post('conversation_id', 0);

if ($conversation_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid conversation']);
    exit;
}

try {
    // Close the conversation
    $stm = $_db->prepare("UPDATE chat_conversations SET status = 'closed' WHERE conversation_id = ?");
    $stm->execute([$conversation_id]);
    
    if ($stm->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Conversation closed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Conversation not found']);
    }
    
} catch (Exception $e) {
    error_log('Close conversation error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to close conversation']);
}