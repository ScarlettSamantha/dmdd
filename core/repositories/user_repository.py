from models.user import User
from typing import Optional
from flask_sqlalchemy import SQLAlchemy
from flask import Flask

from .repository import BaseRepository, execute_with_context

class UserRepository(BaseRepository[User]):
    def __init__(self, db: SQLAlchemy, app: Flask):
        super().__init__(db=db, model=User, app=app)

    @execute_with_context
    def find_by_email(self, email: str) -> Optional[User]:
        self.db.session.query(User).all()
        return self.db.session.query(self.model).filter_by(email=email).first()
    
    @execute_with_context
    def find_by_username(self, username: str) -> Optional[User]:
        return self.db.session.query(self.model).filter_by(username=username).first()
    
    @execute_with_context
    def register_user(self, username: str, email: str, password: str, active_user: bool = False, confirm_user: bool = False, admin_user: bool = False, generate_api_key: bool = False,**kwargs) -> User:
        user = User(username=username, email=email, is_active=active_user, is_confirmed=confirm_user, is_admin=admin_user, **kwargs)
        user.set_password(password)
        
        if generate_api_key:
            user.generate_api_key()
            
        self.db.session.add(user)
        self.db.session.commit()
        return user
    
    @execute_with_context
    def activate_user(self, user: User) -> None:
        user.is_active = True
        self.db.session.add(user)
        self.db.session.commit()
        
    @execute_with_context
    def deactivate_user(self, user: User) -> None:
        user.is_active = False
        self.db.session.add(user)
        self.db.session.commit()
        
    @execute_with_context
    def confirm_user(self, user: User) -> None:
        user.is_confirmed = True
        self.db.session.add(user)
        self.db.session.commit()
        
    @execute_with_context
    def unconfirm_user(self, user: User) -> None:
        user.is_confirmed = False
        self.db.session.add(user)
        self.db.session.commit()
        
    @execute_with_context
    def search_by_api_key(self, api_key: str, is_active: bool = True, is_admin: bool = False, is_confirmed: bool = True) -> Optional[User]:
        query = self.db.session.query(User) \
            .filter_by(api_key=api_key) \
            .filter_by(is_active=is_active) \
            .filter_by(is_confirmed=is_confirmed)
        if is_admin:
            query = query.filter_by(is_admin=True)
        return query.first() if query.count() > 0 else None