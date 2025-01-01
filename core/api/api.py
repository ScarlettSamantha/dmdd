from flask import Flask, request, g
from flask_restful import Api
from typing import Optional, Callable, Type, Any, Dict
from functools import wraps
from flask_sqlalchemy import SQLAlchemy
from api.resources.library_item import LibraryItemResource
from api.resources.playlist import PlaylistResource
from api.resources.playlist_item import PlaylistItemResource
from api.resources.system_event import SystemEventResource
from api.resources.system_user import SystemUserResource
from api.resources.library import LibraryResource
from api.resources.version import VersionResource
from api.auth import ApiAuthenticator
from api.validators import InputValidator


class APIHandler:
    def __init__(self, app: Flask, db, validator: Type[InputValidator]) -> None:
        self.app: Flask = app
        self.api: Api = Api(self.app)
        self.db: SQLAlchemy = db
        self.authenticator: ApiAuthenticator = ApiAuthenticator(app, db)
        self.validator: Type[InputValidator] = validator
        self.setup_routes()

    def unauthorized(self) -> Dict[str, Any]:
        return {"status": "error", "message": "Unauthorized", "http_code": 401}

    def require_auth(self, func: Callable) -> Callable:
        """
        Instance-level decorator for enforcing authentication.
        """

        @wraps(func)
        def wrapper(*args, **kwargs) -> Any:
            _token_raw: Optional[str] = request.headers.get("Authorization")
            _token: Optional[str] = (
                _token_raw.split(" ")[1]
                if _token_raw
                and len(_token_raw.split(" ")) > 1
                and len(_token_raw.split(" ")[1]) == 36
                else None
            )
            user = self.authenticator.authenticate(_token)
            if not user:
                return self.unauthorized()

            g.user = user  # Store the authenticated user in the global context
            return func(*args, **kwargs)

        return wrapper

    def setup_routes(self) -> None:
        constructor_kwargs = {
            "require_auth": self.require_auth,
            "app": self.app,
            "db": self.db,
            "validator": self.validator(),
        }

        # System routes
        self.api.add_resource(
            SystemEventResource,
            "/api/system/events",
            "/api/system/events/<uuid:event_id>",
        )
        self.api.add_resource(
            SystemUserResource,
            "/api/system/users",
            "/api/system/users/<uuid:user_id>",
            resource_class_kwargs=constructor_kwargs,
        )

        self.api.add_resource(
            LibraryResource,
            "/api/libraries",
            "/api/libraries/<uuid:library_id>",
            resource_class_kwargs=constructor_kwargs,
        )

        self.api.add_resource(
            LibraryItemResource,
            "/api/libraries/<uuid:library_id>/items",
            "/api/libraries/<uuid:library_id>/items/<uuid:item_id>",
            resource_class_kwargs=constructor_kwargs,
        )

        # Playlist routes
        self.api.add_resource(
            PlaylistResource, "/api/playlists", "/api/playlists/<uuid:playlist_id>"
        )

        self.api.add_resource(
            PlaylistItemResource,
            "/api/playlists/<uuid:playlist_id>/items",
            "/api/playlists/<uuid:playlist_id>/items/<uuid:item_id>",
        )

        # Version routes
        self.api.add_resource(VersionResource, "/version", "/api/system/version")
