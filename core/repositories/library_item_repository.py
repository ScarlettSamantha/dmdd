from models.library_item import LibraryItem
from typing import Optional
from flask_sqlalchemy import SQLAlchemy
from flask import Flask

from .repository import BaseRepository, execute_with_context

class LibraryItemRepository(BaseRepository[LibraryItem]):
    def __init__(self, db: SQLAlchemy, app: Flask):
        super().__init__(db=db, model=LibraryItem, app=app)

  