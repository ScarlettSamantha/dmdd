from typing import Callable, Dict, Optional, Tuple, Type
from flask import Flask, request
from flask_sqlalchemy import SQLAlchemy
from models.user import User
from api.resources.auth import AuthResource
from api.validators import InputValidator


class SystemUserResource(AuthResource):
    func_auth_required: Tuple[str, ...] = ("get", "post", "put", "delete")

    def __init__(
        self,
        require_auth: Callable,
        app: Flask,
        db: SQLAlchemy,
        validator: Type[InputValidator],
    ) -> None:
        from repositories.user_repository import UserRepository

        super().__init__(require_auth, app, db, validator)
        self.repo = UserRepository(app=self.app, db=self.db)

    def get(self, user_id: Optional[str] = None):
        """
        Fetch user by ID or list all users.
        """
        if user_id:
            user = self.repo.get_by_id(user_id)
            if not user:
                return {"status": "error", "message": "User not found"}, 404
            return self.make_response(
                {"status": "success", "data": user.api_response(full=True)}
            )
        else:
            users = self.repo.get_all()
            return {
                "status": "success",
                "data": {"users": [user.api_response(full=False) for user in users]},
            }

    def post(self):
        """
        Create a new user.
        """
        user_data: Optional[Dict] = request.json
        try:
            validation_errors = self.validator.verify_input(user_data, User)
            if user_data is None or validation_errors:
                return {"status": "error", "errors": validation_errors}, 400

            new_user = self.repo.register_user(
                username=user_data["username"],
                email=user_data["email"],
                password=user_data["password"],
                active_user=user_data.get("is_active", False),
                confirm_user=user_data.get("is_confirmed", False),
                admin_user=user_data.get("is_admin", False),
                generate_api_key=user_data.get("generate_api_key", False),
            )
            return {
                "status": "success",
                "data": new_user.api_response(full=True),
                "message": "User created successfully",
            }, 201
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def put(self, user_id: str):
        """
        Update an existing user by ID.
        """
        user_data = request.json
        user = self.repo.get_by_id(user_id)
        if not user:
            return {"status": "error", "message": "sser not found"}, 404

        try:
            validation_errors = self.validator.verify_input(user_data, User)
            if user_data is None or validation_errors:
                return {"status": "error", "errors": validation_errors}, 400

            for key, value in user_data.items():
                if hasattr(user, key):
                    setattr(user, key, value)
            updated_user = self.repo.update(user)
            return {
                "status": "success",
                "data": updated_user.api_response(full=True),
                "message": "User updated successfully",
            }
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def delete(self, user_id: str):
        """
        Delete a user by ID.
        """
        user = self.repo.get_by_id(user_id)
        if not user:
            return {"status": "error", "message": "User not found"}, 404

        try:
            self.repo.delete(user)
            return {
                "status": "success",
                "message": f"User {user_id} deleted successfully",
            }, 204
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def activate(self, user_id: str):
        """
        Activate a user account.
        """
        user = self.repo.get_by_id(user_id)
        if not user:
            return {"status": "error", "message": "User not found"}, 404

        try:
            self.repo.activate_user(user)
            return {"status": "success", "message": "User activated successfully"}
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def deactivate(self, user_id: str):
        """
        Deactivate a user account.
        """
        user = self.repo.get_by_id(user_id)
        if not user:
            return {"status": "error", "message": "User not found"}, 404

        try:
            self.repo.deactivate_user(user)
            return {"status": "success", "message": "User deactivated successfully"}
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def confirm(self, user_id: str):
        """
        Confirm a user account.
        """
        user = self.repo.get_by_id(user_id)
        if not user:
            return {"status": "error", "message": "User not found"}, 404

        try:
            self.repo.confirm_user(user)
            return {"status": "success", "message": "User confirmed successfully"}
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def unconfirm(self, user_id: str):
        """
        Unconfirm a user account.
        """
        user = self.repo.get_by_id(user_id)
        if not user:
            return {"status": "error", "message": "User not found"}, 404

        try:
            self.repo.unconfirm_user(user)
            return {"status": "success", "message": "User unconfirmed successfully"}
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400
