from faker import Faker
from flask_sqlalchemy import SQLAlchemy
from models.library import Library
from models.library_item import LibraryItem
from sqlalchemy.exc import IntegrityError


def seed_library_items_for_library(db: SQLAlchemy, library: Library, faker: Faker):
    """
    Generate library items for a given library.
    """
    for _ in range(faker.random_int(min=2, max=15)):
        library_item = LibraryItem(
            library_id=library.id,
            name=faker.word(),
            description=faker.text(max_nb_chars=150),
            mime_type=faker.mime_type(),
            file_size=faker.random_int(min=1024, max=10485760),  # 1 KB to 10 MB
            file_path=None
            if faker.boolean(chance_of_getting_true=30)
            else faker.file_path(),
            is_public=faker.boolean(chance_of_getting_true=70),
            owner_id=library.owner_id,
        )

        try:
            db.session.add(library_item)
            db.session.commit()
        except IntegrityError:
            db.session.rollback()
