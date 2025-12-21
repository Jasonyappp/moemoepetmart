<?php
// member/chat_create_new.php - Force create new conversation
require '../_base.php';
require_login();
header('Content-Type: application/json');

if (!is_post()) {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user = current_user();
$user_id = $user->id;

if (user_role() !== 'member') {
    echo json_encode(['success' => false, 'message' => 'Only members can create conversations']);
    exit;
}

try {
    // Always create a new open conversation
    $_db->prepare("INSERT INTO chat_conversations (user_id, status) VALUES (?, 'open')")->execute([$user_id]);
    $conversation_id = $_db->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'conversation_id' => $conversation_id
    ]);
    
} catch (Exception $e) {
    error_log('Create conversation error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to create conversation ♡']);
}