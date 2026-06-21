import asyncio
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from app.api import system
from contextlib import asynccontextmanager
from app.websocket import ws


@asynccontextmanager
async def lifespan(app: FastAPI):
    task = asyncio.create_task(ws.redis_listener())
    yield
    task.cancel()
    try:
        await task
    except asyncio.CancelledError:
        pass

app = FastAPI(
    title="Chat API",
    servers=[
        {"url": "https://telega.local/api", "description": "Production server"}
    ],
    lifespan=lifespan,
)

app.add_middleware(
     CORSMiddleware,
     allow_origin_regex=r"https?://.*",
     allow_credentials=True,
     allow_methods=["*"],
     allow_headers=["*"],
)

app.include_router(system.router)
app.include_router(ws.router)
