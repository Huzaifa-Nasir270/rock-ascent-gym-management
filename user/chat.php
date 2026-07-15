<?php
/**
 * User - Live Chat with Instructor
 */

require_once('../config/functions.php');
requireUser();

$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Live Chat - My Instructor</title>
    <?php include('../includes/head.php'); ?>
    <style>
        .chat-container {
            height: calc(100vh - 200px);
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
        }
        .chat-header {
            padding: 20px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(15, 23, 42, 0.2);
        }
        .partner-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            box-shadow: 0 5px 15px rgba(245, 158, 11, 0.3);
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
            background: rgba(15, 23, 42, 0.2);
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
            border-radius: 20px !important;
            padding: 15px 25px !important;
        }
        .send-btn {
            width: 55px;
            height: 55px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 !important;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <?php include('../includes/user_sidebar.php'); ?>

            <div class="main-content">
                <div id="noInstructor" class="card d-none fade-in-up">
                    <div class="card-body text-center p-5">
                        <i class="fas fa-user-slash fa-4x mb-4 text-muted" style="opacity: 0.3;"></i>
                        <h3>No Instructor Assigned</h3>
                        <p class="text-muted">You haven't been assigned an instructor yet. Please contact admin to get a personal trainer.</p>
                        <a href="dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>
                    </div>
                </div>

                <div id="chatBox" class="chat-container d-none fade-in">
                    <div class="chat-header">
                        <div class="partner-avatar" id="instructorAvatar">I</div>
                        <div>
                            <h5 class="mb-0 font-weight-bold" id="instructorName">Instructor Name</h5>
                            <span id="onlineStatus" class="small text-muted"><i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Offline</span>
                        </div>
                    </div>
                    <div class="chat-messages" id="messageContainer">
                        <!-- Messages will load here instantly -->
                    </div>
                    <div class="chat-input-area">
                        <div id="typingIndicator" class="small text-muted mb-2" style="display:none;">Instructor is typing...</div>
                        <form id="chatForm" class="chat-input-group">
                            <input type="text" id="messageInput" class="chat-input" placeholder="Type a message..." autocomplete="off">
                            <button type="submit" class="btn btn-primary send-btn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
    <!-- Load Socket.IO Client -->
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <script>
        const socket = io('http://localhost:3000'); // Ensure Node server is running on this port
        let conversationId = null;
        let instructorId = null;
        const myId = <?php echo $_SESSION['user_id']; ?>;
        const myRole = 'user';

        // 1. Initial Setup: Find the assigned instructor
        async function initChat() {
            try {
                const response = await fetch('../includes/chat_handler.php?action=get_partners&role=user');
                const partners = await response.json();
                
                if (partners.length > 0) {
                    instructorId = partners[0].id;
                    $('#instructorName').text(partners[0].name);
                    $('#instructorAvatar').text(partners[0].name.charAt(0));
                    $('#chatBox').removeClass('d-none');
                    
                    // Get conversation history and ID
                    const convData = await fetch(`../includes/chat_api.php?action=get_conversation&target_id=${instructorId}`).then(r => r.json());
                    conversationId = convData.conversation_id;
                    
                    // Render History
                    convData.history.forEach(m => addMessageToUI(m.message, m.sender_role === 'user' ? 'sent' : 'received', m.created_at));
                    scrollToBottom();

                    // Join Socket Room
                    socket.emit('join', { userId: myId, conversationId: conversationId });
                } else {
                    $('#noInstructor').removeClass('d-none');
                }
            } catch (e) {
                console.error("Chat init failed", e);
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

        // 3. User Actions
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
                    target_id: instructorId,
                    message: msg 
                })
            }).then(r => r.json());

            if (saveRes.success && !conversationId) {
                conversationId = saveRes.conversation_id;
                // Join the room instantly so socket messages work
                socket.emit('join', { userId: myId, conversationId: conversationId });
            }

            // Broadcast via Socket
            socket.emit('send_message', {
                conversationId,
                senderId: myId,
                message: msg,
                role: 'user'
            });
        });

        // Typing Indicator logic
        let typingTimer;
        $('#messageInput').on('input', function() {
            socket.emit('typing', { conversationId, userId: myId, isTyping: true });
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                socket.emit('typing', { conversationId, userId: myId, isTyping: false });
            }, 2000);
        });

        initChat();
    </script>
</body>
</html>
