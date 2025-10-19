<?php
require_once '../../config.php';
require_auth();

require_once '../../models/Message.php';
require_once '../../models/User.php';

$messageModel = new Message();
$userModel = new User();

$conversations = $messageModel->getRecentConversations($_SESSION['user_id']);
$users = $userModel->getAll();

$page_title = "Messages";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - PropertyHub</title>
    <link rel="stylesheet" href="<?php echo asset_url('css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset_url('css/chat.css'); ?>">
    <style>
        /* Chat Layout */
    .chat-body {
        margin: 0;
        background: #f0f2f5;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    .chat-container {
        display: flex;
        height: calc(100vh - 70px);
        max-width: 1400px;
        margin: 0 auto;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* Sidebar */
    .chat-sidebar {
        width: 350px;
        border-right: 1px solid #e9edef;
        background: #fff;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 16px;
        border-bottom: 1px solid #e9edef;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f0f2f5;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #0084ff;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
    }

    .sidebar-header h3 {
        margin: 0;
        color: #41525d;
        font-size: 18px;
    }

    .new-chat-btn {
        background: none;
        border: none;
        color: #54656f;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: background 0.2s;
    }

    .new-chat-btn:hover {
        background: #e9edef;
    }

    /* Search */
    .search-container {
        padding: 12px;
        border-bottom: 1px solid #e9edef;
    }

    .search-box {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-box svg {
        position: absolute;
        left: 12px;
        color: #54656f;
    }

    .search-box input {
        width: 100%;
        padding: 12px 12px 12px 40px;
        border: none;
        border-radius: 8px;
        background: #f0f2f5;
        font-size: 14px;
        outline: none;
    }

    .search-box input:focus {
        background: #fff;
        box-shadow: 0 0 0 2px #0084ff;
    }

    /* Conversations List */
    .conversations-list {
        flex: 1;
        overflow-y: auto;
    }

    .conversation-item {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f0f2f5;
        transition: background 0.2s;
        gap: 12px;
    }

    .conversation-item:hover {
        background: #f5f5f5;
    }

    .conversation-item.active {
        background: #e9edef;
    }

    .conversation-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 18px;
        color: white;
        flex-shrink: 0;
    }

    .avatar-blue { background: #0084ff; }
    .avatar-green { background: #00a884; }
    .avatar-purple { background: #7c4dff; }
    .avatar-orange { background: #ff6d00; }
    .avatar-pink { background: #e91e63; }

    .conversation-content {
        flex: 1;
        min-width: 0;
    }

    .conversation-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 4px;
    }

    .conversation-info {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .conversation-info h4 {
        margin: 0;
        font-size: 16px;
        color: #3b4a54;
        font-weight: 600;
    }

    .user-badge {
        background: #e9edef;
        color: #667781;
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 500;
    }

    .message-time {
        font-size: 12px;
        color: #667781;
        white-space: nowrap;
    }

    .message-preview {
        margin: 0;
        font-size: 14px;
        color: #667781;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Chat Area */
    .chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #efeae2;
        background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23f0f0f0' fill-opacity='0.4' fill-rule='evenodd'/%3E%3C/svg%3E");
    }

    .chat-header {
        padding: 16px 20px;
        background: #f0f2f5;
        border-bottom: 1px solid #e9edef;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chat-partner-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .partner-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #0084ff;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
    }

    .partner-details h3 {
        margin: 0 0 2px 0;
        font-size: 16px;
        color: #3b4a54;
    }

    .partner-status {
        font-size: 13px;
        color: #667781;
    }

    .chat-actions {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        background: none;
        border: none;
        color: #54656f;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        transition: background 0.2s;
    }

    .action-btn:hover {
        background: #e9edef;
    }

    /* Messages */
    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .welcome-screen {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        text-align: center;
    }

    .welcome-content svg {
        color: #667781;
        margin-bottom: 16px;
    }

    .welcome-content h2 {
        margin: 0 0 8px 0;
        color: #3b4a54;
        font-weight: 300;
    }

    .welcome-content p {
        margin: 0;
        color: #667781;
        font-size: 14px;
    }

    .empty-chat {
        text-align: center;
        padding: 40px 20px;
        color: #667781;
    }

    .date-divider {
        text-align: center;
        margin: 16px 0;
        position: relative;
    }

    .date-divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e9edef;
        z-index: 1;
    }

    .date-divider span {
        background: #f0f2f5;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        color: #667781;
        position: relative;
        z-index: 2;
    }

    .message {
        display: flex;
        margin-bottom: 8px;
    }

    .message.sent {
        justify-content: flex-end;
    }

    .message.received {
        justify-content: flex-start;
    }

    .message-bubble {
        max-width: 65%;
        padding: 8px 12px;
        border-radius: 8px;
        position: relative;
    }

    .message.sent .message-bubble {
        background: #d9fdd3;
        border-top-right-radius: 0;
    }

    .message.received .message-bubble {
        background: #fff;
        border-top-left-radius: 0;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .message-text {
        margin-bottom: 4px;
        font-size: 14px;
        line-height: 1.4;
        color: #3b4a54;
        word-wrap: break-word;
    }

    .message-time {
        font-size: 11px;
        color: #667781;
        text-align: right;
    }

    .message.received .message-time {
        text-align: left;
    }

    /* Chat Input */
    .chat-input-container {
        padding: 16px 20px;
        background: #f0f2f5;
        border-top: 1px solid #e9edef;
    }

    .message-form {
        width: 100%;
    }

    .input-wrapper {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        background: #fff;
        border-radius: 24px;
        padding: 8px 12px;
        border: 1px solid #e9edef;
    }

    .attach-btn, .send-btn {
        background: none;
        border: none;
        color: #54656f;
        cursor: pointer;
        padding: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .attach-btn:hover, .send-btn:hover {
        background: #f0f2f5;
    }

    .send-btn {
        color: #0084ff;
    }

    .message-input-wrapper {
        flex: 1;
    }

    #messageInput {
        width: 100%;
        border: none;
        outline: none;
        resize: none;
        font-size: 14px;
        line-height: 1.4;
        max-height: 120px;
        font-family: inherit;
    }

    #messageInput::placeholder {
        color: #667781;
    }

    /* Modal */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-dialog {
        background: #fff;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        max-height: 80vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    </style>
</head>
<body class="chat-body">
    <?php include '../includes/header.php'; ?>

    <div class="chat-container">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-header">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['first_name'], 0, 1)); ?>
                    </div>
                    <h3>Messages</h3>
                </div>
                <button class="new-chat-btn" onclick="openNewMessageModal()" title="New Message">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                    </svg>
                </button>
            </div>

            <div class="search-container">
                <div class="search-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                    </svg>
                    <input type="text" placeholder="Search conversations..." id="searchConversations">
                </div>
            </div>

            <div class="conversations-list">
                <?php if (empty($conversations)): ?>
                    <div class="no-conversations">
                        <div class="empty-state">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/>
                            </svg>
                            <h3>No conversations</h3>
                            <p>Start a new conversation to connect with others</p>
                            <button class="btn-primary" onclick="openNewMessageModal()">Start Chatting</button>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($conversations as $conversation): ?>
                    <div class="conversation-item" data-user-id="<?php echo $conversation['other_user_id']; ?>">
                        <div class="conversation-avatar <?php echo getAvatarColor($conversation['other_user_id']); ?>">
                            <?php echo strtoupper(substr($conversation['first_name'], 0, 1)); ?>
                        </div>
                        <div class="conversation-content">
                            <div class="conversation-header">
                                <div class="conversation-info">
                                    <h4><?php echo htmlspecialchars($conversation['first_name'] . ' ' . $conversation['last_name']); ?></h4>
                                    <span class="user-badge"><?php echo ucfirst($conversation['user_type']); ?></span>
                                </div>
                                <span class="message-time"><?php echo formatMessageTime($conversation['last_message_time']); ?></span>
                            </div>
                            <p class="message-preview"><?php echo htmlspecialchars(substr($conversation['last_message'], 0, 60)); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <div class="chat-header" id="chatHeader">
                <div class="chat-partner-info">
                    <div class="partner-avatar" id="partnerAvatar">
                        <span>?</span>
                    </div>
                    <div class="partner-details">
                        <h3 id="partnerName">Select a conversation</h3>
                        <span class="partner-status" id="partnerStatus">Click on a conversation to start chatting</span>
                    </div>
                </div>
                <div class="chat-actions">
                    <button class="action-btn" title="Voice call">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 15.5c-1.25 0-2.45-.2-3.57-.57-.35-.11-.74-.03-1.02.24l-2.2 2.2c-2.83-1.44-5.15-3.75-6.59-6.59l2.2-2.21c.28-.26.36-.65.25-1C8.7 6.45 8.5 5.25 8.5 4c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1 0 9.39 7.61 17 17 17 .55 0 1-.45 1-1v-3.5c0-.55-.45-1-1-1zM12 3v10l3-3h6V3h-9z"/>
                        </svg>
                    </button>
                    <button class="action-btn" title="Video call">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <div class="welcome-screen">
                    <div class="welcome-content">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/>
                        </svg>
                        <h2>Your Messages</h2>
                        <p>Select a conversation from the sidebar to start messaging</p>
                    </div>
                </div>
            </div>

            <div class="chat-input-container" id="chatInput" style="display: none;">
                <form id="messageForm" class="message-form">
                    <input type="hidden" id="receiver_id" name="receiver_id">
                    <div class="input-wrapper">
                        <button type="button" class="attach-btn" title="Attach file">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16.5 6v11.5c0 2.21-1.79 4-4 4s-4-1.79-4-4V5c0-1.38 1.12-2.5 2.5-2.5s2.5 1.12 2.5 2.5v10.5c0 .55-.45 1-1 1s-1-.45-1-1V6H10v9.5c0 1.38 1.12 2.5 2.5 2.5s2.5-1.12 2.5-2.5V5c0-2.21-1.79-4-4-4S7 2.79 7 5v12.5c0 3.04 2.46 5.5 5.5 5.5s5.5-2.46 5.5-5.5V6h-1.5z"/>
                            </svg>
                        </button>
                        <div class="message-input-wrapper">
                            <textarea id="messageInput" name="message" placeholder="Type a message..." rows="1"></textarea>
                        </div>
                        <button type="submit" class="send-btn" title="Send message">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- New Message Modal -->
    <div id="newMessageModal" class="modal-overlay">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3>New Message</h3>
                <button class="close-btn" onclick="closeNewMessageModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="search-container">
                    <div class="search-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                        <input type="text" placeholder="Search users..." id="userSearch">
                    </div>
                </div>
                <div class="users-list">
                    <?php foreach ($users as $user): ?>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <div class="user-item" data-user-id="<?php echo $user['id']; ?>">
                            <div class="user-avatar <?php echo getAvatarColor($user['id']); ?>">
                                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                            </div>
                            <div class="user-info">
                                <h4><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h4>
                                <span class="user-type"><?php echo ucfirst($user['user_type']); ?></span>
                            </div>
                            <button class="select-user-btn" onclick="selectUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')">
                                Message
                            </button>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    let currentChatUserId = null;
    let pollInterval = null;

    // Modal functions
    function openNewMessageModal() {
        document.getElementById('newMessageModal').style.display = 'flex';
        document.getElementById('userSearch').focus();
    }

    function closeNewMessageModal() {
        document.getElementById('newMessageModal').style.display = 'none';
    }

    function selectUser(userId, userName) {
        closeNewMessageModal();
        loadConversation(userId, userName);
    }

    // Conversation selection
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.querySelector('h4').textContent;
            loadConversation(userId, userName);
        });
    });

    function loadConversation(userId, userName) {
        currentChatUserId = userId;
        
        // Update UI
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.remove('active');
        });
        document.querySelector(`[data-user-id="${userId}"]`).classList.add('active');
        
        document.getElementById('partnerName').textContent = userName;
        document.getElementById('partnerAvatar').innerHTML = userName.charAt(0);
        document.getElementById('partnerStatus').textContent = 'Online';
        document.getElementById('chatInput').style.display = 'block';
        document.getElementById('receiver_id').value = userId;

        // Load messages
        fetchMessages(userId);

        // Start polling for new messages
        if (pollInterval) clearInterval(pollInterval);
        pollInterval = setInterval(() => fetchMessages(userId), 3000);
    }

    function fetchMessages(userId) {
        fetch(`<?php echo BASE_URL; ?>controllers/MessageController.php?action=get_conversation&receiver_id=${userId}`)
            .then(response => response.json())
            .then(messages => {
                displayMessages(messages);
            })
            .catch(error => {
                console.error('Error loading messages:', error);
            });
    }

    function displayMessages(messages) {
        const chatMessages = document.getElementById('chatMessages');
        
        if (messages.length === 0) {
            chatMessages.innerHTML = `
                <div class="empty-chat">
                    <p>No messages yet. Start the conversation!</p>
                </div>
            `;
            return;
        }

        let messagesHTML = '';
        let lastDate = null;

        messages.forEach(message => {
            const messageDate = new Date(message.created_at).toDateString();
            if (messageDate !== lastDate) {
                messagesHTML += `<div class="date-divider">${formatDate(message.created_at)}</div>`;
                lastDate = messageDate;
            }

            const isSent = message.sender_id == <?php echo $_SESSION['user_id']; ?>;
            messagesHTML += `
                <div class="message ${isSent ? 'sent' : 'received'}">
                    <div class="message-bubble">
                        <div class="message-text">${escapeHtml(message.message)}</div>
                        <div class="message-time">${formatTime(message.created_at)}</div>
                    </div>
                </div>
            `;
        });

        chatMessages.innerHTML = messagesHTML;
        scrollToBottom();
    }

    function scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Message form handling
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = document.getElementById('messageInput').value.trim();
        const receiverId = document.getElementById('receiver_id').value;

        if (!message || !receiverId) return;

        const formData = new FormData();
        formData.append('action', 'send_message');
        formData.append('receiver_id', receiverId);
        formData.append('message', message);

        fetch('<?php echo BASE_URL; ?>controllers/MessageController.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('messageInput').value = '';
                fetchMessages(receiverId);
            }
        })
        .catch(error => {
            console.error('Error sending message:', error);
        });
    });

    // Auto-resize textarea
    document.getElementById('messageInput').addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Utility functions
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatTime(dateString) {
        return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        if (date.toDateString() === today.toDateString()) {
            return 'Today';
        } else if (date.toDateString() === yesterday.toDateString()) {
            return 'Yesterday';
        } else {
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
        }
    }

    // Search functionality
    document.getElementById('searchConversations').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.conversation-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchTerm) ? 'flex' : 'none';
        });
    });

    document.getElementById('userSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('.user-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchTerm) ? 'flex' : 'none';
        });
    });

    // Close modal when clicking outside
    document.getElementById('newMessageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeNewMessageModal();
        }
    });

    // Initialize
    scrollToBottom();
    </script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>

<?php
// Helper functions
function getAvatarColor($userId) {
    $colors = ['avatar-blue', 'avatar-green', 'avatar-purple', 'avatar-orange', 'avatar-pink'];
    return $colors[$userId % count($colors)];
}

function formatMessageTime($timestamp) {
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . 'm';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . 'h';
    } else {
        return date('M j', $time);
    }
}
?>