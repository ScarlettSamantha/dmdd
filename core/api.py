from flask import Flask, request, jsonify, g
from flask_restful import Api, Resource
from typing import Optional, Callable
from functools import wraps
from version import VERSION
from json import dumps

class ApiAuthenticator:
    def __init__(self, app, db) -> None:
        self.app = app
        self.db = db

    def authenticate(self, token: Optional[str]) -> Optional[dict]:
        """
        Validates the provided token and returns the user if authentication is successful.
        """
        from repositories.user_repository import UserRepository

        if not token:
            return None

        return UserRepository(app=self.app, db=self.db).search_by_api_key(
            token, is_active=True, is_admin=False, is_confirmed=True
        )


class APIHandler:
    def __init__(self, app: Flask, db) -> None:
        self.app = app
        self.api = Api(self.app)
        self.db = db 
        self.authenticator = ApiAuthenticator(app, db)
        self.setup_routes()

    def require_auth(self, func: Callable):
        """
        Instance-level decorator for enforcing authentication.
        """
        @wraps(func)
        def wrapper(*args, **kwargs):
            token = request.headers.get('Authorization')
            token = token.split(" ")[1] if token and token.split(" ").__len__() > 1 else None
            user = self.authenticator.authenticate(token)
            if not user:
                return dumps({"status": "error", "message": "Unauthorized"})

            g.user = user  # Store the authenticated user in the global context
            return func(*args, **kwargs)
        return wrapper

    def setup_routes(self) -> None:
        constructor_kwargs = {'require_auth': self.require_auth, 'app': self.app, 'db': self.db}
        
        # Playlist routes
        self.api.add_resource(PlaylistResource, '/api/playlists', '/api/playlists/<uuid:playlist_id>')
        self.api.add_resource(PlaylistItemResource, '/api/playlists/<uuid:playlist_id>/items',
                              '/api/playlists/<uuid:playlist_id>/items/<uuid:item_id>')

        # System routes
        self.api.add_resource(SystemEventResource, '/api/system/events', '/api/system/events/<uuid:event_id>')
        self.api.add_resource(SystemUserResource, '/api/system/users', '/api/system/users/<uuid:user_id>',
                              resource_class_kwargs=constructor_kwargs)

        # Version routes
        self.api.add_resource(VersionResource, '/version', '/api/system/version')



class VersionResource(Resource):
    def get(self):
        return jsonify({
            "status": "success",
            "data": {
                "version": f"{VERSION[0]}.{VERSION[1]}.{VERSION[2]}",
                "releaselevel": VERSION[3],
                "serial": VERSION[4]
            }
        })

class PlaylistResource(Resource):
    def get(self, playlist_id: Optional[str] = None):
        if playlist_id:
            return jsonify({
                "status": "success",
                "data": {"id": playlist_id, "name": f"Playlist {playlist_id}"}
            })
        return jsonify({
            "status": "success",
            "data": {"playlists": [{"id": "1", "name": "Playlist 1"}, {"id": "2", "name": "Playlist 2"}]}
        })

    def post(self):
        new_playlist = request.json
        return jsonify({
            "status": "success",
            "data": new_playlist,
            "message": "Playlist created"
        }), 201

    def put(self, playlist_id: str):
        updated_data = request.json
        return jsonify({
            "status": "success",
            "data": {"id": playlist_id, "updated": updated_data},
            "message": "Playlist updated"
        })

    def delete(self, playlist_id: str):
        return jsonify({
            "status": "success",
            "message": f"Playlist {playlist_id} deleted"
        }), 204

class PlaylistItemResource(Resource):
    def get(self, playlist_id: str, item_id: Optional[str] = None):
        if item_id:
            return jsonify({
                "status": "success",
                "data": {"id": item_id, "name": f"Item {item_id} in Playlist {playlist_id}"}
            })
        return jsonify({
            "status": "success",
            "data": {"items": [{"id": "1", "name": "Item 1"}, {"id": "2", "name": "Item 2"}]}
        })

    def post(self, playlist_id: str):
        new_item = request.json
        return jsonify({
            "status": "success",
            "data": new_item,
            "message": f"Item added to Playlist {playlist_id}"
        }), 201

    def put(self, playlist_id: str, item_id: str):
        updated_data = request.json
        return jsonify({
            "status": "success",
            "data": {"id": item_id, "updated": updated_data},
            "message": f"Item {item_id} updated in Playlist {playlist_id}"
        })

    def delete(self, playlist_id: str, item_id: str):
        return jsonify({
            "status": "success",
            "message": f"Item {item_id} deleted from Playlist {playlist_id}"
        }), 204

class SystemEventResource(Resource):
    def get(self, event_id: Optional[str] = None):
        if event_id:
            return jsonify({
                "status": "success",
                "data": {"id": event_id, "description": f"Event {event_id}"}
            })
        return jsonify({
            "status": "success",
            "data": {"events": [{"id": "1", "description": "Event 1"}, {"id": "2", "description": "Event 2"}]}
        })

    def post(self):
        new_event = request.json
        return jsonify({
            "status": "success",
            "data": new_event,
            "message": "Event created"
        }), 201

    def put(self, event_id: str):
        updated_data = request.json
        return jsonify({
            "status": "success",
            "data": {"id": event_id, "updated": updated_data},
            "message": f"Event {event_id} updated"
        })

    def delete(self, event_id: str):
        return jsonify({
            "status": "success",
            "message": f"Event {event_id} deleted"
        }), 204

class SystemUserResource(Resource):
    """
    A Resource utilizing dynamically attached authentication decorators.
    """
    def __init__(self, require_auth: Callable, app, db):
        from repositories.user_repository import UserRepository
        
        self.app = app
        self.db = db
        self.require_auth = require_auth
        self.repo = UserRepository(app=self.app, db=self.db)

        # Dynamically attach the authentication decorator to all methods
        self.get = self.require_auth(self.get)
        self.post = self.require_auth(self.post)
        self.put = self.require_auth(self.put)
        self.delete = self.require_auth(self.delete)

    def get(self, user_id: Optional[str] = None):
        if user_id:
            user = self.repo.get_by_id(user_id)
            return jsonify({
                "status": "success",
                "data": {"id": user_id, "name": user.username}
            })
        else:
            users = self.repo.get_all()
            return jsonify({
                "status": "success",
                "data": {"users": [{"id": user.id, "name": user.username} for user in users]}
            })

    def post(self):
        new_user = request.json
        return jsonify({
            "status": "success",
            "data": new_user,
            "message": "User created"
        }), 201

    def put(self, user_id: str):
        updated_data = request.json
        return jsonify({
            "status": "success",
            "data": {"id": user_id, "updated": updated_data},
            "message": "User updated"
        })

    def delete(self, user_id: str):
        return jsonify({
            "status": "success",
            "message": f"User {user_id} deleted"
        }), 204