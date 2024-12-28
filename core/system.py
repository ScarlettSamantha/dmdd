
import os
import inspect
import logging
import asyncio
import threading
import sqlalchemy
import setproctitle
import importlib.util
from datetime import datetime, timedelta
from typing import Dict, List, ForwardRef, Self, Type

from tasks.task import Task

class System:
    INIT_FILE_NAME = "__init__.py"
    TASK_CLASS_VARIABLE = "__TASK_CLASS__"
    DEFAULT_RUN_INTERVAL_SECONDS = 10
    TASK_THREAD_NAME_PREFIX = "Task-"
    SLEEP_INTERVAL_SECONDS = 1
    STOP_WAIT_INTERVAL_SECONDS = 0.5
    DEFAULT_TASKS_FOLDER = "tasks"
    PROCESS_NAME = "CoreDaemon-System"
    LOGGER_CHILD = "System"

    def __init__(self, app, logger: logging.Logger, db: sqlalchemy.engine.base.Engine, tasks_folder: str = None) -> None:
        setproctitle.setproctitle(self.PROCESS_NAME)
        
        if tasks_folder is None:
            tasks_folder = self.DEFAULT_TASKS_FOLDER
            
        self.app = app
        self.logger: logging.Logger = logger.getChild(self.LOGGER_CHILD)
        self.db: sqlalchemy.engine.base.Engine = db
        self.tasks_folder: str = tasks_folder

        self.tasks: Dict[datetime, List[Task]] = {}
        self.running_tasks: List[asyncio.Task] = []

        self.__register_tasks()

    def __register_tasks(self: Self) -> None:
        self.discover_tasks()

    def discover_tasks(self) -> None:
        """Discover all tasks in the tasks folder."""
        tasks_path: str = os.path.join(os.path.dirname(__file__), self.tasks_folder)
        
        for folder_name in os.listdir(tasks_path):
            folder_path: str = os.path.join(tasks_path, folder_name)
            
            if not os.path.isdir(folder_path):
                continue
            
            if folder_name.startswith("__") or folder_name.startswith("."):
                continue
            
            if folder_name.lower() == "__pycache__":
                continue

            init_file: str = os.path.join(folder_path, self.INIT_FILE_NAME)
            if not os.path.exists(init_file):
                self.logger.warning(f"Skipping {folder_name}: No {self.INIT_FILE_NAME} found.")
                continue

            spec: importlib.machinery.ModuleSpec = importlib.util.spec_from_file_location(
                f"tasks.{folder_name}", init_file
            )
            module = importlib.util.module_from_spec(spec)

            try:
                spec.loader.exec_module(module)  # Load the module dynamically
            except Exception as e:
                import traceback
                self.logger.error(f"Failed to load module {folder_name}: {traceback.format_exc()}")
                continue

            if not hasattr(module, self.TASK_CLASS_VARIABLE):
                self.logger.warning(f"Skipping {folder_name}: No {self.TASK_CLASS_VARIABLE} variable found.")
                continue

            task_class: Type = getattr(module, self.TASK_CLASS_VARIABLE)
            if not inspect.isclass(task_class) or not issubclass(task_class, Task):
                self.logger.warning(f"Skipping {folder_name}: {self.TASK_CLASS_VARIABLE} is not a subclass of Task.")
                continue

            try:
                task_instance: Task = task_class(
                    name=folder_name,
                    run_interval=timedelta(seconds=self.DEFAULT_RUN_INTERVAL_SECONDS),
                    app=self.app,
                    logger=self.logger,
                    db=self.db
                )
                self.add_task(task_instance, first_call=True)
                self.logger.info(f"Task {task_instance.name} from {folder_name} registered successfully.")
            except Exception as e:
                self.logger.error(f"Failed to instantiate task {folder_name}: {e}")

    def add_task(self, task: Task, first_call: bool = False) -> None:
        """Add a task to the system."""
        try:
            if first_call and hasattr(task, "first_call") and callable(getattr(task, "first_call")):
                self.logger.info(f"Executing first call for task {task.name}.")
                task.first_call()  # This is a onload task
        except Exception as e:
            self.logger.error(f"Task {task.name} first_call failed with error: {e}")

        if task.next_run not in self.tasks:
            self.tasks[task.next_run] = []
        self.tasks[task.next_run].append(task)
        self.logger.info(f"Task {task.name} registered to run at {task.next_run}.")

    async def tick(self, core_daemon: ForwardRef("CoreDaemon")) -> None:
        """Entry point for periodic tasks."""
        while core_daemon.running:
            now = datetime.now()
            tasks_to_run = [time for time in self.tasks if time <= now]

            for run_time in tasks_to_run:
                for task in self.tasks[run_time]:
                    if task.is_blocking:
                        self.logger.info(f"Blocking task detected: {task.name}. Waiting for running tasks to finish.")
                        self.logger.info(f"Executing blocking task: {task.name}.")
                        await self._wait_for_running_tasks()
                        try:
                            await task.tick()
                        except Exception as e:
                            self.logger.error(f"Blocking task {task.name} failed with error: {e}")
                        task.update_next_run()
                        self.add_task(task)
                    else:
                        self.logger.info(f"Running task: {task.name}.")
                        try:
                            if asyncio.iscoroutinefunction(task.tick):
                                task_instance = asyncio.create_task(task.tick())
                                self.running_tasks.append(task_instance)
                                task_instance.add_done_callback(self._task_done)
                            else:
                                self._run_task_sync(task)
                        except Exception as e:
                            self.logger.error(f"Task {task.name} failed with error: {e}")
                        task.update_next_run()
                        self.add_task(task)

                del self.tasks[run_time]

            await asyncio.sleep(self.SLEEP_INTERVAL_SECONDS)

    async def stop(self) -> None:
        """Stop the system and notify all tasks."""
        self.logger.info("Stopping system and notifying all tasks.")
        
        for run_time, task_list in self.tasks.items():
            for task in task_list:
                if hasattr(task, "stop") and callable(getattr(task, "stop")):
                    self.logger.info(f"Calling stop for task {task.name}.")
                    try:
                        await task.stop()
                    except Exception as e:
                        self.logger.error(f"Task {task.name} stop failed with error: {e}")

        await self._wait_for_running_tasks()
        self.logger.info("System stopped.")

    async def _wait_for_running_tasks(self) -> None:
        """Wait for all running tasks to finish."""
        while self.running_tasks:
            self.logger.info(f"Waiting for {len(self.running_tasks)} running tasks to finish.")
            await asyncio.sleep(self.STOP_WAIT_INTERVAL_SECONDS)

    def _run_task_sync(self, task: Task) -> None:
        """Run a synchronous task."""
        threading.current_thread().name = f"{self.TASK_THREAD_NAME_PREFIX}{task.name}"
        loop = asyncio.get_event_loop()
        loop.run_until_complete(task.tick())

    def _task_done(self, task: asyncio.Task) -> None:
        """Callback for when an async task is done."""
        if task in self.running_tasks:
            self.running_tasks.remove(task)

    def health_check(self) -> str:
        """Check the health of all tasks and return their status."""
        statuses = []
        completed_tasks = [task for task in self.running_tasks if task.done()]
        for task_list in self.tasks.values():
            for task in task_list:
                try:
                    status = task.health_check()
                    statuses.append(f"{task.name}: {status}")
                except Exception as e:
                    statuses.append(f"{task.name}: Health check failed with error: {e}")

        statuses.append(f"Running tasks: {len(self.running_tasks)}")
        for running_task in self.running_tasks:
            statuses.append(f"Running Task: {running_task.get_name()} State: {'Done' if running_task.done() else 'Running'}")

        return "\n".join(statuses)
