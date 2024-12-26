from datetime import datetime
from sqlalchemy import Column, String, DateTime, Boolean
from sqlalchemy.orm import Mapped, mapped_column
import uuid
import hashlib
import os

from .model import BaseModel

class User(BaseModel):
    """
    User model with fields typically required for a backend application.
    """
    __tablename__ = 'users'
    serialize_head_only = ('id', 'username', 'email', 'is_active', 'is_admin')
    serialize_only = ('id', 'username', 'email', 'first_name', 'last_name', 'is_active', 'is_admin', 'last_login', 'last_ip', 'user_agent', 'avatar')

    username: Mapped[str] = mapped_column(String(150), unique=True, nullable=False)
    email: Mapped[str] = mapped_column(String(255), unique=True, nullable=False)
    password_hash: Mapped[str] = mapped_column(String(255), nullable=False)
    password_salt: Mapped[str] = mapped_column(String(255), nullable=False)
    first_name: Mapped[str] = mapped_column(String(100), nullable=True)
    last_name: Mapped[str] = mapped_column(String(100), nullable=True)
    is_active: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)
    is_confirmed: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)
    is_admin: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)
    last_login: Mapped[datetime | None] = mapped_column(DateTime, nullable=True)
    last_ip: Mapped[str | None] = mapped_column(String(45), nullable=True)
    user_agent: Mapped[str | None] = mapped_column(String(255), nullable=True)
    api_key: Mapped[str] = mapped_column(String(36), unique=True, nullable=False, default=lambda: str(uuid.uuid4()))
    avatar: Mapped[str | None] = mapped_column(String(255), nullable=True)

    def __repr__(self) -> str:
        return f"<User(username={self.username}, email={self.email}, is_active={self.is_active}, is_admin={self.is_admin})>"

    def set_password(self, password: str) -> None:
        """
        Set the user's password hash with a user-specific salt.

        :param password: The plain-text password to hash and store.
        """
        self.password_salt = self._generate_salt()
        self.password_hash = self._hash_password(password, self.password_salt)

    def check_password(self, password: str) -> bool:
        """
        Check if the provided password matches the stored hash.

        :param password: The plain-text password to verify.
        :return: True if the password matches, False otherwise.
        """
        return self._verify_password(password, self.password_hash, self.password_salt)
    
    def generate_api_key(self) -> None:
        """
        Generate a new API key for the user.
        """
        self.api_key = self._generate_api_key()

    @staticmethod
    def _generate_salt() -> str:
        """
        Generate a user-specific salt.

        :return: A new random salt.
        """
        return os.urandom(16).hex()

    @staticmethod
    def _hash_password(password: str, salt: str) -> str:
        """
        Hash the given password using SHA-3 (quantum-resistant) with a user-specific salt.

        :param password: The plain-text password to hash.
        :param salt: The user-specific salt to use for hashing.
        :return: The hashed password.
        """
        salted_password = f"{salt}{password}".encode()
        return hashlib.sha3_512(salted_password).hexdigest()

    @staticmethod
    def _verify_password(password: str, hashed_password: str, salt: str) -> bool:
        """
        Verify the given password against the stored hash using SHA-3.

        :param password: The plain-text password to check.
        :param hashed_password: The stored hash to verify against.
        :param salt: The user-specific salt used for hashing.
        :return: True if the password matches, False otherwise.
        """
        return hashlib.sha3_512(f"{salt}{password}".encode()).hexdigest() == hashed_password
    
    @staticmethod
    def _generate_api_key() -> str:
        """
        Generate a new API key.

        :return: A new random
        """
        return str(uuid.uuid4())
    
    
    
