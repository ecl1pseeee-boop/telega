from fastapi import APIRouter
from app.websocket.manager import ConnectionManager
from fastapi import WebSocket

router = APIRouter()
manager = ConnectionManager()

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