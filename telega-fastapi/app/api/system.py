from datetime import datetime
from fastapi import APIRouter
from sqlalchemy import text
from fastapi import Depends

from app.db.session import get_db

router = APIRouter(tags=["system"])

@router.get("/health")
def health():
    return {"status": "ok"}

@router.get('/status')
async def status():
    return {"status": "ok", "time": str(datetime.now())}

@router.get("/db-check")
async def check_db(db = Depends(get_db)):
    try:
        await db.execute(text("SELECT 1"))
        return {"status": "connected"}
    except Exception as e:
        return {"status": "error", "detail": str(e)}