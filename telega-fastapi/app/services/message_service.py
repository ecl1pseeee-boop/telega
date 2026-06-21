import json
from typing import Optional
import redis.asyncio as redis
from sqlalchemy import select, update, delete
from sqlalchemy.ext.asyncio import AsyncSession
from app.core.config import settings
from app.schemas.message import Message


class MessageService:
    def __init__(self, db: AsyncSession):
        self.db = db
        self.redis_client = redis.from_url(
            settings.REDIS_URL,
            decode_responses=True,
            socket_timeout=None,
            socket_connect_timeout=10
        )

    async def _publish(self, action: str, message: Message, user_name: str):
        payload = {
            "action": action,
            "data": {
                "id": message.id,
                "chat_id": message.chat_id,
                "user_id": message.user_id,
                "body": message.body,
                "author": user_name,
                "created_at": str(message.created_at),
                "updated_at": str(message.updated_at),
            }
        }
        await self.redis_client.publish("chat_channel", json.dumps(payload))

    async def store(self, chat_id: int, user_id: int, user_name: str, body: str) -> Message:
        new_message = Message(chat_id=chat_id, user_id=user_id, body=body)
        self.db.add(new_message)
        await self.db.commit()
        await self.db.refresh(new_message)

        await self._publish("message.created", new_message, user_name)
        return new_message

    async def update(self, message_id: int, body: str, user_name: str) -> Optional[Message]:
        query = select(Message).where(Message.id == message_id)
        result = await self.db.execute(query)
        message = result.scalar_one_or_none()

        if message:
            message.body = body
            await self.db.commit()
            await self.db.refresh(message)
            await self._publish("message.updated", message, user_name)

        return message

    async def destroy(self, message_id: int) -> bool:
        query = select(Message).where(Message.id == message_id)
        result = await self.db.execute(query)
        message = result.scalar_one_or_none()

        if message:
            payload = {"action": "message.deleted", "data": {"id": message.id, "chat_id": message.chat_id}}
            await self.db.delete(message)
            await self.db.commit()
            await self.redis_client.publish("chat_channel", json.dumps(payload))
            return True
        return False

    async def get_chat_messages(self, chat_id: int):
        query = select(Message).where(Message.chat_id == chat_id).order_by(Message.created_at.asc())
        result = await self.db.execute(query)
        return result.scalars().all()