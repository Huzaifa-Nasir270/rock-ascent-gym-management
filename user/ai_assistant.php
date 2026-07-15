<?php
require_once('../config/functions.php');
requireUser();

$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'],'qty')) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>AI Health Assistant - Project Rock Ascent</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .chat-container {
            height: 600px;
            display: flex;
            flex-direction: column;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(245, 158, 11, 0.1);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        .chat-header {
            padding: 20px;
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(236, 72, 153, 0.1));
            border-bottom: 1px solid rgba(245, 158, 11, 0.2);
            display: flex;
            align-items: center;
        }
        .ai-status {
            width: 10px;
            height: 10px;
            background: #22c55e;
            border-radius: 50%;
            margin-right: 10px;
            box-shadow: 0 0 10px #22c55e;
        }
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .message {
            max-width: 80%;
            padding: 12px 18px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.5;
            animation: fadeIn 0.3s ease;
        }
        .message.ai {
            align-self: flex-start;
            background: rgba(30, 41, 59, 0.8);
            color: #e2e8f0;
            border-bottom-left-radius: 4px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .message.user {
            align-self: flex-end;
            background: linear-gradient(135deg, #f59e0b, #ec4899);
            color: white;
            border-bottom-right-radius: 4px;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
        }
        .chat-input-area {
            padding: 20px;
            background: rgba(15, 23, 42, 0.8);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        .chat-input-group {
            display: flex;
            gap: 10px;
        }
        .chat-input {
            flex: 1;
            background: rgba(30, 41, 59, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            padding: 12px 15px;
            outline: none;
            transition: border-color 0.3s;
        }
        .chat-input:focus {
            border-color: #f59e0b;
        }
        .send-btn {
            background: linear-gradient(135deg, #f59e0b, #ec4899);
            border: none;
            border-radius: 12px;
            color: white;
            width: 48px;
            height: 48px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .send-btn:hover {
            transform: scale(1.05);
        }
        .typing {
            font-style: italic;
            color: #94a3b8;
            font-size: 12px;
            margin-bottom: 5px;
            display: none;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<?php include('../includes/header.php'); ?>
<div class="container-fluid px-4"><div class="dashboard-container">
        <!-- Unified Sidebar -->
        <?php include('../includes/user_sidebar.php'); ?>

    <div class="main-content">
        <!-- Premium Page Header -->
        <div class="welcome-section mb-5 fade-in-up">
            <h1 class="mb-2">🤖 AI <span style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Health Assistant</span></h1>
            <p class="text-muted" style="font-size: 1.1rem; font-weight: 500;">Get personalized, data-backed fitness and nutrition advice instantly.</p>
        </div>

        <div class="chat-container">
            <div class="chat-header">
                <div class="ai-status"></div>
                <div>
                    <h6 class="mb-0 font-weight-bold">RockBot AI</h6>
                    <small class="text-muted">Health & Fitness Specialist</small>
                </div>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <div class="message ai">
                    Hello! I've analyzed your current fitness profile. I'm ready to provide detailed, scientific advice on your workouts, nutrition, or how to use our shop products to reach your goals faster. How can I assist you today?
                </div>
            </div>

            <div class="chat-input-area">
                <div class="typing" id="typingIndicator">RockBot is thinking...</div>
                <div class="chat-input-group">
                    <input type="text" id="userInput" class="chat-input" placeholder="Ask about supplements, workouts, or your progress..." autocomplete="off">
                    <button class="send-btn" id="sendBtn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div></div>

<?php include('../includes/footer.php'); ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessages');
    const userInput = document.getElementById('userInput');
    const sendBtn = document.getElementById('sendBtn');
    const typingIndicator = document.getElementById('typingIndicator');

    function addMessage(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `message ${sender}`;
        msgDiv.innerHTML = text; // Changed to innerHTML to support rich formatting
        chatMessages.appendChild(msgDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    async function handleSend() {
        const text = userInput.value.trim();
        if (!text) return;

        addMessage(text, 'user');
        userInput.value = '';
        
        // Show typing indicator
        typingIndicator.style.display = 'block';

        try {
            const response = await fetch('ai_chat_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            const data = await response.json();
            
            typingIndicator.style.display = 'none';
            if (data.reply) {
                addMessage(data.reply, 'ai');
            } else {
                addMessage("I'm sorry, I'm having trouble connecting right now. Please try again later.", 'ai');
            }
        } catch (error) {
            typingIndicator.style.display = 'none';
            addMessage("Error: Could not connect to AI services.", 'ai');
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
