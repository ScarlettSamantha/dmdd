from models.library_item import LibraryItem
from models.library import Library
from typing import Optional, Union
from flask_sqlalchemy import SQLAlchemy
from flask import Flask

from .repository import BaseRepository, execute_with_context


class LibraryItemRepository(BaseRepository[LibraryItem]):
    def __init__(self, db: SQLAlchemy, app: Flask):
        super().__init__(db=db, model=LibraryItem, app=app)

    @execute_with_context
    def link_to_library(
        self,
        item: Union[LibraryItem, str],
        library: Union[Library, str],
        check_existence: bool = True,
    ) -> Optional[LibraryItem]:
        """
        Link a library item to a library by updating the library_id field.

        Args:
            item (Union[LibraryItem, str]): The library item object or the ID of the library item to link.
            library (Union[Library, str]): The library object or the ID of the library to link to.
            check_existence (bool): Whether to check for the existence of the library item and library.
        Returns:
            Optional[LibraryItem]: The updated library item if successful, None otherwise.
        """
        if check_existence:
            if isinstance(item, str):
                item = self.get_by_id(item)
            if not item:
                return None

            if isinstance(library, str):
                library = self.db.session.query(Library).filter_by(id=library).first()
            if not library:
                return None

        _item: LibraryItem = (
            self.get_by_id(item.id)
            if not check_existence and isinstance(item, LibraryItem)
            else item
        )
        _item.library_id = str(library.id if isinstance(library, Library) else library)  # type: ignore
        self.update(_item)
        return _item

    @execute_with_context
    def unlink_from_library(
        self, item: Union[LibraryItem, str], check_existence: bool = True
    ) -> Optional[LibraryItem]:
        """
        Unlink a library item from a library by setting the library_id field to None.

        Args:
            item (Union[LibraryItem, str]): The library item object or the ID of the library item to unlink.
            check_existence (bool): Whether to check for the existence of the library item.
        Returns:
            Optional[LibraryItem]: The updated library item if successful, None otherwise.
        """
        if check_existence:
            if isinstance(item, str):
                item = self.get_by_id(item)
            if not item:
                return None

        _item: LibraryItem = (
            self.get_by_id(item.id)
            if not check_existence and isinstance(item, LibraryItem)
            else item
        )
        _item.library_id = None  # type: ignore
        self.update(_item)
        return item
