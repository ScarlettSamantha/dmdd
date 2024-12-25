from ..task import Task
import threading
import asyncio
        
class ExampleTask(Task):
    async def tick(self) -> None:
        threading.current_thread().name = f"Task-{self.name}"
        await asyncio.sleep(0.1)
        self.logger.info("Executing example task.")

    def health_check(self) -> str:
        return "ExampleTask is healthy."