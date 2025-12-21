<?php
// admin/chat_list.php - Admin view of all chat conversations
require '../_base.php';
require_login();
require_admin();

$_title = 'Customer Chats ♡ Admin';

// Get filter
$filter = get('filter', 'open'); // open, closed, all

$where = '1=1';
if ($filter === 'open') {
    $where = "c.status = 'open'";
} elseif ($filter === 'closed') {
    $where = "c.status = 'closed'";
}

// Get all conversations with unread count
$sql = "
    SELECT c.*, u.username, u.profile_pic,
           (SELECT COUNT(*) FROM chat_messages 
            WHERE conversation_id = c.conversation_id 
              AND sender_type = 'member' 
              AND is_read = 0) as unread_count,
           (SELECT message FROM chat_messages 
            WHERE conversation_id = c.conversation_id 
            ORDER BY created_at DESC LIMIT 1) as last_message,
           (SELECT created_at FROM chat_messages 
            WHERE conversation_id = c.conversation_id 
            ORDER BY created_at DESC LIMIT 1) as last_message_time
    FROM chat_conversations c
    JOIN users u ON c.user_id = u.id
    WHERE $where
    ORDER BY c.updated_at DESC
";

$conversations = $_db->query($sql)->fetchAll();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .chat-list-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 20px;
        }

        .chat-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            padding: 15px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(255,105,180,0.1);
        }

        .chat-filters a {
            padding: 10px 25px;
            text-decoration: none;
            color: #666;
            border-radius: 20px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .chat-filters a:hover {
            background: #fff0f5;
            color: #ff69b4;
        }

        .chat-filters a.active {
            background: linear-gradient(135deg, #ff69b4, #ff1493);
            color: white;
        }

        .conversation-card {
            background: white;
            border-radius: 20px;
            padding: 20px 25px;
            margin-bottom: 15px;
            box-shadow: 0 5px 15px rgba(255,105,180,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .conversation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255,105,180,0.2);
        }

        .conversation-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff69b4, #ff1493);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            font-weight: bold;
            flex-shrink: 0;
            position: relative;
        }

        .conversation-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .unread-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff1493;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
            border: 2px solid white;
        }

        .conversation-info {
            flex: 1;
            min-width: 0;
        }

        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .conversation-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: #ff1493;
        }

        .conversation-time {
            font-size: 0.85rem;
            color: #999;
        }

        .conversation-last-message {
            color: #666;
            font-size: 0.95rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-status {
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .status-open {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-closed {
            background: #ffebee;
            color: #c62828;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 5rem;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .conversation-card {
                flex-direction: column;
                align-items: flex-start;
            }

            .conversation-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
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
        <header class="admin-header">
            <h1>Customer Chats ♡</h1>
            <div class="admin-user">
                <i class="fas fa-user-circle"></i>
                <span><?= encode($_SESSION['user']) ?></span>
            </div>
        </header>

        <div class="chat-list-container">
            <!-- Filters -->
            <div class="chat-filters">
                <a href="?filter=open" class="<?= $filter === 'open' ? 'active' : '' ?>">
                    Open Chats
                </a>
                <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">
                    All Chats
                </a>
                <a href="?filter=closed" class="<?= $filter === 'closed' ? 'active' : '' ?>">
                    Closed Chats
                </a>
            </div>

            <!-- Conversation List -->
            <?php if (empty($conversations)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">💬</div>
                    <p style="font-size: 1.2rem;">No conversations yet~</p>
                    <p>Customer chats will appear here ♡</p>
                </div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <a href="chat_admin.php?id=<?= $conv->conversation_id ?>" class="conversation-card">
                        <div class="conversation-avatar">
                            <?php if ($conv->profile_pic && file_exists('../' . $conv->profile_pic)): ?>
                                <img src="/<?= encode($conv->profile_pic) ?>" alt="<?= encode($conv->username) ?>">
                            <?php else: ?>
                                <?= strtoupper(substr($conv->username, 0, 1)) ?>
                            <?php endif; ?>
                            
                            <?php if ($conv->unread_count > 0): ?>
                                <span class="unread-badge"><?= $conv->unread_count ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="conversation-info">
                            <div class="conversation-header">
                                <span class="conversation-name"><?= encode($conv->username) ?></span>
                                <span class="conversation-time">
                                    <?= $conv->last_message_time ? date('M d, H:i', strtotime($conv->last_message_time)) : date('M d, H:i', strtotime($conv->created_at)) ?>
                                </span>
                            </div>
                            <div class="conversation-last-message">
                                <?= $conv->last_message ? encode($conv->last_message) : 'No messages yet' ?>
                            </div>
                        </div>

                        <span class="conversation-status status-<?= $conv->status ?>">
                            <?= ucfirst($conv->status) ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
// Auto-refresh every 5 seconds
setInterval(() => {
    location.reload();
}, 5000);
</script>

</body>
</html>