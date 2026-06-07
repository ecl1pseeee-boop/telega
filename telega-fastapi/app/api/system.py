from datetime import datetime
from fastapi import APIRouter

router = APIRouter(tags=["system"])

@router.get("/health")
def health():
    return {"status": "ok"}

@router.get('/status')
async def status():
    return {"status": "ok", "time": str(datetime.now())}
