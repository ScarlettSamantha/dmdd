from flask import Flask, request, jsonify, g
from flask_restful import Api, Resource
from typing import Optional, Callable, Dict, Union, Type, List, Any, Iterable, Tuple
from functools import wraps
from version import VERSION
from models.user import User
from models.model import T
from flask_sqlalchemy import SQLAlchemy


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
        
class InputValidator:
    @staticmethod
    def verify_input(
        input_data: Dict,
        model: Union[Type[T], Dict[str, Type]],
        exclude_internal: bool = True,
        type_validation: Callable[[object, Type], bool] = lambda value, expected_type: isinstance(value, expected_type)
    ) -> List[str]:
        """
        Verify input data matches the model fields and types or a provided schema, including nested validation.

        :param input_data: The data to verify
        :param model: The SQLAlchemy model or a dictionary defining required fields and types
        :param exclude_internal: If True, excludes internal columns (e.g., primary key, timestamps)
        :param type_validation: A custom callable for type validation
        :return: A list of validation error messages
        """
        errors = []
        internal_columns = ['id', 'created_at', 'updated_at'] if exclude_internal else []

        def get_allowed_api_fields(model_class: Type[T]) -> Iterable[str]:
            """
            Retrieve the allowed API fields for the given model.

            :param model_class: The SQLAlchemy model class
            :return: An iterable of allowed field names
            """
            allowed_fields: Optional[Iterable] = getattr(model_class, 'get_allowed_fields', None)() if hasattr(model_class, 'get_allowed_fields') else None
            if not allowed_fields:
                # Default to all column names if __ALLOWED_API_FIELDS__ is None or empty
                return [column.name for column in model_class.__table__.columns]
            return allowed_fields

        def validate_field(value: Any, expected_type: Any, field_name: str):
            """Perform validation for a single field, including nested objects."""
            if isinstance(expected_type, dict):
                # Recursive validation for nested dictionaries
                if not isinstance(value, dict):
                    errors.append(f"Field '{field_name}' should be a dictionary.")
                else:
                    nested_errors = InputValidator.verify_input(value, expected_type, exclude_internal, type_validation)
                    errors.extend([f"{field_name}.{err}" for err in nested_errors])
            elif isinstance(expected_type, list):
                # Validation for lists of a specific type
                if not isinstance(value, list):
                    errors.append(f"Field '{field_name}' should be a list.")
                else:
                    for idx, item in enumerate(value):
                        for subtype in expected_type:
                            if not type_validation(item, subtype):
                                errors.append(f"Field '{field_name}[{idx}]' should be of type '{subtype.__name__}'.")
            else:
                # Basic type validation
                if not type_validation(value, expected_type):
                    errors.append(f"Field '{field_name}' should be of type '{expected_type.__name__}'.")

        if isinstance(model, dict):
            # Validate against a provided dictionary schema
            for field, field_type in model.items():
                if field not in input_data:
                    errors.append(f"Field '{field}' is required.")
                    continue

                validate_field(input_data[field], field_type, field)

            for key in input_data.keys():
                if key not in model:
                    errors.append(f"Field '{key}' is not valid for the provided schema.")
        else:
            # Validate against SQLAlchemy model
            allowed_fields = get_allowed_api_fields(model)

            for column in model.__table__.columns:
                column_name = column.name

                if column_name in internal_columns or column_name not in allowed_fields:
                    continue

                if column_name not in input_data:
                    if not column.nullable and column.default is None:
                        errors.append(f"Field '{column_name}' is required.")
                    continue

                value = input_data[column_name]

                # Type validation
                expected_type = column.type.python_type
                validate_field(value, expected_type, column_name)

            # Additional validation for unexpected fields
            for key in input_data.keys():
                if key not in [column.name for column in model.__table__.columns] or key not in allowed_fields:
                    errors.append(f"Field '{key}' is not valid for model '{model.__name__}'.")

        return errors


