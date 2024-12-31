import click
from flask import Flask
from flask_sqlalchemy import SQLAlchemy
from logging import Logger
from alembic import command, config as alembic_config
import logging
from colorama import Fore, Style
from datetime import datetime


class ColoredCLIHandler(logging.StreamHandler):
    """CLI handler that adds colors, emojis, and custom formatting to log messages."""

    LEVEL_COLORS = {
        logging.DEBUG: (Fore.CYAN, "✈️"),  # Airplane emoji
        logging.INFO: (Fore.GREEN, "✅"),  # Checkmark emoji
        logging.WARNING: (Fore.YELLOW, "⚠️"),  # Warning emoji
        logging.ERROR: (Fore.RED, "❌"),  # Cross Mark emoji
        logging.CRITICAL: (Fore.MAGENTA, "🔥"),  # Fire emoji
    }

    def emit(self, record: logging.LogRecord) -> None:
        color, emoji = self.LEVEL_COLORS.get(record.levelno, (Style.RESET_ALL, ""))
        timestamp = datetime.fromtimestamp(record.created).strftime(
            "%d-%m %H:%M:%S:%f"
        )[:-3]
        formatted_message = (
            f"[{emoji}|{timestamp}] {color}{record.name}{Style.RESET_ALL}: {record.msg}"
        )
        record.msg = formatted_message
        super().emit(record)


class CLICommands:
    def __init__(self, app: Flask, db: SQLAlchemy, logger: Logger) -> None:
        self.app = app
        self.db = db
        self.logger = logger
        self.register_commands()

    def register_commands(self) -> None:
        """Register custom CLI commands for Flask."""

        @click.argument("username")
        @click.argument("email")
        @click.argument("password")
        @self.app.cli.command("user-create")
        def user_create(username: str, email: str, password: str):
            """Create a new user."""
            from repositories.user_repository import UserRepository

            user_repo = UserRepository(self.db, self.app)
            user_repo.register_user(username, email, password)
            self.logger.info(f"User {username} created successfully.")

        @click.argument("username")
        @self.app.cli.command("user-set-password")
        def user_set_password(username: str):
            """Set password for a user."""
            from repositories.user_repository import UserRepository

            user_repo = UserRepository(self.db, self.app)
            user = user_repo.find_by_username(username)
            if user:
                password = click.prompt(
                    "Enter new password", hide_input=True, confirmation_prompt=True
                )
                user.set_password(password)
                user_repo.update(user)
                self.logger.info(f"Password for {username} updated successfully.")
            else:
                self.logger.error(f"User {username} not found.")

        @click.argument("username")
        @self.app.cli.command("user-activate")
        def user_activate(username: str):
            """Activate a user account."""
            from repositories.user_repository import UserRepository

            user_repo = UserRepository(self.db, self.app)
            user = user_repo.find_by_username(username)
            if user:
                user_repo.activate_user(user)
                self.logger.info(f"User {username} activated successfully.")
            else:
                self.logger.error(f"User {username} not found.")

        @self.app.cli.command("db-init")
        def db_init():
            """Initialize the migration directory."""
            with self.app.app_context():
                alembic_cfg = alembic_config.Config("migrations/alembic.ini")
                alembic_cfg.set_main_option(
                    "sqlalchemy.url", self.app.config["SQLALCHEMY_DATABASE_URI"]
                )
                command.init(alembic_cfg, "migrations")
                self.logger.info("Migration directory initialized.")

        @self.app.cli.command("db-migrate")
        def db_migrate():
            """Generate a new migration."""
            with self.app.app_context():
                alembic_cfg = alembic_config.Config("migrations/alembic.ini")
                alembic_cfg.set_main_option(
                    "sqlalchemy.url", self.app.config["SQLALCHEMY_DATABASE_URI"]
                )
                command.revision(
                    alembic_cfg, autogenerate=True, message="Generate migration"
                )
                self.logger.info("Migration script created.")

        @self.app.cli.command("db-upgrade")
        def db_upgrade():
            """Apply migrations."""
            with self.app.app_context():
                alembic_cfg = alembic_config.Config("migrations/alembic.ini")
                alembic_cfg.set_main_option(
                    "sqlalchemy.url", self.app.config["SQLALCHEMY_DATABASE_URI"]
                )
                command.upgrade(alembic_cfg, "head")
                self.logger.info("Database upgraded successfully.")

        @self.app.cli.command("db-downgrade")
        def db_downgrade():
            """Revert migrations."""
            with self.app.app_context():
                alembic_cfg = alembic_config.Config("migrations/alembic.ini")
                alembic_cfg.set_main_option(
                    "sqlalchemy.url", self.app.config["SQLALCHEMY_DATABASE_URI"]
                )
                command.downgrade(alembic_cfg, "-1")
                self.logger.info("Database downgraded successfully.")

        @self.app.cli.command("db-seed")
        @click.option("--models", "-m", multiple=True, help="Specific models to seed.")
        @click.option(
            "--all", "-a", is_flag=True, help="Seed all models.", default=False
        )
        @click.option(
            "--stop-on-error",
            "-s",
            is_flag=True,
            help="Stop on first error.",
            default=False,
        )
        def db_seed(models: list[str], all: bool, stop_on_error: bool):
            """Seed the database."""
            from seeders.user import UserSeeder

            with self.app.app_context():
                user_seeder = UserSeeder(self.db)

                try:
                    user_seeder.run()
                    self.logger.info("Seeding completed successfully.")
                except Exception as e:
                    self.logger.error(f"Error during seeding: {e}")
                    if stop_on_error:
                        raise e
