from models.library import Library
from typing import Optional
from flask_sqlalchemy import SQLAlchemy
from flask import Flask

from .repository import BaseRepository, execute_with_context

class LibraryRepository(BaseRepository[Library]):
    def __init__(self, db: SQLAlchemy, app: Flask):
        super().__init__(db=db, model=Library, app=app)

  