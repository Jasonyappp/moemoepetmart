<?php
// member/chat.php - Real-time chat interface for members
require '../_base.php';
require_login();

if (user_role() !== 'member') {
    temp('error', 'Only members can access chat ♡');
    redirect('../index.php');
}

$user = current_user();
$user_id = $user->id;

// Get the conversation to display (either from URL or most recent)
$view_conversation_id = (int)get('id', 0);

if ($view_conversation_id > 0) {
    // Viewing specific conversation - verify it belongs to user
    $stm = $_db->prepare("SELECT conversation_id, status FROM chat_conversations WHERE conversation_id = ? AND user_id = ?");
    $stm->execute([$view_conversation_id, $user_id]);
    $conversation = $stm->fetch();
    
    if (!$conversation) {
        temp('error', 'Conversation not found ♡');
        redirect('chat.php');
    }
    
    $conversation_id = $conversation->conversation_id;
    $is_closed = ($conversation->status === 'closed');
} else {
    // Get most recent conversation
    $stm = $_db->prepare("SELECT conversation_id, status FROM chat_conversations WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1");
    $stm->execute([$user_id]);
    $conversation = $stm->fetch();
    
    if (!$conversation) {
        // No conversation exists - create new one
        $_db->prepare("INSERT INTO chat_conversations (user_id, status) VALUES (?, 'open')")->execute([$user_id]);
        $conversation_id = $_db->lastInsertId();
        $is_closed = false;
    } else {
        $conversation_id = $conversation->conversation_id;
        $is_closed = ($conversation->status === 'closed');
    }
}

