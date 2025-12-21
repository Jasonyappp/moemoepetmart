    </main>

    <footer>
        Represented by <b>Moe Moe Pet Mart</b> &middot;
        Copyright &copy; <?= date('Y') ?> ♡
    </footer>

    <!-- Floating Chat Button (Only for Members) -->
    <?php if (isset($_SESSION['user']) && isset($_SESSION['role']) && $_SESSION['role'] === 'member'): ?>
    <?php
    // Get unread message count for current member
    $member_id = $_SESSION['user_id'] ?? 0;
    $unread_count = 0;
    
    if ($member_id > 0) {
        try {
            $stm = $_db->prepare("
                SELECT COUNT(*) as unread
                FROM chat_messages cm
                JOIN chat_conversations cc ON cm.conversation_id = cc.conversation_id
                WHERE cc.user_id = ?
                  AND cm.sender_type = 'admin'
                  AND cm.is_read = 0
            ");
            $stm->execute([$member_id]);
            $result = $stm->fetch();
            $unread_count = $result ? (int)$result->unread : 0;
        } catch (Exception $e) {
            error_log('Chat notification error: ' . $e->getMessage());
        }
    }
    ?>
    
    
    <style>
    .floating-chat-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #ff69b4, #ff1493);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.8rem;
        cursor: pointer;
        box-shadow: 0 10px 30px rgba(255,20,147,0.4);
        z-index: 9999;
        transition: all 0.3s;
        text-decoration: none;
        animation: chatPulse 2s infinite;
    }

    .floating-chat-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 15px 40px rgba(255,20,147,0.6);
    }

    .floating-chat-btn:active {
        transform: scale(0.95);
    }

    .chat-notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ff1493;
        color: white;
        border-radius: 50%;
        min-width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        animation: badgePop 0.5s ease;
    }

    .chat-notification-badge.has-unread {
        animation: badgePulse 1.5s infinite;
    }

    @keyframes chatPulse {
        0%, 100% {
            box-shadow: 0 10px 30px rgba(255,20,147,0.4);
        }
        50% {
            box-shadow: 0 10px 30px rgba(255,20,147,0.7), 0 0 0 10px rgba(255,20,147,0.2);
        }
    }

    @keyframes badgePop {
        0% {
            transform: scale(0);
        }
        50% {
            transform: scale(1.2);
        }
        100% {
            transform: scale(1);
        }
    }

    @keyframes badgePulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        50% {
            transform: scale(1.15);
            box-shadow: 0 4px 12px rgba(255,20,147,0.6);
        }
    }

    /* Mobile adjustments */
    @media (max-width: 768px) {
        .floating-chat-btn {
            bottom: 20px;
            right: 20px;
            width: 55px;
            height: 55px;
            font-size: 1.6rem;
        }
        
        .chat-notification-badge {
            min-width: 22px;
            height: 22px;
            font-size: 0.7rem;
            border: 2px solid white;
        }
    }
    </style>

    <a href="/member/chat.php" class="floating-chat-btn" title="Chat with us ♡">
        💬
        <?php if ($unread_count > 0): ?>
            <span class="chat-notification-badge has-unread">
                <?= $unread_count > 99 ? '99+' : $unread_count ?>
            </span>
        <?php endif; ?>
    </a>
    <?php endif; ?>
</body>
</html>