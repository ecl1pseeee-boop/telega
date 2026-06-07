import asyncio
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.api import system
from contextlib import asynccontextmanager
from app.websocket.manager import redis_listener

@asynccontextmanager
async def lifespan(app: FastAPI):
    task = asyncio.create_task(redis_listener())
    yield
    task.cancel()

app = FastAPI(lifespan=lifespan)

app.add_middleware(
     CORSMiddleware,
     allow_origin_regex=r"https?://.*",
     allow_credentials=True,
     allow_methods=["*"],
     allow_headers=["*"],
)

app.include_router(system.router)
