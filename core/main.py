# Core stuff imports
import os
import sys
import click
import signal
import logging
import asyncio
from flask import Flask
from flask_restful import Api
from dotenv import load_dotenv
from typing import Optional, Any
from colorama import Fore, Style, init
from flask_sqlalchemy import SQLAlchemy
from logging.handlers import RotatingFileHandler
from alembic import command, config as alembic_config

# application imports
from api import APIHandler
from system import System

from models.model import BaseModel

# Initialize colorama for colored CLI output
init(autoreset=True)  # Initialize colorama

def ensure_path_exists(path: str) -> None:
    """Ensure a directory exists."""
    os.makedirs(path, exist_ok=True)

class CoreDaemon:
    app: Flask
    api: Api
    db_path: str
    log_path: str
    db_engine: Optional[Any]
    db_session: Optional[Any]
    running: bool

    def __init__(self) -> None:
        load_dotenv()

        self.app = Flask(__name__)
        self.db_path = os.getenv("DB_PATH", "sqlite:////app/instance/./db.sqlite3")
        self.log_path = os.getenv("LOG_PATH", "./tmp/core_daemon.log")
        self.db_engine = None
        self.db_session = None
        self.running = False
        self.shutting_down: bool = False

        self.app.config['SQLALCHEMY_DATABASE_URI'] = self.db_path
        self.app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

        self.db = SQLAlchemy(model_class=BaseModel)
        self.db.init_app(self.app)

        self.setup_logging()
        self.logger = logging.getLogger("CoreDaemon")

        self.logger.info("Initializing CoreDaemon.")
        self.setup_database()
        self.setup_signal_handling()

        self.api_handler: APIHandler = APIHandler(self.app, self.db)
        self.register_cli_commands()

        self.system = System(self.app, self.logger, self.db)
        self.echo_configuration()

    def setup_logging(self) -> None:
        """Set up logging with file and CLI handlers."""
        ensure_path_exists(os.path.dirname(self.log_path))

        # Create a rotating file handler
        file_handler = RotatingFileHandler(self.log_path, maxBytes=10 * 1024 * 1024, backupCount=3)
        file_handler.setFormatter(logging.Formatter('%(asctime)s - %(levelname)s - %(message)s'))
        file_handler.setLevel(logging.DEBUG)

        # Create a CLI handler with colored output
        class ColoredCLIHandler(logging.StreamHandler):
            LEVEL_COLORS = {
                logging.DEBUG: Fore.CYAN,
                logging.INFO: Fore.GREEN,
                logging.WARNING: Fore.YELLOW,
                logging.ERROR: Fore.RED,
                logging.CRITICAL: Fore.MAGENTA,
            }

            def emit(self, record: logging.LogRecord) -> None:
                color = self.LEVEL_COLORS.get(record.levelno, Style.RESET_ALL)
                record.msg = f"{color}{record.msg}{Style.RESET_ALL}"
                super().emit(record)

        cli_handler = ColoredCLIHandler()
        cli_handler.setFormatter(logging.Formatter('%(levelname)s - %(message)s'))
        cli_handler.setLevel(logging.DEBUG)

        # Configure root logger
        logging.basicConfig(level=logging.DEBUG, handlers=[file_handler, cli_handler])

    def setup_database(self) -> None:
        """Set up SQLite database connection."""
        try:
            with self.app.app_context():
                self.db.create_all()
            self.logger.info("Database setup complete.")
        except Exception as e:
            self.logger.error(f"Failed to initialize database: {e}")
            sys.exit(1)

    def setup_signal_handling(self) -> None:
        """Set up signal handling for graceful shutdown and info."""
        signal.signal(signal.SIGINT, self.graceful_shutdown)
        signal.signal(signal.SIGTERM, self.graceful_shutdown)
        try:
            signal.signal(signal.SIGUSR2, self.print_status)
        except AttributeError:
            pass

    def print_status(self, *args: Any) -> None:
        """Print the current status of the daemon."""
        self.logger.info("Received status request (SIGINFO).")
        print(f"Daemon is running: {self.running}")
        print(f"Database Path: {self.db_path}")
        print(f"Log Path: {self.log_path}")

    def echo_configuration(self) -> None:
        """Echo the current configuration to the CLI."""
        print("Configuration:")
        print(f"Database Path: {self.db_path}")
        print(f"Log Path: {self.log_path}")
        print(f"Running: {self.running}")

    def run(self) -> None:
        """Start the daemon."""
        self.running = True
        self.logger.info("CoreDaemon is starting.")

        asyncio.run(self.start_async_components())

    async def start_async_components(self) -> None:
        """Start async components alongside the Flask app."""
        task = asyncio.create_task(self.system.tick(self))

        try:
            await asyncio.to_thread(self.app.run, host=os.getenv("FLASK_HOST", "0.0.0.0"), port=int(os.getenv("FLASK_PORT", 5000)), use_reloader=False)
        except Exception as e:
            self.logger.error(f"Unexpected error: {e}")
        finally:
            self.running = False
            task.cancel()
            await asyncio.gather(task, return_exceptions=True)
            self.graceful_shutdown()

    def graceful_shutdown(self, *args: Any) -> None:
        """Shutdown gracefully, releasing resources."""
        if self.shutting_down:
            return
        self.shutting_down = True

        self.logger.info("Shutting down CoreDaemon gracefully.")
        self.running = False

        loop = asyncio.get_event_loop()
        if loop.is_running():
            self.logger.info("Stopping the asyncio event loop.")
            loop.stop()

        pending_tasks = asyncio.all_tasks(loop=loop)
        for task in pending_tasks:
            task.cancel()
            try:
                loop.run_until_complete(task)
            except asyncio.CancelledError:
                pass

        try:
            loop.close()
            self.logger.info("Asyncio event loop closed.")
        except RuntimeError as e:
            self.logger.warning(f"Error during async loop shutdown: {e}")

        self.logger.info("CoreDaemon has shut down.")
        sys.exit(0)

    def register_cli_commands(self) -> None:
        """Register custom CLI commands for flask-migrate."""
            
        @click.argument("username")
        @click.argument("email")
        @click.argument("password")
        @self.app.cli.command("user-create")
        def user_create(username: str, email: str, password: str):
            """Create a new user with the provided username, email, and password."""
            from repositories.user_repository import UserRepository
            user_repo = UserRepository(self.db, self.app)
            user_repo.register_user(username, email, password)
            self.logger.info(f"User {username} created successfully.")
            
        @click.argument("username")
        @self.app.cli.command("user-set-password")
        def user_set_password(username: str):
            """Set the password for a user."""
            from repositories.user_repository import UserRepository
            user_repo = UserRepository(self.db, self.app)
            user = user_repo.find_by_username(username)
            if user:
                password = click.prompt("Enter the new password", hide_input=True, confirmation_prompt=True)
                user.set_password(password)
                user_repo.update(user)
                self.logger.info(f"Password for user {username} set successfully.")
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
                
                
        @click.argument("username")
        @self.app.cli.command("user-confirm")
        def user_confirm(username: str):
            """Confirm a user account."""
            from repositories.user_repository import UserRepository
            user_repo = UserRepository(self.db, self.app)
            user = user_repo.find_by_username(username)
            if user:
                user_repo.confirm_user(user)
                self.logger.info(f"User {username} confirmed successfully.")
            else:
                self.logger.error(f"User {username} not found.")
        
        @self.app.cli.command("db-init")
        def db_init():
            """Initialize the migration directory."""
            import models  # Import models dynamically

            with self.app.app_context():
                init()
                self.logger.info("Migration directory initialized.")

        @self.app.cli.command("db-migrate")
        def db_migrate():
            """Generate a new migration."""
            with self.app.app_context():
                alembic_cfg = alembic_config.Config(file_="migrations/alembic.ini")
                alembic_cfg.set_main_option("sqlalchemy.url", self.app.config['SQLALCHEMY_DATABASE_URI'])
                command.revision(alembic_cfg, autogenerate=True, message="Generate migration")
                self.logger.info("Migration script created.")

        @self.app.cli.command("db-upgrade")
        def db_upgrade():
            """Apply migrations."""
            with self.app.app_context():
                alembic_cfg = alembic_config.Config(file_="migrations/alembic.ini")
                alembic_cfg.set_main_option("sqlalchemy.url", self.app.config['SQLALCHEMY_DATABASE_URI'])
                command.upgrade(alembic_cfg, "head")
                self.logger.info("Database upgraded successfully.")

        @self.app.cli.command("db-downgrade")
        def db_downgrade():
            """Revert migrations."""
            with self.app.app_context():
                alembic_cfg = alembic_config.Config(file_="migrations/alembic.ini")
                alembic_cfg.set_main_option("sqlalchemy.url", self.app.config['SQLALCHEMY_DATABASE_URI'])
                command.downgrade(alembic_cfg, "-1")
                self.logger.info("Database downgraded successfully.")

