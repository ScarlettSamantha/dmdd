from faker import Faker
from flask_sqlalchemy import SQLAlchemy
from models.library import Library
from models.user import User
from sqlalchemy.exc import IntegrityError
from seeders.library_item import seed_library_items_for_library


def seed_libraries_for_user(db: SQLAlchemy, user: User, faker: Faker):
    """
    Generate libraries and library items for a given user.
    """
    for _ in range(faker.random_int(min=1, max=5)):
        library = Library(
            owner_id=user.id,
            name=faker.company(),
            description=faker.text(max_nb_chars=200),
            is_public=faker.boolean(chance_of_getting_true=75),
        )

        try:
            db.session.add(library)
            db.session.commit()
            seed_library_items_for_library(db=db, library=library, faker=faker)
        except IntegrityError:
            db.session.rollback()
