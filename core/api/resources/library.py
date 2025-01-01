from typing import Optional, Tuple, Type
from flask import Flask, request
from flask_sqlalchemy import SQLAlchemy
from models.library import Library
from api.resources.auth import AuthResource
from api.validators import InputValidator
from repositories.library_repository import LibraryRepository


class LibraryResource(AuthResource):
    func_auth_required: Tuple[str, ...] = ("get", "post", "put", "delete")

    def __init__(
        self, require_auth, app: Flask, db: SQLAlchemy, validator: Type[InputValidator]
    ) -> None:
        super().__init__(require_auth, app, db, validator)
        self.repo = LibraryRepository(app=self.app, db=self.db)

    def get(self, library_id: Optional[str] = None):
        """
        Fetch a library by ID or list all libraries.
        """
        if library_id:
            library = self.repo.get_by_id(library_id)
            if not library:
                return {"status": "error", "message": "Library not found"}, 404
            return self.make_response(
                {"status": "success", "data": library.api_response(full=True)}
            )
        else:
            libraries = self.repo.get_all()
            return {
                "status": "success",
                "data": {
                    "libraries": [
                        library.api_response(full=False) for library in libraries
                    ]
                },
            }

    def post(self):
        """
        Create a new library.
        """
        library_data = request.json
        try:
            if not isinstance(library_data, dict):
                return {"status": "error", "message": "Invalid input data"}, 400

            validation_errors = self.validator.verify_input(library_data, Library)
            if validation_errors:
                return {"status": "error", "errors": validation_errors}, 400

            new_library = Library(
                name=library_data["name"],
                description=library_data["description"],
                is_public=library_data.get("is_public", True),
                owner_id=library_data["owner_id"],
            )
            self.repo.add(new_library)
            return {
                "status": "success",
                "data": new_library.api_response(full=True),
                "message": "Library created successfully",
            }, 201
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def put(self, library_id: str):
        """
        Update an existing library by ID.
        """
        library_data = request.json
        library = self.repo.get_by_id(library_id)
        if not library:
            return {"status": "error", "message": "Library not found"}, 404

        try:
            if not isinstance(library_data, dict):
                return {"status": "error", "message": "Invalid input data"}, 400

            validation_errors = self.validator.verify_input(library_data, Library)
            if validation_errors:
                return {"status": "error", "errors": validation_errors}, 400

            for key, value in library_data.items():
                if hasattr(library, key):
                    setattr(library, key, value)
            updated_library = self.repo.update(library)
            return {
                "status": "success",
                "data": updated_library.api_response(full=True),
                "message": "Library updated successfully",
            }
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def delete(self, library_id: str):
        """
        Delete a library by ID.
        """
        library = self.repo.get_by_id(library_id)
        if not library:
            return {"status": "error", "message": "Library not found"}, 404

        try:
            self.repo.delete(library)
            return {
                "status": "success",
                "message": f"Library {library_id} deleted successfully",
            }, 204
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def link(self, library_id: str, other_id: str):
        """
        Link a library to another entity.
        """
        try:
            library = self.repo.get_by_id(library_id)
            if not library:
                return {"status": "error", "message": "Library not found"}, 404

            linked = self.repo.link_to_other(library_id, other_id)
            if not linked:
                return {"status": "error", "message": "Failed to link library"}, 400

            return {
                "status": "success",
                "message": f"Library {library_id} linked successfully",
            }
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def unlink(self, library_id: str, user_id: str):
        """
        Unlink a library from another entity.
        """
        try:
            library = self.repo.get_by_id(library_id)
            if not library:
                return {"status": "error", "message": "Library not found"}, 404

            unlinked = self.repo.unlink_from_other(library_id, user_id)
            if not unlinked:
                return {"status": "error", "message": "Failed to unlink library"}, 400

            return {
                "status": "success",
                "message": f"Library {library_id} unlinked successfully",
            }
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400
