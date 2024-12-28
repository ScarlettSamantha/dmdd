from sqlalchemy import String, Boolean, ForeignKey
from sqlalchemy.orm import Mapped, mapped_column, relationship
from typing import ForwardRef, TYPE_CHECKING
from .model import BaseModel

if TYPE_CHECKING:
    from .user import User

class Library(BaseModel):
    """
    User model with fields typically required for a backend application.
    """
    __tablename__ = 'library'
    serialize_head_only = ('id', 'name', 'description', 'is_public', 'owner_id')
    serialize_only = ('id', 'name', 'description', 'is_public', 'owner', 'created_at', 'updated_at', 'deleted_at')

    owner_id: Mapped[str] = mapped_column(ForeignKey("users.id"), nullable=True)
    owner: Mapped["User"] = relationship(back_populates="libraries")
    
    name: Mapped[str] = mapped_column(String(150), unique=True, nullable=False)
    description: Mapped[str] = mapped_column(String(255), nullable=False)
    is_public: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)

    items: Mapped[ForwardRef("LibraryItem")] = relationship("LibraryItem", back_populates="library")
    itemsThumbnails: Mapped[ForwardRef("Thumbnail")] = relationship("Thumbnail", back_populates="library")

    def __repr__(self) -> str:
        return f"<Library(name={self.name}, description={self.description}, is_public={self.is_public})>"

    