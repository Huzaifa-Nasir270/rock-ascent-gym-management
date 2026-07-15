<?php
require_once('../config/functions.php');
requireInstructor();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Instructor AI Coach - Project Rock Ascent</title>
    <?php include('../includes/head.php'); ?>
    <style>
        .chat-container {
            height: 600px;
            display: flex;
            flex-direction: column;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0,0,0,0.5);
        }
        .chat-header {
            padding: 25px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(6, 182, 212, 0.1));
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            display: flex;
            align-items: center;
        }
        .ai-status-indicator {
            width: 12px;
            height: 12px;
            background: #22c55e;
            border-radius: 50%;
            margin-right: 15px;
            box-shadow: 0 0 15px #22c55e;
        }
        .chat-messages {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .chat-messages::-webkit-scrollbar { width: 6px; }
        .chat-messages::-webkit-scrollbar-thumb { background: rgba(59, 130, 246, 0.2); border-radius: 10px; }

        .message {
            max-width: 80%;
            padding: 15px 22px;
            border-radius: 20px;
            font-size: 15px;
            line-height: 1.6;
            animation: messageSlideIn 0.4s ease-out;
        }
        @keyframes messageSlideIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message.ai {
            align-self: flex-start;
            background: rgba(30, 41, 59, 0.8);
            color: #f1f5f9;
            border-bottom-left-radius: 4px;
            border: 1px solid rgba(59, 130, 246, 0.1);
        }
        .message.user {
            align-self: flex-end;
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            color: white;
            border-bottom-right-radius: 4px;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }
        .chat-input-area {
            padding: 25px;
            background: rgba(15, 23, 42, 0.9);
            border-top: 1px solid rgba(59, 130, 246, 0.2);
        }
        .chat-input-group {
            display: flex;
            gap: 15px;
        }
        .chat-input {
            flex: 1;
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 16px;
            color: white;
            padding: 15px 20px;
            outline: none;
            transition: all 0.3s;
        }
        .chat-input:focus {
            border-color: #3b82f6;
            background: rgba(30, 41, 59, 0.8);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        .send-btn {
            background: linear-gradient(135deg, #3b82f6, #06b6d4);
            border: none;
            border-radius: 16px;
            color: white;
            width: 55px;
            height: 55px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .send-btn:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }
        .typing {
            font-style: italic;
            color: #3b82f6;
            font-size: 13px;
            margin-bottom: 10px;
            display: none;
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container-fluid">
        <div class="dashboard-container">
            <!-- Instructor Sidebar -->
            <?php include('../includes/instructor_sidebar.php'); ?>

            <div class="main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0"><i class="fas fa-robot mr-2" style="color:#3b82f6"></i>AI Coach Advisor</h2>
                    <span class="badge badge-primary px-3 py-2" style="background: rgba(59, 130, 246, 0.2); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3);">Science Mode</span>
                </div>

                <div class="chat-container">
                    <div class="chat-header">
                        <div class="ai-status-indicator"></div>
                        <div>
                            <h6 class="mb-0 font-weight-bold" style="color: white">Master Advisor AI</h6>
                            <small class="text-muted">Physiology & Training Science Specialist</small>
                        </div>
                    </div>
                    
                    <div class="chat-messages" id="chatMessages">
                        <div class="message ai">
                            Welcome, Instructor. I am your Master Advisor AI. I'm here to provide advanced physiological insights, training methodologies, and nutritional science data. How can I assist your coaching today?
                        </div>
                    </div>

                    <div class="chat-input-area">
                        <div class="typing" id="typingIndicator">Advisor is analyzing research...</div>
                        <div class="chat-input-group">
                            <input type="text" id="userInput" class="chat-input" placeholder="Ask a professional coaching question..." autocomplete="off">
                            <button class="send-btn" id="sendBtn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>

    <?php include('../includes/scripts.php'); ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatMessages = document.getElementById('chatMessages');
        const userInput = document.getElementById('userInput');
        const sendBtn = document.getElementById('sendBtn');
        const typingIndicator = document.getElementById('typingIndicator');

        function addMessage(text, sender) {
            const msgDiv = document.createElement('div');
            msgDiv.className = `message ${sender}`;
            msgDiv.innerText = text;
            chatMessages.appendChild(msgDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        async function handleSend() {
            const text = userInput.value.trim();
            if (!text) return;

            addMessage(text, 'user');
            userInput.value = '';
            typingIndicator.style.display = 'block';

            try {
                const response = await fetch('ai_coach_handler.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text })
                });
                const data = await response.json();
                
                typingIndicator.style.display = 'none';
                if (data.reply) {
                    addMessage(data.reply, 'ai');
                } else {
                    addMessage("System Error: Advisor core unreachable.", 'ai');
                }
            } catch (error) {
                typingIndicator.style.display = 'none';
                addMessage("Error connecting to Advisor Core.", 'ai');
            }
        }

        sendBtn.addEventListener('click', handleSend);
        userInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') handleSend();
        });
    });
    </script>
</body>
</html>
