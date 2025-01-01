from sqlalchemy.exc import IntegrityError
from flask_sqlalchemy import SQLAlchemy
from models.user import User
from models.library import Library
from models.library_item import LibraryItem
from faker import Faker


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
        # Generate a password for all users
        password, salt = User._generate_password("default_password")

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
            self._create_libraries_for_user(admin_user)
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
                self._create_libraries_for_user(user)
            except IntegrityError:
                self.db.session.rollback()

    def _create_libraries_for_user(self, user: User):
        """
        Generate libraries and library items for a given user.
        """
        for _ in range(self.faker.random_int(min=1, max=5)):
            library = Library(
                owner_id=user.id,
                name=self.faker.company(),
                description=self.faker.text(max_nb_chars=200),
                is_public=self.faker.boolean(chance_of_getting_true=75),
            )

            try:
                self.db.session.add(library)
                self.db.session.commit()
                self._create_library_items_for_library(library)
            except IntegrityError:
                self.db.session.rollback()

    def _create_library_items_for_library(self, library: Library):
        """
        Generate library items for a given library.
        """
        for _ in range(self.faker.random_int(min=2, max=15)):
            library_item = LibraryItem(
                library_id=library.id,
                name=self.faker.word(),
                description=self.faker.text(max_nb_chars=150),
                mime_type=self.faker.mime_type(),
                file_size=self.faker.random_int(
                    min=1024, max=10485760
                ),  # 1 KB to 10 MB
                file_path=None
                if self.faker.boolean(chance_of_getting_true=30)
                else self.faker.file_path(),
                is_public=self.faker.boolean(chance_of_getting_true=70),
                owner_id=library.owner_id,
            )

            try:
                self.db.session.add(library_item)
                self.db.session.commit()
            except IntegrityError:
                self.db.session.rollback()
