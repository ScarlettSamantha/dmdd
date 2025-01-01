from typing import Callable, Tuple, Type
from flask import Flask
from flask_sqlalchemy import SQLAlchemy
from api.validators import InputValidator
from api.resource import Resource


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
