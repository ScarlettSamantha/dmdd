from sqlalchemy.exc import IntegrityError
from flask_sqlalchemy import SQLAlchemy
from models.user import User
from faker import Faker
import os

from seeders.library import (
    seed_libraries_for_user,
)  # Delegates also to seed_library_items_for_library


class UserSeeder:
    """
    Seeder class to populate the database with User, Library, and LibraryItem records.
    """

    def __init__(self, db: SQLAlchemy):
        self.db: SQLAlchemy = db
        self.faker: Faker = Faker()

    def run(self):
        """
        Seed the database with User, Library, and LibraryItem records.
        """
        # Fetch the default password from environment variables
        default_password = os.getenv("SEED_DEFAULT_PASSWORD", "default_password")
        password, salt = User._generate_password(default_password)

        # Create admin user
        admin_user = User(
            username="admin_user",
            email="admin@example.com",
            first_name="Admin",
            last_name="User",
            is_active=True,
            is_admin=True,
            password_hash=password,
            password_salt=salt,
        )

        try:
            self.db.session.add(admin_user)
            self.db.session.commit()
            seed_libraries_for_user(
                db=self.db, user=admin_user, faker=self.faker
            )  # Delegates also to seed_library_items_for_library
        except IntegrityError:
            self.db.session.rollback()

        # Create regular users
        for _ in range(10):
            user = User(
                username=self.faker.user_name(),
                email=self.faker.email(),
                first_name=self.faker.first_name(),
                last_name=self.faker.last_name(),
                is_active=self.faker.boolean(chance_of_getting_true=90),
                is_admin=False,
                password_hash=password,
                password_salt=salt,
            )

            try:
                self.db.session.add(user)
                self.db.session.commit()
                seed_libraries_for_user(
                    db=self.db, user=user, faker=self.faker
                )  # Delegates also to seed_library_items_for_library
            except IntegrityError:
                self.db.session.rollback()
