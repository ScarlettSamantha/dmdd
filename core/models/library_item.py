from sqlalchemy import String, Boolean, ForeignKey, Integer, Text
from sqlalchemy.orm import Mapped, mapped_column, relationship
from typing import TYPE_CHECKING
from .model import BaseModel

if TYPE_CHECKING:
    from .user import User
    from .library import Library

class LibraryItem(BaseModel):
    """
    User model with fields typically required for a backend application.
    """
    __tablename__ = 'libraryItems'
    serialize_head_only = ('id', 'name', 'description', 'is_public', 'owner_id', 'library_id')
    serialize_only = ('id', 'name', 'description', 'is_public', 'owner', 'library', 'mime_type', 'file_size', 'file_path', 'raw_data', 'created_at', 'updated_at', 'deleted_at')
    
    # one to many relationship with User, back_populates is used to define the relationship in the other model
    owner_id: Mapped[str] = mapped_column(ForeignKey("users.id"), nullable=True)
    owner: Mapped["User"] = relationship(back_populates="libraryItems")
    
    # one to many relationship with Library, back_populates is used to define the relationship in the other model
    library_id: Mapped[str] = mapped_column(ForeignKey("library.id"), nullable=True)
    library: Mapped["Library"] = relationship(back_populates="items")
    
    mime_type: Mapped[str] = mapped_column(String(150), nullable=False)
    file_size: Mapped[int] = mapped_column(Integer, nullable=False)
    file_path: Mapped[str] = mapped_column(String(255), nullable=False)
    is_public: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)
    
    name: Mapped[str] = mapped_column(String(150), unique=True, nullable=False)
    description: Mapped[str] = mapped_column(Text(255), nullable=True)
    
    raw_data: Mapped[str] = mapped_column(Text, nullable=True)

    def __repr__(self) -> str:
        return f"<LibraryItem(name={self.name}, description={self.description}, is_public={self.is_public}, library_id={self.library_id}, path={self.file_path})>"

    