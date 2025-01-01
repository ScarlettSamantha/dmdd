from typing import Callable, Tuple, Type
from flask import Flask, Response, jsonify
from flask_sqlalchemy import SQLAlchemy
from flask_restful import Resource
from api.validators import InputValidator


class AuthResource(Resource):
    func_auth_required: Tuple[str, ...] = ()

    def __init__(
        self,
        require_auth: Callable,
        app: Flask,
        db: SQLAlchemy,
        validator: Type[InputValidator],
    ) -> None:
        self.app: Flask = app
        self.db: SQLAlchemy = db
        self.require_auth: Callable = require_auth
        self.validator: Type[InputValidator] = validator

        self.apply_auths()

    def apply_auths(self) -> None:
        for func in self.func_auth_required:
            if (
                hasattr(self, func)
                and callable(getattr(self, func))
                and not func.startswith("_")
            ):
                setattr(self, func, self.require_auth(getattr(self, func)))

    def apply_auth(self, func) -> Callable:
        return self.require_auth(func)

    def make_response(self, data, *args, **kwargs) -> Tuple | Response:
        """
        Ensure responses are always JSON serializable.
        """
        if isinstance(data, (dict, list)):  # If data is already JSON-serializable
            return jsonify(data)
        elif isinstance(
            data, tuple
        ):  # Handle (data, status) or (data, status, headers)
            serialized_data = (
                jsonify(data[0]) if isinstance(data[0], (dict, list)) else data[0]
            )
            return serialized_data, *data[1:]
        return data  # Default behavior if it's already a valid response
