from fastapi import APIRouter, Depends, HTTPException
from app.schemas.message import MessageRead, MessageCreate, MessageUpdate
from app.services.message_service import MessageService
from app.api.deps import get_message_service  # Импортируем фабрику

router = APIRouter(prefix="/chats/{chat_id}/messages")

@router.get("/", response_model=list[MessageRead])
async def get_messages(chat_id: int, service: MessageService = Depends(get_message_service)):
    return await service.get_chat_messages(chat_id)

@router.post("/", response_model=MessageRead)
async def store_message(
        chat_id: int,
        msg: MessageCreate,
        service: MessageService = Depends(get_message_service)
):

    return await service.store(
        chat_id=chat_id,
        user_id=msg.user_id,
        user_name=msg.user_name,
        body=msg.body
    )

@router.put("/{message_id}", response_model=MessageRead)
async def update_message(
        message_id: int,
        msg: MessageUpdate,
        service: MessageService = Depends(get_message_service)
):
    return await service.update(message_id=message_id, body=msg.body, user_name=msg.user_name)


@router.delete("/{message_id}")
async def delete_message(
        message_id: int,
        service: MessageService = Depends(get_message_service)
):
    success = await service.destroy(message_id)
    if not success:
        raise HTTPException(status_code=404, detail="Message not found")
    return {"status": "ok"}