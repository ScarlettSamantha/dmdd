from datetime import datetime
from typing import Type, TypeVar
from sqlalchemy import String, DateTime
from sqlalchemy.orm import declared_attr, Mapped, mapped_column
from sqlalchemy.ext.declarative import declarative_base
from uuid import uuid4, UUID

# Type variable for model classes
T = TypeVar('T', bound='BaseModel')

base = declarative_base()
 
class BaseModel(base):
    """
    Base model class that other models will inherit from.
    Provides common columns and methods for migrations and utilities.
    """
    __abstract__ = True

    @declared_attr
    def id(cls) -> Mapped[str]:
        """
        Declare the `id` column dynamically, with a default UUID as string.
        """
        return mapped_column(
            String(36),
            primary_key=True,
            default=lambda: str(uuid4()),
            unique=True,
            nullable=False,
        )

    created_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, nullable=False)
    updated_at: Mapped[datetime] = mapped_column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow, nullable=False)

    def save(self, db) -> None:
        """
        Save the current instance to the database.

        :param db: The SQLAlchemy instance from the main application.
        """
        db.session.add(self)
        db.session.commit()

    def delete(self, db) -> None:
        """
        Delete the current instance from the database.

        :param db: The SQLAlchemy instance from the main application.
        """
        db.session.delete(self)
        db.session.commit()

    @classmethod
    def find_by_id(cls: Type[T], record_id: UUID | str, db) -> T | None:
        """
        Find a record by its ID.

        :param record_id: The ID of the record to fetch (as UUID or string).
        :param db: The SQLAlchemy instance from the main application.
        :return: The record instance or None if not found.
        """
        record_id = str(record_id) if isinstance(record_id, UUID) else record_id
        return db.session.query(cls).get(record_id)

    @classmethod
    def all(cls: Type[T], db) -> list[T]:
        """
        Fetch all records of this model.

        :param db: The SQLAlchemy instance from the main application.
        :return: List of all records.
        """
        return db.session.query(cls).all()

    @property
    def uuid(self) -> UUID:
        """
        Get the ID as a `UUID` object.

        :return: UUID object representation of the ID.
        """
        return UUID(self.id)

    @uuid.setter
    def uuid(self, value: UUID) -> None:
        """
        Set the ID using a `UUID` object.

        :param value: UUID object to set as the ID.
        """
        self.id = str(value)
