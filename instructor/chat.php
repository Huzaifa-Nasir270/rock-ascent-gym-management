<?php
/**
 * Instructor - Live Chat
 */

require_once('../config/functions.php');
requireInstructor();

$instructorId = $_SESSION['instructor_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Live Chat - Instructor</title>
    <?php include('../includes/head.php'); ?>
    <style>
        .chat-layout {
            display: grid;
            grid-template-columns: 350px 1fr;
            height: calc(100vh - 180px);
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .chat-sidebar {
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .partner-list {
            flex: 1;
            overflow-y: auto;
        }
        .partner-item {
            padding: 20px 25px;
            cursor: pointer;
            transition: all 0.3s;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .partner-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .partner-item.active {
            background: var(--primary-gradient);
            color: white;
        }
        .partner-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .chat-main {
            display: flex;
            flex-direction: column;
            background: rgba(15, 23, 42, 0.2);
        }
        .chat-header {
            padding: 20px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .chat-messages {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .message {
            max-width: 70%;
            padding: 15px 20px;
            border-radius: 20px;
            font-size: 0.95rem;
            position: relative;
        }
        .message.sent {
            align-self: flex-end;
            background: var(--primary-gradient);
            color: white;
            border-bottom-right-radius: 5px;
        }
        .message.received {
            align-self: flex-start;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-bottom-left-radius: 5px;
        }
        .message-time {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 5px;
            display: block;
            text-align: right;
        }
        .chat-input-area {
            padding: 25px 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .chat-input-group {
            display: flex;
            gap: 15px;
        }
        .chat-input {
            flex: 1;
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            border-radius: 15px !important;
            padding: 12px 20px !important;
        }
        .send-btn {
            width: 50px;
            height: 50px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
        }
        .unread-badge {
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 10px;
            margin-left: auto;
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/instructor_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4 fade-in">
                    <h2 class="mb-0">💬 Member Live Chat</h2>
                    <div class="text-muted small">Real-time communication with your members</div>
                </div>

                <div class="chat-layout shadow-lg fade-in-up">
                    <!-- Sidebar: Member List -->
                    <div class="chat-sidebar">
                        <div class="sidebar-header">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent border-right-0" style="border-color: rgba(255,255,255,0.1);"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" id="memberSearch" class="form-control bg-transparent border-left-0" placeholder="Search members..." style="border-color: rgba(255,255,255,0.1); color: white;">
                            </div>
                        </div>
                        <div class="partner-list" id="partnerList">
                            <div class="p-4 text-center text-muted">Loading members...</div>
                        </div>
                    </div>

                    <!-- Main Chat Area -->
                    <div class="chat-main">
                        <div id="chatPlaceholder" class="h-100 d-flex flex-column align-items-center justify-content-center text-muted fade-in">
                            <i class="fas fa-comments fa-4x mb-3" style="opacity: 0.1;"></i>
                            <p class="font-weight-bold">Select a member to start chatting</p>
                            <small>All messages are encrypted and secure</small>
                        </div>

                        <div id="chatContent" class="h-100 flex-column d-none fade-in">
                            <div class="chat-header">
                                <div class="partner-avatar" id="activeAvatar">A</div>
                                <div>
                                    <h5 class="mb-0 font-weight-bold" id="activeName">Member Name</h5>
                                    <span id="onlineStatus" class="small text-muted"><i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Offline</span>
                                </div>
                            </div>
                            <div class="chat-messages" id="messageContainer">
                                <!-- Messages will be loaded here instantly -->
                            </div>
                            <div class="chat-input-area">
                                <div id="typingIndicator" class="small text-muted mb-2" style="display:none;">Member is typing...</div>
                                <form id="chatForm" class="chat-input-group">
                                    <input type="text" id="messageInput" class="chat-input" placeholder="Type your response..." autocomplete="off">
                                    <button type="submit" class="btn btn-primary send-btn">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <!-- Load Socket.IO Client -->
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <?php include('../includes/scripts.php'); ?>
    <script>
        const socket = io('http://localhost:3000');
        let activePartnerId = null;
        let conversationId = null;
        const myId = <?php echo $_SESSION['instructor_id']; ?>;
        const myRole = 'instructor';

        // 1. Partner Management
        function loadPartners() {
            $.get('../includes/chat_handler.php?action=get_partners&role=instructor', function(data) {
                const partners = JSON.parse(data);
                renderPartners(partners);
            });
        }

        function renderPartners(partners) {
            let html = '';
            partners.forEach(p => {
                const activeClass = activePartnerId == p.id ? 'active' : '';
                const unread = p.unread > 0 ? `<span class="unread-badge animate-pulse">${p.unread}</span>` : '';
                html += `
                    <div class="partner-item ${activeClass}" onclick="selectPartner(${p.id}, '${p.name}')" id="partner-${p.id}">
                        <div class="partner-avatar">${p.name.charAt(0)}</div>
                        <div class="flex-grow-1">
                            <div class="font-weight-bold">${p.name}</div>
                            <div class="small text-muted" id="last-msg-${p.id}">Click to chat</div>
                        </div>
                        ${unread}
                    </div>
                `;
            });
            $('#partnerList').html(html || '<div class="p-4 text-center text-muted">No members assigned</div>');
        }

        async function selectPartner(id, name) {
            if (activePartnerId === id) return;
            activePartnerId = id;
            
            $('#chatPlaceholder').addClass('d-none');
            $('#chatContent').removeClass('d-none').addClass('d-flex');
            $('#activeName').text(name);
            $('#activeAvatar').text(name.charAt(0));
            $('#messageContainer').empty(); // Clear old messages before loading new ones
            
            $('.partner-item').removeClass('active');
            $(`#partner-${id}`).addClass('active');

            try {
                // Get conversation history and ID
                const convData = await fetch(`../includes/chat_api.php?action=get_conversation&target_id=${id}`).then(r => r.json());
                conversationId = convData.conversation_id;
                
                // Join Socket Room
                socket.emit('join', { userId: myId, conversationId: conversationId });

                // Render History
                convData.history.forEach(m => addMessageToUI(m.message, m.sender_role === 'instructor' ? 'sent' : 'received', m.created_at));
                scrollToBottom();
            } catch (e) {
                console.error("Failed to load conversation", e);
            }
        }

        function addMessageToUI(msg, type, time = new Date()) {
            const timeStr = new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const html = `
                <div class="message ${type}">
                    ${msg}
                    <span class="message-time">${timeStr}</span>
                </div>
            `;
            $('#messageContainer').append(html);
            scrollToBottom();
        }

        function scrollToBottom() {
            const container = $('#messageContainer');
            container.scrollTop(container[0].scrollHeight);
        }

        // 2. Socket Events
        socket.on('receive_message', (data) => {
            // Only add if it's the active conversation
            addMessageToUI(data.message, 'received');
        });

        socket.on('display_typing', (data) => {
            if (data.isTyping) $('#typingIndicator').fadeIn();
            else $('#typingIndicator').fadeOut();
        });

        socket.on('user_status', (data) => {
            if (data.status === 'online') {
                $('#onlineStatus').removeClass('text-muted').addClass('text-success').html('<i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Online');
            }
        });

        // 3. Instructor Actions
        $('#chatForm').submit(async function(e) {
            e.preventDefault();
            const msg = $('#messageInput').val().trim();
            if (!msg || !conversationId) return;

            $('#messageInput').val('');
            socket.emit('typing', { conversationId, userId: myId, isTyping: false });

            // Add to UI immediately
            addMessageToUI(msg, 'sent');

            // Save to DB via PHP API
            const saveRes = await fetch('../includes/chat_api.php?action=save_message', {
                method: 'POST',
                body: JSON.stringify({ 
                    conversation_id: conversationId, 
                    target_id: activePartnerId,
                    message: msg 
                })
            }).then(r => r.json());

            if (saveRes.success && !conversationId) {
                conversationId = saveRes.conversation_id;
                // Join the room instantly
                socket.emit('join', { userId: myId, conversationId: conversationId });
            }

            // Broadcast via Socket
            socket.emit('send_message', {
                conversationId,
                senderId: myId,
                message: msg,
                role: 'instructor'
            });
        });

        // Typing logic
        let typingTimer;
        $('#messageInput').on('input', function() {
            socket.emit('typing', { conversationId, userId: myId, isTyping: true });
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                socket.emit('typing', { conversationId, userId: myId, isTyping: false });
            }, 2000);
        });

        // Initial Load
        loadPartners();

        // 4. Live Search Members
        $('#memberSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('.partner-item').filter(function() {
                $(this).toggle($(this).find('.font-weight-bold').text().toLowerCase().indexOf(value) > -1)
            });
        });
    </script>
</body>
</html>