# Entry point for the core daemon and docker container
if __name__ == "__main__":
    import argparse

    parser = argparse.ArgumentParser(description="CoreDaemon")
    parser.add_argument("--db-path", type=str, help="Path to the SQLite database file.")
    parser.add_argument("--log-path", type=str, help="Path to the log file.")
    parser.add_argument("--flask-host", type=str, help="Host for Flask.")
    parser.add_argument("--flask-port", type=int, help="Port for Flask.")
    parser.add_argument("command", type=str, nargs="?", choices=["migrate", "upgrade", "downgrade"], help="Database migration commands.")

    args = parser.parse_args()

    if args.db_path:
        os.environ["DB_PATH"] = args.db_path
    if args.log_path:
        os.environ["LOG_PATH"] = args.log_path
    if args.flask_host:
        os.environ["FLASK_HOST"] = args.flask_host
    if args.flask_port:
        os.environ["FLASK_PORT"] = str(args.flask_port)

    daemon = CoreDaemon()

    if args.command == "migrate":
        daemon.db_migrate()
    elif args.command == "upgrade":
        daemon.db_upgrade()
    elif args.command == "downgrade":
        daemon.db_downgrade()
    else:
        daemon.run()
        
# this is the entry point for the flask cli utility
else:
    daemon = CoreDaemon()
    # flask helpers
    db = daemon.db
    app = daemon.app