from models.library import Library
from flask_sqlalchemy import SQLAlchemy
from flask import Flask
from sqlalchemy.exc import SQLAlchemyError
from models.user import User
from .repository import BaseRepository


class LibraryRepository(BaseRepository[Library]):
    def __init__(self, db: SQLAlchemy, app: Flask):
        super().__init__(db=db, model=Library, app=app)

    def link_to_other(self, library_id: str, user_id: str) -> bool:
        """
        Link a library to another entity (user, project, etc.).

        :param library_id: ID of the library to link.
        :param user_id: ID of the user to link the library to.
        :return: True if linking is successful, False otherwise.
        """
        try:
            library = self.db.session.query(self.model).filter_by(id=library_id).first()
            if not library:
                return False

            user = self.db.session.query(User).filter_by(id=user_id).first()
            if not user:
                return False

            # Assume 'owner_id' is the linking field
            library.owner_id = user_id
            self.db.session.commit()
            return True
        except SQLAlchemyError as e:
            self.db.session.rollback()
            self.app.logger.error(f"Failed to link library: {e}")
            return False

    def unlink_from_other(self, library_id: str, user_id: str) -> bool:
        """
        Unlink a library from another entity (user, project, etc.).

        :param library_id: ID of the library to unlink.
        :param user_id: ID of the user the library is linked to.
        :return: True if unlinking is successful, False otherwise.
        """
        try:
            library = (
                self.db.session.query(self.model)
                .filter_by(id=library_id, owner_id=user_id)
                .first()
            )
            if not library:
                return False

            # Verify that the user exists before unlinking
            user = self.db.session.query(User).filter_by(id=user_id).first()
            if not user:
                return False

            # Set the owner_id to None to unlink
            library.owner_id = None
            self.db.session.commit()
            return True
        except SQLAlchemyError as e:
            self.db.session.rollback()
            self.app.logger.error(f"Failed to unlink library: {e}")
            return False
