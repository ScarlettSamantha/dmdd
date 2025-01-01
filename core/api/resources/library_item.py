from typing import Any, Callable, Dict, Optional, Tuple, Type, List
from flask import Flask, request
from flask_sqlalchemy import SQLAlchemy

from models.library_item import LibraryItem
from models.library import Library

from api.resources.auth import AuthResource
from api.validators import InputValidator

from repositories.library_item_repository import LibraryItemRepository


class LibraryItemResource(AuthResource):
    func_auth_required: Tuple[str, ...] = ("get", "post", "put", "delete", "link")

    def __init__(
        self,
        require_auth: Callable,
        app: Flask,
        db: SQLAlchemy,
        validator: Type[InputValidator],
    ) -> None:
        super().__init__(require_auth, app, db, validator)
        self.repo: LibraryItemRepository = LibraryItemRepository(
            app=self.app, db=self.db
        )

    def get(self, item_id: Optional[str] = None):
        """
        Fetch a library item by ID or list all library items.
        """
        try:
            if item_id:
                item: Optional[LibraryItem] = self.repo.get_by_id(item_id)
                if not item:
                    return self.failure_response(
                        "Library item not found", status_code=404
                    )
                return self.success_response(data=item.api_response(full=True))
            else:
                items: List[LibraryItem] = self.repo.get_all()
                return self.success_response(
                    data={
                        "library_items": [
                            item.api_response(full=False) for item in items
                        ]
                    }
                )
        except Exception as e:
            return self.exception_response(e)

    def post(self):
        """
        Create a new library item.
        """
        try:
            item_data: Optional[Dict] = request.json
            if item_data is None:
                return self.failure_response("Invalid input data", status_code=400)

            validation_errors: List[str] = self.validator.verify_input(
                item_data, LibraryItem
            )
            if validation_errors:
                return self.failure_response(errors=validation_errors, status_code=400)

            new_item: LibraryItem = self.repo.add(
                LibraryItem(
                    name=item_data["name"],
                    description=item_data.get("description"),
                    mime_type=item_data["mime_type"],
                    file_size=item_data["file_size"],
                    file_path=item_data["file_path"],
                    is_public=item_data.get("is_public", True),
                    owner_id=item_data.get("owner_id"),
                    library_id=item_data.get("library_id"),
                )
            )
            return self.success_response(
                data=new_item.api_response(full=True),
                message="Library item created successfully",
                status_code=201,
            )
        except Exception as e:
            return self.exception_response(e)

    def put(self, item_id: str):
        """
        Update an existing library item by ID.
        """
        try:
            item_data: Any | None = request.json
            if item_data is None:
                return self.failure_response("Invalid input data", status_code=400)

            item = self.repo.get_by_id(item_id)
            if not item:
                return self.failure_response("Library item not found", status_code=404)

            validation_errors = self.validator.verify_input(item_data, LibraryItem)
            if validation_errors:
                return self.failure_response(errors=validation_errors, status_code=400)

            for key, value in item_data.items():
                if hasattr(item, key):
                    setattr(item, key, value)

            updated_item = self.repo.update(item)
            return self.success_response(
                data=updated_item.api_response(full=True),
                message="Library item updated successfully",
            )
        except Exception as e:
            return self.exception_response(e)

    def delete(self, item_id: str):
        """
        Delete a library item by ID.
        """
        try:
            item = self.repo.get_by_id(item_id)
            if not item:
                return self.failure_response("Library item not found", status_code=404)

            self.repo.delete(item)
            return self.success_response(
                message=f"Library item {item_id} deleted successfully",
                status_code=204,
            )
        except Exception as e:
            return self.exception_response(e)

    def link(self, item_id: str, library_id: str):
        """
        Link a library item to a library.
        """
        try:
            item = self.repo.get_by_id(item_id)
            if not item:
                return self.failure_response("Library item not found", status_code=404)

            library: Optional[Library] = (
                self.repo.db.session.query(Library).filter_by(id=library_id).first()
            )
            if not library:
                return self.failure_response("Library not found", status_code=404)

            item.library_id = library_id
            updated_item = self.repo.update(item)

            return self.success_response(
                data=updated_item.api_response(full=True),
                message="Library item linked to library successfully",
            )
        except Exception as e:
            return self.exception_response(e)
