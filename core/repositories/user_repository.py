from models.user import User
from typing import Optional

from .repository import BaseRepository, execute_with_context

class UserRepository(BaseRepository[User]):
    def __init__(self, db, app):
        super().__init__(db=db, model=User, app=app)

    @execute_with_context
    def find_by_email(self, email: str) -> Optional[User]:
        self.db.session.query(User).all()
        return self.session.query(self.model).filter_by(email=email).first()
