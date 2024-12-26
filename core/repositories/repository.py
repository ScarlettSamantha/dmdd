from typing import TypeVar, Generic, List, Optional, Type, Callable
from sqlalchemy import func as sql_func
from sqlalchemy.ext.declarative import DeclarativeMeta
from sqlalchemy.exc import SQLAlchemyError
from flask import Flask
from flask_sqlalchemy import SQLAlchemy

T = TypeVar("T", bound=DeclarativeMeta)  # Generic type for models

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
    def get_by_id(self, id: int) -> Optional[T]:
        return self.db.session.query(self.model).filter_by(id=id).first()

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
