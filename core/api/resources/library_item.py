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

    def get(self, item_id: Optional[str] = None) -> Tuple[Dict, int]:
        """
        Fetch a library item by ID or list all library items.
        """
        if item_id:
            item: Optional[LibraryItem] = self.repo.get_by_id(item_id)
            if not item:
                return {"status": "error", "message": "Library item not found"}, 404
            return {"status": "success", "data": item.api_response(full=True)}, 200
        else:
            items: List[LibraryItem] = self.repo.get_all()
            return {
                "status": "success",
                "data": {
                    "library_items": [item.api_response(full=False) for item in items]
                },
            }, 200

    def post(self) -> Tuple[Dict, int]:
        """
        Create a new library item.
        """
        item_data: Optional[Dict] = request.json
        try:
            validation_errors: List[str] = self.validator.verify_input(
                item_data, LibraryItem
            )
            if item_data is None or validation_errors:
                return {"status": "error", "errors": validation_errors}, 400

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
            return {
                "status": "success",
                "data": new_item.api_response(full=True),
                "message": "Library item created successfully",
            }, 201
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def put(self, item_id: str) -> Tuple[Dict, int]:
        """
        Update an existing library item by ID.
        """
        item_data: Any | None = request.json
        item = self.repo.get_by_id(item_id)
        if not item:
            return {"status": "error", "message": "Library item not found"}, 404

        try:
            validation_errors = self.validator.verify_input(item_data, LibraryItem)
            if item_data is None or validation_errors:
                return {"status": "error", "errors": validation_errors}, 400

            for key, value in item_data.items():
                if hasattr(item, key):
                    setattr(item, key, value)

            updated_item = self.repo.update(item)
            return {
                "status": "success",
                "data": updated_item.api_response(full=True),
                "message": "Library item updated successfully",
            }, 200
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def delete(self, item_id: str) -> Tuple[Dict, int]:
        """
        Delete a library item by ID.
        """
        item = self.repo.get_by_id(item_id)
        if not item:
            return {"status": "error", "message": "Library item not found"}, 404

        try:
            self.repo.delete(item)
            return {
                "status": "success",
                "message": f"Library item {item_id} deleted successfully",
            }, 204
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def link(self, item_id: str, library_id: str) -> Tuple[Dict, int]:
        """
        Link a library item to a library.
        """
        item = self.repo.get_by_id(item_id)
        if not item:
            return {"status": "error", "message": "Library item not found"}, 404

        try:
            library: Optional[Library] = (
                self.repo.db.session.query(Library).filter_by(id=library_id).first()
            )
            if not library:
                return {"status": "error", "message": "Library not found"}, 404

            item.library_id = library_id
            updated_item = self.repo.update(item)

            return {
                "status": "success",
                "data": updated_item.api_response(full=True),
                "message": "Library item linked to library successfully",
            }, 200
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400
