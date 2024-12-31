from typing import List, Union, Optional, TYPE_CHECKING
from sqlalchemy import String, Boolean, ForeignKey
from sqlalchemy.orm import Mapped, mapped_column, relationship
from faker import Faker

from .model import BaseModel

if TYPE_CHECKING:
    from .user import User
    from .library_item import LibraryItem
    from .thumbnail import Thumbnail

faker = Faker()


class Library(BaseModel):
    """
    User model with fields typically required for a backend application.
    """

    __tablename__ = "library"
    serialize_head_only = ("id", "name", "description", "is_public", "owner_id")
    serialize_only = (
        "id",
        "name",
        "description",
        "is_public",
        "owner",
        "created_at",
        "updated_at",
        "deleted_at",
    )

    owner_id: Mapped[Optional[str]] = mapped_column(
        ForeignKey("users.id"), nullable=True
    )
    owner: Mapped["User"] = relationship(back_populates="libraries")

    name: Mapped[str] = mapped_column(String(150), unique=True, nullable=False)
    description: Mapped[str] = mapped_column(String(255), nullable=False)
    is_public: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)

    items: Mapped["LibraryItem"] = relationship("LibraryItem", back_populates="library")
    itemsThumbnails: Mapped["Thumbnail"] = relationship(
        "Thumbnail", back_populates="library"
    )

    def __repr__(self) -> str:
        return f"<Library(name={self.name}, description={self.description}, is_public={self.is_public})>"

    @classmethod
    def seed(
        cls,
        user: Union["User", str],
        num_objects: int = 1,
        generate_sub_objects: bool = False,
    ) -> Optional[List["BaseModel"]]:
        """
        Generate and return a list of Library objects linked to a given user with realistic random properties.

        Args:
            user (Union["User", str]): The User object or user ID to link the libraries to.
            num_objects (int): The number of Library objects to generate. Default is 1.

        Returns:
            List[Library]: A list of Library objects.
        """
        user_id = user.id if isinstance(user, User) else user

        return [
            cls(
                owner_id=user_id,
                name=faker.unique.company(),
                description=faker.text(max_nb_chars=200),
                is_public=faker.boolean(chance_of_getting_true=75),
            )
            for _ in range(num_objects)
        ]
