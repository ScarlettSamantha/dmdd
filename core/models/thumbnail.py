from sqlalchemy import String, Boolean, ForeignKey, Integer, Text
from sqlalchemy.orm import Mapped, mapped_column, relationship
from typing import Optional, TYPE_CHECKING 

from .model import BaseModel
from .user import User
from .library import Library

if TYPE_CHECKING:
    from .user import User
    from .library import Library

class Thumbnail(BaseModel):
    """
    Thumbnail model representing media assets linked to users or libraries.
    """
    __tablename__ = 'thumbnails'

    # Fields to serialize for concise API responses
    serialize_head_only = ('id', 'name', 'description', 'is_public', 'owner_id')
    serialize_only = (
        'id', 'name', 'description', 'is_public', 'owner', 'created_at',
        'updated_at', 'deleted_at', 'mime_type', 'file_size', 'file_path',
        'raw_data', 'library_id', 'library'
    )

    # Foreign key relationship with User
    owner_id: Mapped[int] = mapped_column(ForeignKey("users.id"), nullable=True)
    owner: Mapped["User"] = relationship("User", back_populates="libraryItemsThumbnails")

    # Foreign key relationship with Library
    library_id: Mapped[int] = mapped_column(ForeignKey("library.id"), nullable=True)
    library: Mapped["Library"] = relationship("Library", back_populates="itemsThumbnails")

    # Additional properties specific to Thumbnails
    mime_type: Mapped[str] = mapped_column(String(150), nullable=False)
    file_size: Mapped[int] = mapped_column(Integer, nullable=False)
    file_path: Mapped[str] = mapped_column(String(255), nullable=False)
    is_public: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)

    name: Mapped[str] = mapped_column(String(150), unique=True, nullable=False)
    description: Mapped[Optional[str]] = mapped_column(Text, nullable=True)

    raw_data: Mapped[Optional[str]] = mapped_column(Text, nullable=True)

    def __repr__(self) -> str:
        return (
            f"<Thumbnail(name={self.name}, description={self.description}, "
            f"is_public={self.is_public}, library_id={self.library_id}, "
            f"path={self.file_path})>"
        )
