const http = require('http');
const { Server } = require('socket.io');

const httpServer = http.createServer();
const io = new Server(httpServer, {
    cors: {
        origin: "*", // In production, restrict this to your domain
        methods: ["GET", "POST"]
    }
});

const activeUsers = new Map(); // Store userId -> socketId

io.on('connection', (socket) => {
    console.log('A user connected:', socket.id);

    // 1. User Joins (Identify user and their role)
    socket.on('join', (data) => {
        const roomName = `room_${data.conversationId}`;
        socket.join(roomName);
        activeUsers.set(data.userId, socket.id);
        console.log(`User ${data.userId} joined Room: ${roomName}`);
        
        // Notify others that user is online
        socket.to(roomName).emit('user_status', { userId: data.userId, status: 'online' });
    });

    // 2. Real-Time Message Broadcasting
    socket.on('send_message', (data) => {
        const roomName = `room_${data.conversationId}`;
        // Broadcast to everyone in the room EXCEPT the sender
        socket.to(roomName).emit('receive_message', {
            senderId: data.senderId,
            message: data.message,
            timestamp: new Date().toISOString(),
            role: data.role
        });
    });

    // 3. Typing Indicator
    socket.on('typing', (data) => {
        const roomName = `room_${data.conversationId}`;
        socket.to(roomName).emit('display_typing', {
            userId: data.userId,
            isTyping: data.isTyping
        });
    });

    // 4. Read Receipts
    socket.on('mark_seen', (data) => {
        const roomName = `room_${data.conversationId}`;
        socket.to(roomName).emit('message_seen', {
            conversationId: data.conversationId
        });
    });

    socket.on('disconnect', () => {
        console.log('User disconnected');
    });
});

const PORT = 3000;
httpServer.listen(PORT, () => {
    console.log(`🚀 WebSocket Server running on port ${PORT}`);
});
