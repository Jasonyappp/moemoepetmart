<?php
// admin/chat_admin.php - Admin chat interface for replying to customers
require '../_base.php';
require_login();
require_admin();

$admin = current_user();
$conversation_id = (int)get('id', 0);

if ($conversation_id <= 0) {
    temp('error', 'Invalid conversation');
    redirect('chat_list.php');
}

// Get conversation details
$stm = $_db->prepare("
    SELECT c.*, u.username, u.profile_pic
    FROM chat_conversations c
    JOIN users u ON c.user_id = u.id
    WHERE c.conversation_id = ?
");
$stm->execute([$conversation_id]);
$conversation = $stm->fetch();

if (!$conversation) {
    temp('error', 'Conversation not found');
    redirect('chat_list.php');
}

// Assign admin to conversation if not assigned
if (!$conversation->admin_id) {
    $_db->prepare("UPDATE chat_conversations SET admin_id = ? WHERE conversation_id = ?")
        ->execute([$admin->id, $conversation_id]);
}

$_title = 'Chat with ' . $conversation->username . ' ♡ Admin';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .admin-chat-container {
            max-width: 1000px;
            margin: 2rem auto;
            background: white;
            border-radius: 25px;
            box-shadow: 0 10px 40px rgba(255,105,180,0.2);
            overflow: hidden;
            height: calc(100vh - 200px);
            display: flex;
            flex-direction: column;
        }

        .admin-chat-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-chat-header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .customer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }

        .customer-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .customer-info h3 {
            margin: 0;
            font-size: 1.3rem;
        }

        .customer-info p {
            margin: 5px 0 0 0;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .chat-actions {
            display: flex;
            gap: 10px;
        }

        .btn-close-chat {
            padding: 8px 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-close-chat:hover {
            background: white;
            color: #667eea;
        }

        .btn-back {
            padding: 8px 20px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 20px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-back:hover {
            transform: scale(1.05);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 25px;
            background: #f5f5f5;
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

        .message.admin {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message.member {
            align-self: flex-start;
        }

        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
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

        .message.member .message-avatar {
            background: linear-gradient(135deg, #ff69b4, #ff1493);
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

        .message.admin .message-bubble {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.member .message-bubble {
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

        .message.admin .message-time {
            text-align: right;
        }

        .admin-chat-input-area {
            padding: 20px 25px;
            background: white;
            border-top: 2px solid #e0e0e0;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .admin-chat-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #667eea;
            border-radius: 25px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s;
        }

        .admin-chat-input:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }

        .admin-send-btn {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
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

        .admin-send-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }

        .admin-send-btn:active {
            transform: scale(0.95);
        }

        .no-messages {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #f5f5f5;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="logo"><h2>MoeMoePet</h2></div>
        <ul>
            <li><a href="/admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/admin/product_list.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="/admin/member_list.php"><i class="fas fa-users"></i> Members</a></li>
            <li><a href="/admin/order_list.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="/admin/review_list.php"><i class="fas fa-star"></i> Reviews</a></li>
            <li><a href="/admin/chat_list.php" class="active"><i class="fas fa-comments"></i> Chats</a></li>
            <li><a href="/admin/report.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="/admin/profile.php"><i class="fas fa-user-cog"></i> My Profile ♛</a></li>
            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <div class="admin-chat-container">
            <!-- Chat Header -->
            <div class="admin-chat-header">
                <div class="admin-chat-header-left">
                    <div class="customer-avatar">
                        <?php if ($conversation->profile_pic && file_exists('../' . $conversation->profile_pic)): ?>
                            <img src="/<?= encode($conversation->profile_pic) ?>" alt="<?= encode($conversation->username) ?>">
                        <?php else: ?>
                            <?= strtoupper(substr($conversation->username, 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="customer-info">
                        <h3><?= encode($conversation->username) ?></h3>
                        <p>Customer</p>
                    </div>
                </div>
                <div class="chat-actions">
                    <?php if ($conversation->status === 'open'): ?>
                        <button class="btn-close-chat" onclick="closeConversation()">Close Chat</button>
                    <?php endif; ?>
                    <a href="chat_list.php" class="btn-back">← Back</a>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="chat-messages" id="chatMessages">
                <div class="no-messages" id="noMessages">
                    <p>Start chatting with <?= encode($conversation->username) ?> ♡</p>
                </div>
            </div>

            <!-- Input Area -->
            <div class="admin-chat-input-area">
                <input type="text" 
                       class="admin-chat-input" 
                       id="messageInput" 
                       placeholder="Type your reply here... ♡"
                       maxlength="1000">
                <button class="admin-send-btn" id="sendBtn" title="Send message">
                    ➤
                </button>
            </div>
        </div>
    </main>
</div>

<script>
const conversationId = <?= $conversation_id ?>;
const adminId = <?= $admin->id ?>;
const messagesContainer = document.getElementById('chatMessages');
const messageInput = document.getElementById('messageInput');
const sendBtn = document.getElementById('sendBtn');
const noMessages = document.getElementById('noMessages');

let lastMessageId = 0;
let isLoading = false;

// Load messages on page load
loadMessages();

// Auto-refresh messages every 2 seconds
setInterval(loadMessages, 2000);

// Send message on button click
sendBtn.addEventListener('click', sendMessage);

// Send message on Enter key
messageInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

function sendMessage() {
    const message = messageInput.value.trim();
    
    if (!message || isLoading) return;
    
    isLoading = true;
    sendBtn.disabled = true;
    sendBtn.innerHTML = '⏳';
    
    fetch('chat_admin_send.php', {
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
            alert(data.message || 'Failed to send message');
        }
    })
    .catch(error => {
        console.error('Send error:', error);
        alert('Error sending message. Please try again');
    })
    .finally(() => {
        isLoading = false;
        sendBtn.disabled = false;
        sendBtn.innerHTML = '➤';
        messageInput.focus();
    });
}

function loadMessages() {
    fetch(`chat_admin_get_messages.php?conversation_id=${conversationId}&last_id=${lastMessageId}`)
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
    avatar.textContent = msg.sender_type === 'admin' ? '👑' : '👤';
    
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

function closeConversation() {
    if (!confirm('Close this conversation? The customer won\'t be able to send new messages.')) {
        return;
    }
    
    fetch('chat_close.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            conversation_id: conversationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Conversation closed successfully ♡');
            location.href = 'chat_list.php';
        } else {
            alert(data.message || 'Failed to close conversation');
        }
    });
}

messageInput.focus();
</script>

</body>
</html>