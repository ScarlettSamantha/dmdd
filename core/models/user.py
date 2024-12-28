from datetime import datetime
from sqlalchemy import String, DateTime, Boolean
from sqlalchemy.orm import Mapped, mapped_column, relationship
import uuid
import hashlib
import os
from typing import TYPE_CHECKING, Optional, List, Tuple

from .model import BaseModel

# this is used to avoid circular imports but still have type hints
if TYPE_CHECKING:
    from .library import Library
    from .library_item import LibraryItem
    from .thumbnail import Thumbnail

class User(BaseModel):
    __tablename__ = 'users'
    __enable_seeding__ = True
    
    __default_hash_algorithm__ = 'sha3_512'
    __hash_algorithm__ = 'sha3_512'

    serialize_head_only = ('id', 'username', 'email', 'is_active', 'is_admin')
    serialize_only = ('id', 'username', 'email', 'first_name', 'last_name', 'is_active', 'is_admin', 
                      'last_login', 'last_ip', 'user_agent', 'avatar')

    username: Mapped[str] = mapped_column(String(150), unique=True, nullable=False)
    email: Mapped[str] = mapped_column(String(255), unique=True, nullable=False)
    password_hash: Mapped[str] = mapped_column(String(255), nullable=False)
    password_salt: Mapped[str] = mapped_column(String(255), nullable=False)
    first_name: Mapped[Optional[str]] = mapped_column(String(100), nullable=True)
    last_name: Mapped[Optional[str]] = mapped_column(String(100), nullable=True)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)
    is_confirmed: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)
    is_admin: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)
    last_login: Mapped[Optional[datetime]] = mapped_column(DateTime, nullable=True)
    last_ip: Mapped[Optional[str]] = mapped_column(String(45), nullable=True)
    user_agent: Mapped[Optional[str]] = mapped_column(String(255), nullable=True)
    api_key: Mapped[str] = mapped_column(String(36), unique=True, nullable=False, default=lambda: str(uuid.uuid4()))
    avatar: Mapped[Optional[str]] = mapped_column(String(255), nullable=True)

    # Relationships using forward references
    libraries: Mapped[List['Library']] = relationship("Library", back_populates="owner", lazy="joined")
    libraryItems: Mapped[List['LibraryItem']] = relationship("LibraryItem", back_populates="owner", lazy="joined")
    libraryItemsThumbnails: Mapped[List['Thumbnail']] = relationship("Thumbnail", back_populates="owner", lazy="joined")

    def __repr__(self) -> str:
        return f"<User(username={self.username}, email={self.email}, is_active={self.is_active}, is_admin={self.is_admin})>"

    def set_password(self, password: str) -> None:
        """
        Set the user's password hash with a user-specific salt.
        """
        self.password_salt: str= self._generate_salt()
        self.password_hash: str = self._hash_password(password, self.password_salt)

    def check_password(self, password: str) -> bool:
        """
        Check if the provided password matches the stored hash.
        """
        return self._verify_password(password, self.password_hash, self.password_salt)
    
    def generate_api_key(self) -> None:
        """
        Generate a new API key for the user.
        """
        self.api_key = self._generate_api_key()
        
    def generate_password(self, password: Optional[str] = None) -> None:
        """
        Generate a new password for the user.
        """
        if password is None:
            password: str = self._random_password()
        self.password_hash, self.password_salt = self._generate_password(password=password)

    @staticmethod
    def _generate_salt() -> str:
        """
        Generate a user-specific salt.
        """
        return os.urandom(16).hex()

    @staticmethod
    def _hash_password(password: str, salt: str) -> str:
        """
        Hash the given password using SHA-3 with a user-specific salt.
        """
        salted_password: str = f"{salt}{password}".encode()
        if not hasattr(hashlib, User.__hash_algorithm__):
            User.__hash_algorithm__ = User.__default_hash_algorithm__
        hash_func = getattr(hashlib, User.__hash_algorithm__, User.__default_hash_algorithm__)
        hash_str: str = hash_func(salted_password).hexdigest()
        return hash_str

    @staticmethod
    def _verify_password(password: str, hashed_password: str, salt: str) -> bool:
        """
        Verify the given password against the stored hash using SHA-3.
        """
        return hashlib.sha3_512(f"{salt}{password}".encode()).hexdigest() == hashed_password

    @staticmethod
    def _generate_api_key() -> str:
        """
        Generate a new API key.
        """
        return str(uuid.uuid4())
    
    @classmethod
    def _generate_password(cls, password: str) -> Tuple[str, str]:
        """
        Generate a new password.
        """
        _salt: str = cls._generate_salt()
        _hash: str = cls._hash_password(password, _salt)
        return _hash, _salt
    
    @classmethod
    def _random_password(cls) -> str:
        """
        Generate a random password.
        """
        return os.urandom(16).hex()

    @classmethod
    def seed(cls) -> Optional[List['User']]:
        """
        Seed the database with some initial users.
        """
        password, salt = cls._generate_password("password")
        
        return [
            cls(username="admin_user", email="test2@dmdd.eu", first_name="Admin", last_name="User", is_active=True, is_admin=True, password_hash=password, password_salt=salt),
            cls(username="user", email="test3@dmdd.eu", first_name="Regular", last_name="User", is_active=True, is_admin=False, password_hash=password, password_salt=salt),
            cls(username="disabled", email="test4@dmdd.eu", first_name="Disabled", last_name="User", is_active=False, is_admin=False, password_hash=password, password_salt=salt),
            cls(username="unconfirmed", email="test5@dmdd.eu", first_name="Unconfirmed", last_name="User", is_confirmed=False, is_admin=False, password_hash=password, password_salt=salt),
            cls(username="api", email="test6@dmdd.eu", first_name="API", last_name="User", is_active=True, is_admin=False, api_key=cls._generate_api_key(), password_hash=password, password_salt=salt),
        ]
