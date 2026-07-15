<style>
/* Floating Chatbot Widget Styles */
.chatbot-widget {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 9999;
    font-family: 'Outfit', sans-serif;
}

.chatbot-button {
    width: 65px;
    height: 65px;
    background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 28px;
    cursor: pointer;
    box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 2px solid rgba(255, 255, 255, 0.2);
    animation: breathingRobot 3s ease-in-out infinite;
}

@keyframes breathingRobot {
    0%, 100% { transform: scale(1); box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4); }
    50% { transform: scale(1.08); box-shadow: 0 15px 45px rgba(6, 182, 212, 0.6); }
}

.chatbot-button:hover {
    transform: scale(1.1) rotate(15deg);
    box-shadow: 0 15px 40px rgba(59, 130, 246, 0.6);
    animation: none;
}

.chatbot-window {
    position: absolute;
    bottom: 85px;
    right: 0;
    width: 400px;
    height: 600px;
    background: rgba(15, 23, 42, 0.9);
    backdrop-filter: blur(40px) saturate(200%);
    -webkit-backdrop-filter: blur(40px) saturate(200%);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 32px;
    display: none;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 30px 100px rgba(0, 0, 0, 0.7);
}

.chatbot-window.active {
    display: flex;
    animation: chatbotFadeIn 0.4s ease-out;
}

@keyframes chatbotFadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.chatbot-header {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(6, 118, 212, 0.1));
    padding: 25px;
    border-bottom: 1px solid rgba(59, 130, 246, 0.2);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chatbot-header .ai-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chatbot-header .status-dot {
    width: 10px;
    height: 10px;
    background: #22c55e;
    border-radius: 50%;
    box-shadow: 0 0 10px #22c55e;
}

.chatbot-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.chatbot-messages::-webkit-scrollbar {
    width: 5px;
}

.chatbot-messages::-webkit-scrollbar-thumb {
    background: rgba(59, 130, 246, 0.2);
    border-radius: 10px;
}

.chatbot-input-area {
    padding: 20px;
    border-top: 1px solid rgba(59, 130, 246, 0.2);
    background: rgba(15, 23, 42, 0.8);
}

.chatbot-input-group {
    display: flex;
    gap: 10px;
}

.chatbot-input {
    flex: 1;
    background: rgba(30, 41, 59, 0.6);
    border: 1px solid rgba(59, 130, 246, 0.3);
    border-radius: 14px;
    color: white !important;
    padding: 12px 18px;
    outline: none;
    font-size: 14px;
    transition: all 0.3s;
}

.chatbot-input:focus {
    border-color: #3b82f6;
    background: rgba(30, 41, 59, 0.8);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.chatbot-send-btn {
    background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
    border: none;
    width: 45px;
    height: 45px;
    border-radius: 12px;
    color: white;
    cursor: pointer;
    transition: 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chatbot-send-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
}

.chatbot-typing {
    font-size: 11px;
    color: #94a3b8;
    margin-bottom: 8px;
    display: none;
}

.message {
    max-width: 85%;
    padding: 12px 18px;
    border-radius: 18px;
    font-size: 13.5px;
    line-height: 1.5;
    animation: messageFadeIn 0.3s ease;
}

@keyframes messageFadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.message.ai {
    align-self: flex-start;
    background: rgba(30, 41, 59, 0.8);
    border: 1px solid rgba(59, 130, 246, 0.1);
    color: #e2e8f0;
    border-bottom-left-radius: 4px;
}

.message.user {
    align-self: flex-end;
    background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
    color: white;
    border-bottom-right-radius: 4px;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
}

@media (max-width: 576px) {
    .chatbot-window {
        width: calc(100vw - 40px);
        right: -10px;
        height: 70vh;
    }
}
</style>
<div class="chatbot-widget">
    <div class="chatbot-button" id="chatbotToggle">
        <i class="fas fa-robot"></i>
    </div>
    
    <div class="chatbot-window" id="chatbotWindow">
        <div class="chatbot-header">
            <div class="ai-info">
                <div class="status-dot"></div>
                <div>
                    <h6 class="mb-0 font-weight-bold" style="color: white">RockBot AI</h6>
                    <small class="text-muted">Fitness Assistant</small>
                </div>
            </div>
            <button class="btn btn-sm text-white" id="closeChatbot"><i class="fas fa-times"></i></button>
        </div>
        
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="message ai">
                <?php 
                $role = $_SESSION['role'] ?? 'member';
                if ($role === 'admin') echo "Hello Admin! I'm your system assistant. How can I help you manage the gym today?";
                elseif ($role === 'instructor') echo "Greetings Coach! Ready to optimize your members' performance? Ask me anything.";
                else echo "Hello! I am RockBot. How can I help you with your fitness journey today?";
                ?>
            </div>
        </div>
        
        <div class="chatbot-input-area">
            <div class="chatbot-typing" id="chatbotTyping">RockBot is thinking...</div>
            <div class="chatbot-input-group">
                <input type="text" id="chatbotInput" class="chatbot-input" placeholder="Ask anything..." autocomplete="off">
                <button class="chatbot-send-btn" id="chatbotSend">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('chatbotToggle');
    const window = document.getElementById('chatbotWindow');
    const close = document.getElementById('closeChatbot');
    const input = document.getElementById('chatbotInput');
    const send = document.getElementById('chatbotSend');
    const messages = document.getElementById('chatbotMessages');
    const typing = document.getElementById('chatbotTyping');

    toggle.addEventListener('click', () => {
        window.classList.toggle('active');
        if(window.classList.contains('active')) input.focus();
    });

    close.addEventListener('click', () => {
        window.classList.remove('active');
    });

    function addChatMsg(text, sender) {
        const div = document.createElement('div');
        div.className = `message ${sender}`;
        div.innerText = text;
        messages.appendChild(div);
        messages.scrollTop = messages.scrollHeight;
    }

    async function handleChatSend() {
        const text = input.value.trim();
        if (!text) return;

        addChatMsg(text, 'user');
        input.value = '';
        typing.style.display = 'block';

        try {
            const baseUrl = '<?php echo (strpos($_SERVER['REQUEST_URI'], "/user/") !== false || strpos($_SERVER['REQUEST_URI'], "/admin/") !== false || strpos($_SERVER['REQUEST_URI'], "/instructor/") !== false) ? "../" : ""; ?>';
            
            const response = await fetch(baseUrl + 'config/ai_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            const data = await response.json();
            
            typing.style.display = 'none';
            if (data.reply) {
                addChatMsg(data.reply, 'ai');
            } else {
                addChatMsg("I'm sorry, I couldn't process that. Please try again.", 'ai');
            }
        } catch (e) {
            typing.style.display = 'none';
            addChatMsg("Connection error. Please check your internet.", 'ai');
        }
    }

    send.addEventListener('click', handleChatSend);
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') handleChatSend();
    });
});
</script>
