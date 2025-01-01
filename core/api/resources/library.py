from typing import Optional, Tuple, Type
from flask import Flask, request
from flask_sqlalchemy import SQLAlchemy
from api.resources.auth import AuthResource
from api.validators import InputValidator
from models.library import Library
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
        try:
            if library_id:
                library = self.repo.get_by_id(library_id)
                if not library:
                    return self.failure_response("Library not found", status_code=404)
                return self.success_response(data=library.api_response(full=True))
            else:
                libraries = self.repo.get_all()
                return self.success_response(
                    data={
                        "libraries": [
                            library.api_response(full=False) for library in libraries
                        ]
                    }
                )
        except Exception as e:
            return self.exception_response(e)

    def post(self):
        """
        Create a new library.
        """
        try:
            library_data = request.json
            if not isinstance(library_data, dict):
                return self.failure_response("Invalid input data", status_code=400)

            validation_errors = self.validator.verify_input(library_data, Library)
            if validation_errors:
                return self.failure_response(errors=validation_errors, status_code=400)

            new_library = Library(
                name=library_data["name"],
                description=library_data["description"],
                is_public=library_data.get("is_public", True),
                owner_id=library_data["owner_id"],
            )
            self.repo.add(new_library)
            return self.success_response(
                data=new_library.api_response(full=True),
                message="Library created successfully",
                status_code=201,
            )
        except Exception as e:
            return self.exception_response(e)

    def put(self, library_id: str):
        """
        Update an existing library by ID.
        """
        try:
            library_data = request.json
            library = self.repo.get_by_id(library_id)
            if not library:
                return self.failure_response("Library not found", status_code=404)

            if not isinstance(library_data, dict):
                return self.failure_response("Invalid input data", status_code=400)

            validation_errors = self.validator.verify_input(library_data, Library)
            if validation_errors:
                return self.failure_response(errors=validation_errors, status_code=400)

            for key, value in library_data.items():
                if hasattr(library, key):
                    setattr(library, key, value)
            updated_library = self.repo.update(library)
            return self.success_response(
                data=updated_library.api_response(full=True),
                message="Library updated successfully",
            )
        except Exception as e:
            return self.exception_response(e)

    def delete(self, library_id: str):
        """
        Delete a library by ID.
        """
        try:
            library = self.repo.get_by_id(library_id)
            if not library:
                return self.failure_response("Library not found", status_code=404)

            self.repo.delete(library)
            return self.success_response(
                message=f"Library {library_id} deleted successfully",
                status_code=204,
            )
        except Exception as e:
            return self.exception_response(e)

    def link(self, library_id: str, other_id: str):
        """
        Link a library to another entity.
        """
        try:
            library = self.repo.get_by_id(library_id)
            if not library:
                return self.failure_response("Library not found", status_code=404)

            linked = self.repo.link_to_other(library_id, other_id)
            if not linked:
                return self.failure_response("Failed to link library", status_code=400)

            return self.success_response(
                message=f"Library {library_id} linked successfully"
            )
        except Exception as e:
            return self.exception_response(e)

    def unlink(self, library_id: str, user_id: str):
        """
        Unlink a library from another entity.
        """
        try:
            library = self.repo.get_by_id(library_id)
            if not library:
                return self.failure_response("Library not found", status_code=404)

            unlinked = self.repo.unlink_from_other(library_id, user_id)
            if not unlinked:
                return self.failure_response(
                    "Failed to unlink library", status_code=400
                )

            return self.success_response(
                message=f"Library {library_id} unlinked successfully"
            )
        except Exception as e:
            return self.exception_response(e)