class APIHandler:
    def __init__(self, app: Flask, db, validator: Type[InputValidator]) -> None:
        self.app: Flask = app
        self.api: Api = Api(self.app)
        self.db: SQLAlchemy = db 
        self.authenticator: ApiAuthenticator = ApiAuthenticator(app, db)
        self.validator: Type[InputValidator] = validator
        self.setup_routes()
        
    def unauthorized(self) -> dict:
        return {"status": "error", "message": "Unauthorized", "http_code": 401}, 401

    def require_auth(self, func: Callable):
        """
        Instance-level decorator for enforcing authentication.
        """
        @wraps(func)
        def wrapper(*args, **kwargs):
            token: str = request.headers.get('Authorization')
            token: Optional[str] = token.split(" ")[1] if token and len(token.split(" ")) > 1 and len(token.split(" ")[1]) == 36 else None
            user = self.authenticator.authenticate(token)
            if not user:
                return self.unauthorized()

            g.user = user  # Store the authenticated user in the global context
            return func(*args, **kwargs)
        return wrapper

    def setup_routes(self) -> None:
        constructor_kwargs = {'require_auth': self.require_auth, 'app': self.app, 'db': self.db, 'validator': self.validator()}
        
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


class AuthResource(Resource):
    
    func_auth_required = ()
    
    def __init__(self, require_auth: Callable, app: Flask, db: SQLAlchemy, validator: Type[InputValidator]) -> None:
        self.app: Flask = app
        self.db: SQLAlchemy = db
        self.require_auth: Callable = require_auth
        self.validator: Type[InputValidator] = validator

        self.apply_auths()
    
    def apply_auths(self):
        for func in self.func_auth_required:
            if hasattr(self, func) and callable(getattr(self, func)) and not func.startswith('_'):
                setattr(self, func, self.require_auth(getattr(self, func)))
    
    def apply_auth(self, func):
        return self.require_auth(func)
    
    def make_response(self, data, *args, **kwargs):
        """
        Ensure responses are always JSON serializable.
        """
        if isinstance(data, (dict, list)):  # If data is already JSON-serializable
            return jsonify(data)
        elif isinstance(data, tuple):  # Handle (data, status) or (data, status, headers)
            serialized_data = jsonify(data[0]) if isinstance(data[0], (dict, list)) else data[0]
            return serialized_data, *data[1:]
        return data  # Default behavior if it's already a valid response


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

class SystemUserResource(AuthResource):

    func_auth_required: Tuple[str] = ('get', 'post', 'put', 'delete')

    def __init__(self, require_auth: Callable, app: Flask, db: SQLAlchemy, validator: Type[InputValidator]) -> None:
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
            return self.make_response({"status": "success", "data": user.api_response(full=True)})
        else:
            users = self.repo.get_all()
            return {"status": "success", "data": {"users": [user.api_response(full=False) for user in users]}}

    def post(self):
        """
        Create a new user.
        """
        user_data = request.json
        try:
            validation_errors = self.validator.verify_input(user_data, User)
            if validation_errors:
                return {"status": "error", "errors": validation_errors}, 400

            new_user = self.repo.register_user(
                username=user_data['username'],
                email=user_data['email'],
                password=user_data['password'],
                active_user=user_data.get('is_active', False),
                confirm_user=user_data.get('is_confirmed', False),
                admin_user=user_data.get('is_admin', False),
                generate_api_key=user_data.get('generate_api_key', False)
            )
            return {"status": "success", "data": new_user.api_response(full=True), "message": "User created successfully"}, 201
        except Exception as e:
            return {"status": "error", "message": str(e)}, 400

    def put(self, user_id: str):
        """
        Update an existing user by ID.
        """
        user_data = request.json
        user = self.repo.get_by_id(user_id)
        if not user:
            return {"status": "error", "message": "User not found"}, 404

        try:
            validation_errors = self.validator.verify_input(user_data, User)
            if validation_errors:
                return {"status": "error", "errors": validation_errors}, 400

            for key, value in user_data.items():
                if hasattr(user, key):
                    setattr(user, key, value)
            updated_user = self.repo.update(user)
            return {"status": "success", "data": updated_user.api_response(full=True), "message": "User updated successfully"}
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
            return {"status": "success", "message": f"User {user_id} deleted successfully"}, 204
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