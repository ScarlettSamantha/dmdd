from typing import Optional


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
