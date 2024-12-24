import logging
import asyncio
import sqlalchemy
from datetime import datetime, timedelta
from typing import Dict, List, ForwardRef, Self
from tasks.task import Task
from tasks.example.main import ExampleTask
import setproctitle
import threading


class System:
    def __init__(self, logger: logging.Logger, db: sqlalchemy.engine.base.Engine) -> None:
        setproctitle.setproctitle("CoreDaemon-System")

        self.logger: logging.Logger = logger.getChild("System")
        self.db: sqlalchemy.engine.base.Engine = db
        self.tasks: Dict[datetime, List[Task]] = {}
        self.running_tasks: List[asyncio.Task] = []

        self.__register_tasks()

    def __register_tasks(self: Self) -> None:
        self.add_task(ExampleTask("ExampleTask", timedelta(seconds=10), logger=self.logger))

    def add_task(self, task: Task) -> None:
        """Add a task to the system."""
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
                        await self._wait_for_running_tasks()
                        self.logger.info(f"Executing blocking task: {task.name}.")
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

            await asyncio.sleep(1)

    async def _wait_for_running_tasks(self) -> None:
        """Wait for all running tasks to finish."""
        while self.running_tasks:
            self.logger.info(f"Waiting for {len(self.running_tasks)} running tasks to finish.")
            await asyncio.sleep(0.5)

    def _run_task_sync(self, task: Task) -> None:
        """Run a synchronous task."""
        threading.current_thread().name = f"Task-{task.name}"
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