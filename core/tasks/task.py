from abc import ABC, abstractmethod
from datetime import datetime, timedelta
import threading
import logging
from flask import Flask
from flask_sqlalchemy import SQLAlchemy

class Task(ABC):
    def __init__(self, name: str, run_interval: timedelta, app: Flask, logger: logging.Logger, db: SQLAlchemy, is_blocking: bool = False) -> None:
        self.name: str = name
        self.app: Flask = app
        self.logger: logging.Logger = logger.getChild(name)
        self.run_interval: timedelta = run_interval
        self.next_run: datetime = datetime.now() + self.run_interval
        self.is_blocking: bool = is_blocking
        self.db: SQLAlchemy = db

    def update_next_run(self) -> None:
        """Update the next run time for the task."""
        self.next_run += self.run_interval

    @abstractmethod
    async def tick(self) -> None:
        """Define the logic to be executed for this task."""
        pass
    
    def first_call(self) -> None:
        threading.current_thread().name = f"Task-{self.name}"

    @abstractmethod
    def health_check(self) -> str:
        """Return the health status of the task."""
        pass
    
    async def stop(self):
        """Optional logic to handle task finalization when the system stops."""
        pass