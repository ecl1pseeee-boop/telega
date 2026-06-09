// src/Hooks/useChatSocket.js
import { useEffect } from 'react';

export const useChatSocket = (chatId, onMessageReceived) => {
    useEffect(() => {
        if (!chatId) return;

        const ws = new WebSocket(`ws://127.0.0.1:8001/ws/${chatId}`);

        ws.onmessage = (event) => {
            const payload = JSON.parse(event.data);
            onMessageReceived(payload);
        };

        return () => ws.close();
    }, [chatId, onMessageReceived]);
};
