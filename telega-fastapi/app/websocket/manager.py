import json
import redis.asyncio as redis 
from fastapi import APIRouter, WebSocket
from app.core.config import settings

router = APIRouter()

class ConnectionManager:
    def __init__(self):
        self.active_connections: dict[int, list[WebSocket]] = {}
    
    async def connect(self, chat_id: int, websocket: WebSocket):
        await websocket.accept()
        if chat_id not in self.active_connections:
            self.active_connections[chat_id] = []
        self.active_connections[chat_id].append(WebSocket)
    
    def disconnect(self, chat_id: int, websocket: WebSocket):
        if chat_id in self.active_connections:
            self.active_connections[chat_id].remove(websocket)
    
    async def broadcast(self, chat_id: int, message: str):
        if chat_id in self.active_connections:
            for connection in self.active_connections[chat_id]:
                await connection.send_text(message)

manager = ConnectionManager()

async def redis_listener():
    r = redis.from_url(settings.REDIS_URL)
    pubsub = r.pubsub()
    await pubsub.subscribe("chat_channel")

    print("Подключение прошло успешно")
    async for message in pubsub.listen():
        if message['type'] == 'message':
            payload = json.loads(message['data'].decode('utf-8'))
            action = payload.get('action')
            chat_id = payload['data']['chat_id']
            message_data = payload['data']
            await manager.broadcast(chat_id=chat_id, message=message_data)           
