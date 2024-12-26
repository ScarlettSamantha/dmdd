from ..task import Task
import threading
import asyncio
import logging
from datetime import timedelta
from repositories.user_repository import UserRepository
from flask_sqlalchemy import SQLAlchemy
from flask import Flask
        
class ExampleTask(Task):
    
    def __init__(self, name: str, run_interval: timedelta, logger: logging.Logger, app: Flask, db: SQLAlchemy, is_blocking: bool = False) -> None:
        super().__init__(name=name, run_interval=run_interval, logger=logger, app=app, db=db, is_blocking=is_blocking)
    
    async def tick(self) -> None:
        user_repo = UserRepository(self.db, self.app)
        self.logger.info(f"User count: {user_repo.count()}")
        
        threading.current_thread().name = f"Task-{self.name}"
        await asyncio.sleep(0.1)
        
        self.logger.info("Executing example task.")

    def health_check(self) -> str:
        return "ExampleTask is healthy."