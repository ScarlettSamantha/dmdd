from models.thumbnail import Thumbnail
from typing import Optional
from flask_sqlalchemy import SQLAlchemy
from flask import Flask

from .repository import BaseRepository, execute_with_context

class ThumbnailRepository(BaseRepository[Thumbnail]):
    def __init__(self, db: SQLAlchemy, app: Flask):
        super().__init__(db=db, model=Thumbnail, app=app)

  