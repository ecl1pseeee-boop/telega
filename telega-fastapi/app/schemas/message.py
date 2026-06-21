from datetime import datetime
from typing import Optional
from sqlalchemy import Text
from sqlalchemy.orm import DeclarativeBase, Mapped, mapped_column
from pydantic import BaseModel, ConfigDict, Field


class Base(DeclarativeBase):
    pass

class Message(Base):
    __tablename__ = "messages"

    id: Mapped[int] = mapped_column(primary_key=True, index=True)
    chat_id: Mapped[int] = mapped_column(index=True)
    user_id: Mapped[int] = mapped_column(index=True)
    user_name: Optional[str] = None
    body: Mapped[str] = mapped_column(Text)
    created_at: Mapped[datetime] = mapped_column(default=datetime.utcnow)
    updated_at: Mapped[datetime] = mapped_column(default=datetime.utcnow, onupdate=datetime.utcnow)

# Схема автора
class UserSchema(BaseModel):
    id: int
    name: str
    model_config = ConfigDict(from_attributes=True)

# Базовая схема сообщения
class MessageBase(BaseModel):
    body: str
    user_id: int
    user_name: str

# Схема для создания
class MessageCreate(MessageBase):
    chat_id: Optional[int] = None

# Схема для чтения
class MessageRead(MessageBase):
    id: int
    chat_id: int
    created_at: datetime
    updated_at: datetime
    user: Optional[UserSchema] = None

    model_config = ConfigDict(from_attributes=True)

class MessageUpdate(BaseModel):
    body: str = Field(..., min_length=1, max_length=5000)