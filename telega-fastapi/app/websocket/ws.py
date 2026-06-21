import redis.asyncio as redis 
import json
from fastapi import APIRouter
from fastapi import WebSocket
from app.websocket.manager import manager
from app.core.config import settings

router = APIRouter()

@router.websocket("/ws/{chat_id}")
async def websocket_endpoint(websocket: WebSocket, chat_id: int):
    await manager.connect(chat_id, websocket)
    try:
        while True:
            data = await websocket.receive_text()
            await manager.broadcast(chat_id, data)
    except Exception as e:
        manager.disconnect(chat_id, websocket)
        print(f"WebSocket connection error: {e}")

async def redis_listener():
    while True:
        try:
            r = redis.from_url(
                settings.REDIS_URL, 
                decode_responses=True, 
                socket_timeout=None,
                socket_connect_timeout=10
            )
            pubsub = r.pubsub()
            await pubsub.subscribe("chat_channel")

            print("Подключение прошло успешно")
            async for message in pubsub.listen():
                if message['type'] == 'message':
                    payload = json.loads(message['data'])
                    action = payload.get('action')
                    chat_id = payload['data']['chat_id']
                    message_data = payload['data']
                    print(f"Получено сообщение из Redis: {message_data}")
                    full_data = {
                        "data": message_data,
                        "action": action
                    }
                    await manager.broadcast(chat_id=chat_id, message=full_data)           
        except Exception as e:
            print(f"Ошибка в слушателе: {e}")
        finally:
            await pubsub.unsubscribe("chat_channel")
            await r.close()