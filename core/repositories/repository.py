from typing import TypeVar, Generic, List, Optional, Type, Callable
from sqlalchemy import func as sql_func
from models.model import BaseModel
from sqlalchemy.exc import SQLAlchemyError
from flask import Flask
from flask_sqlalchemy import SQLAlchemy
from uuid import UUID

T = TypeVar("T", bound=BaseModel)  # Generic type for models

def execute_with_context(func: Callable) -> Callable:
    """
    Wraps a repository method to ensure it executes within the app context.
    """
    def wrapper(self, *args, **kwargs):
        with self.app.app_context():
            return func(self, *args, **kwargs)
    return wrapper

class BaseRepository(Generic[T]):
    def __init__(self, app: Flask, db: SQLAlchemy, model: Type[T]):
        self.db: SQLAlchemy = db
        self.app: Flask = app
        self.model: Type[T] = model

    @execute_with_context
    def get_all(self) -> List[T]:
        return self.db.session.query(self.model).all()

    @execute_with_context
    def get_by_id(self, _id: UUID) -> Optional[T]:
        return self.db.session.query(self.model).filter_by(id=str(_id)).first()

    @execute_with_context
    def find(self, **kwargs) -> Optional[T]:
        return self.db.session.query(self.model).filter_by(**kwargs).first()
    
    @execute_with_context
    def find_all(self, **kwargs) -> List[T]:
        return self.db.session.query(self.model).filter_by(**kwargs).all()
    
    @execute_with_context
    def all(self) -> List[T]:
        return self.db.session.query(self.model).all()
    
    @execute_with_context
    def add(self, entity: T) -> T:
        self.db.session.add(entity)
        self._commit()
        return entity

    @execute_with_context
    def update(self, entity: T) -> T:
        self.db.session.merge(entity)
        self._commit()
        return entity

    @execute_with_context
    def delete(self, entity: T) -> None:
        self.db.session.delete(entity)
        self._commit()

    @execute_with_context
    def _commit(self) -> None:
        try:
            self.db.session.commit()
        except SQLAlchemyError as e:
            self.db.session.rollback()
            raise e
        
    @execute_with_context
    def count(self) -> int:
        return self.db.session.query(sql_func.count(self.model.id)).scalar()

    @execute_with_context
    def print_db_path(self) -> None:
        engine = self.db.get_engine()
        print(f"Database path: {engine.url}")