// Get all conversations for this user (for history sidebar)
$all_conversations = $_db->prepare("
    SELECT c.conversation_id, c.status, c.created_at,
           (SELECT message FROM chat_messages 
            WHERE conversation_id = c.conversation_id 
            ORDER BY created_at DESC LIMIT 1) as last_message
    FROM chat_conversations c
    WHERE c.user_id = ?
    ORDER BY c.updated_at DESC
")->execute([$user_id]);
$all_conversations = $_db->prepare("
    SELECT c.conversation_id, c.status, c.created_at,
           (SELECT message FROM chat_messages 
            WHERE conversation_id = c.conversation_id 
            ORDER BY created_at DESC LIMIT 1) as last_message
    FROM chat_conversations c
    WHERE c.user_id = ?
    ORDER BY c.updated_at DESC
")->fetchAll();
// Get all conversations for this user (for history sidebar)
$stm = $_db->prepare("
    SELECT c.conversation_id, c.status, c.created_at,
           (SELECT message FROM chat_messages 
            WHERE conversation_id = c.conversation_id 
            ORDER BY created_at DESC LIMIT 1) as last_message
    FROM chat_conversations c
    WHERE c.user_id = ?
    ORDER BY c.updated_at DESC
");
$stm->execute([$user_id]);
$all_conversations = $stm->fetchAll();

$_title = 'Chat with Us ♡ Moe Moe Pet Mart';
include '../_head.php';
?>

<style>
.chat-wrapper {
    display: flex;
    max-width: 1200px;
    margin: 2rem auto;
    gap: 20px;
    height: 600px;
}

.chat-history-sidebar {
    width: 280px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(255,105,180,0.15);
    overflow-y: auto;
    padding: 15px;
}

.history-header {
    font-size: 1.1rem;
    font-weight: bold;
    color: #ff1493;
    margin-bottom: 15px;
    padding: 10px;
}

.history-item {
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s;
    border: 2px solid transparent;
}

.history-item:hover {
    background: #fff0f5;
    border-color: #ffd4e4;
}

.history-item.active {
    background: linear-gradient(135deg, #ff69b4, #ff1493);
    color: white;
}

.history-item-status {
    font-size: 0.75rem;
    padding: 3px 8px;
    border-radius: 10px;
    display: inline-block;
    margin-bottom: 5px;
}

.history-item.active .history-item-status {
    background: rgba(255,255,255,0.3);
}

.history-item:not(.active) .history-item-status.open {
    background: #e8f5e9;
    color: #2e7d32;
}

.history-item:not(.active) .history-item-status.closed {
    background: #ffebee;
    color: #c62828;
}

.history-item-preview {
    font-size: 0.85rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    opacity: 0.8;
}

.new-chat-btn {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #ff69b4, #ff1493);
    color: white;
    border: none;
    border-radius: 15px;
    font-weight: bold;
    cursor: pointer;
    margin-bottom: 15px;
    transition: all 0.3s;
}

.new-chat-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255,20,147,0.4);
}

.chat-container {
    flex: 1;
    background: white;
    border-radius: 25px;
    box-shadow: 0 10px 40px rgba(255,105,180,0.2);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.chat-header {
    background: linear-gradient(135deg, #ff69b4, #ff1493);
    color: white;
    padding: 20px 25px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chat-header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

.chat-header-icon {
    width: 50px;
    height: 50px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.chat-header-info h3 {
    margin: 0;
    font-size: 1.3rem;
}

.chat-header-info p {
    margin: 5px 0 0 0;
    font-size: 0.9rem;
    opacity: 0.9;
}

.chat-status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: bold;
    background: rgba(255,255,255,0.3);
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 25px;
    background: #fff5f9;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.message {
    display: flex;
    gap: 12px;
    max-width: 70%;
    animation: messageSlide 0.3s ease;
}

@keyframes messageSlide {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message.member {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message.admin {
    align-self: flex-start;
}

.message-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ff69b4, #ff1493);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    flex-shrink: 0;
}

.message.admin .message-avatar {
    background: linear-gradient(135deg, #667eea, #764ba2);
}

.message-content {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.message-bubble {
    padding: 12px 18px;
    border-radius: 18px;
    word-wrap: break-word;
    line-height: 1.5;
}

.message.member .message-bubble {
    background: linear-gradient(135deg, #ff69b4, #ff1493);
    color: white;
    border-bottom-right-radius: 4px;
}

.message.admin .message-bubble {
    background: white;
    color: #333;
    border: 2px solid #e0e0e0;
    border-bottom-left-radius: 4px;
}

.message-time {
    font-size: 0.75rem;
    color: #999;
    padding: 0 8px;
}

.message.member .message-time {
    text-align: right;
}

.chat-input-area {
    padding: 20px 25px;
    background: white;
    border-top: 2px solid #ffd4e4;
    display: flex;
    gap: 15px;
    align-items: center;
}

.chat-input-area.disabled {
    opacity: 0.6;
    pointer-events: none;
}

.chat-input {
    flex: 1;
    padding: 12px 20px;
    border: 2px solid #ff69b4;
    border-radius: 25px;
    font-size: 1rem;
    outline: none;
    transition: all 0.3s;
}

.chat-input:focus {
    border-color: #ff1493;
    box-shadow: 0 0 0 3px rgba(255,105,180,0.1);
}

.chat-send-btn {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #ff69b4, #ff1493);
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 1.3rem;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chat-send-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 5px 15px rgba(255,20,147,0.4);
}

.chat-send-btn:active {
    transform: scale(0.95);
}

.closed-notice {
    background: #ffebee;
    color: #c62828;
    padding: 15px 20px;
    text-align: center;
    font-weight: bold;
    border-radius: 12px;
    margin: 15px 25px;
}

.no-messages {
    text-align: center;
    padding: 40px;
    color: #999;
}

.no-messages-icon {
    font-size: 4rem;
    margin-bottom: 15px;
}

.chat-messages::-webkit-scrollbar {
    width: 8px;
}

.chat-messages::-webkit-scrollbar-track {
    background: #fff0f5;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #ff69b4;
    border-radius: 4px;
}

@media (max-width: 968px) {
    .chat-wrapper {
        flex-direction: column;
        height: auto;
    }
    
    .chat-history-sidebar {
        width: 100%;
        height: 200px;
    }
    
    .chat-container {
        height: 500px;
    }
}
</style>

<div class="chat-wrapper">
    <!-- Chat History Sidebar -->
    <div class="chat-history-sidebar">
        <div class="history-header">💬 Chat History</div>
        
        <button class="new-chat-btn" onclick="createNewChat()">
            ➕ Start New Chat
        </button>
        
        <?php foreach ($all_conversations as $conv): ?>
            <div class="history-item <?= $conv->conversation_id == $conversation_id ? 'active' : '' ?>"
                 onclick="location.href='chat.php?id=<?= $conv->conversation_id ?>'">
                <div class="history-item-status <?= $conv->status ?>">
                    <?= ucfirst($conv->status) ?>
                </div>
                <div style="font-size: 0.8rem; margin-bottom: 5px;">
                    <?= date('M d, Y', strtotime($conv->created_at)) ?>
                </div>
                <div class="history-item-preview">
                    <?= $conv->last_message ? encode(substr($conv->last_message, 0, 40)) . '...' : 'No messages yet' ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Chat Container -->
    <div class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="chat-header-left">
                <div class="chat-header-icon">💬</div>
                <div class="chat-header-info">
                    <h3>Moe Moe Support Team</h3>
                    <p>We're here to help you! ♡</p>
                </div>
            </div>
            <div class="chat-status-badge">
                <?= $is_closed ? '🔒 Closed' : '🟢 Active' ?>
            </div>
        </div>

        <?php if ($is_closed): ?>
            <div class="closed-notice">
                ⚠️ This conversation is closed. Please start a new chat to continue.
            </div>
        <?php endif; ?>

        <!-- Messages Area -->
        <div class="chat-messages" id="chatMessages">
            <div class="no-messages" id="noMessages">
                <div class="no-messages-icon">💬</div>
                <p>Start a conversation with us! ♡</p>
            </div>
        </div>

        <!-- Input Area -->
        <div class="chat-input-area <?= $is_closed ? 'disabled' : '' ?>">
            <input type="text" 
                   class="chat-input" 
                   id="messageInput" 
                   placeholder="<?= $is_closed ? 'This chat is closed' : 'Type your message here... ♡' ?>"
                   maxlength="1000"
                   <?= $is_closed ? 'disabled' : '' ?>>
            <button class="chat-send-btn" id="sendBtn" title="Send message" <?= $is_closed ? 'disabled' : '' ?>>
                ➤
            </button>
        </div>
    </div>
</div>

<script>
const conversationId = <?= $conversation_id ?>;
const userId = <?= $user_id ?>;
const isClosed = <?= $is_closed ? 'true' : 'false' ?>;
const messagesContainer = document.getElementById('chatMessages');
const messageInput = document.getElementById('messageInput');
const sendBtn = document.getElementById('sendBtn');
const noMessages = document.getElementById('noMessages');

let lastMessageId = 0;
let isLoading = false;

// Load messages on page load
loadMessages();

// Auto-refresh messages every 2 seconds (only if not closed)
if (!isClosed) {
    setInterval(loadMessages, 2000);
}

// Send message on button click
if (!isClosed) {
    sendBtn.addEventListener('click', sendMessage);
    
    // Send message on Enter key
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });
}

function createNewChat() {
    if (confirm('Start a new conversation? ♡')) {
        // Force create new conversation via AJAX
        fetch('chat_create_new.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.href = 'chat.php?id=' + data.conversation_id;
            } else {
                alert(data.message || 'Failed to create new conversation');
            }
        })
        .catch(error => {
            console.error('Create chat error:', error);
            alert('Error creating new conversation');
        });
    }
}

function sendMessage() {
    const message = messageInput.value.trim();
    
    if (!message || isLoading) return;
    
    isLoading = true;
    sendBtn.disabled = true;
    sendBtn.innerHTML = '⏳';
    
    fetch('chat_send.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            conversation_id: conversationId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            loadMessages();
        } else {
            if (data.closed) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Failed to send message ♡');
            }
        }
    })
    .catch(error => {
        console.error('Send error:', error);
        alert('Error sending message. Please try again ♡');
    })
    .finally(() => {
        isLoading = false;
        sendBtn.disabled = false;
        sendBtn.innerHTML = '➤';
        if (!isClosed) messageInput.focus();
    });
}

function loadMessages() {
    fetch(`chat_get_messages.php?conversation_id=${conversationId}&last_id=${lastMessageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.messages.length > 0) {
                if (noMessages) {
                    noMessages.style.display = 'none';
                }
                
                data.messages.forEach(msg => {
                    appendMessage(msg);
                    lastMessageId = Math.max(lastMessageId, msg.message_id);
                });
                
                scrollToBottom();
            }
        })
        .catch(error => {
            console.error('Load messages error:', error);
        });
}

function appendMessage(msg) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${msg.sender_type}`;
    messageDiv.dataset.messageId = msg.message_id;
    
    const avatar = document.createElement('div');
    avatar.className = 'message-avatar';
    avatar.textContent = msg.sender_type === 'member' ? '👤' : '👑';
    
    const content = document.createElement('div');
    content.className = 'message-content';
    
    const bubble = document.createElement('div');
    bubble.className = 'message-bubble';
    bubble.textContent = msg.message;
    
    const time = document.createElement('div');
    time.className = 'message-time';
    time.textContent = formatTime(msg.created_at);
    
    content.appendChild(bubble);
    content.appendChild(time);
    
    messageDiv.appendChild(avatar);
    messageDiv.appendChild(content);
    
    messagesContainer.appendChild(messageDiv);
}

function scrollToBottom() {
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function formatTime(datetime) {
    const date = new Date(datetime);
    const now = new Date();
    const diff = now - date;
    
    if (diff < 60000) {
        return 'Just now';
    }
    
    if (diff < 3600000) {
        const mins = Math.floor(diff / 60000);
        return `${mins} min${mins > 1 ? 's' : ''} ago`;
    }
    
    if (date.toDateString() === now.toDateString()) {
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }
    
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

if (!isClosed) messageInput.focus();
</script>

<?php include '../_foot.php'; ?>