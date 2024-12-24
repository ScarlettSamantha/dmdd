from flask import Flask
from flask_restful import Api, Resource

class StatusResource(Resource):
    def get(self):
        return {
            "status": "running",
            "message": "API is operational."
        }, 200


class APIHandler:
    def __init__(self, app: Flask) -> None:
        self.api = Api(app)
        self.setup_routes()

    def setup_routes(self) -> None:
        self.api.add_resource(StatusResource, '/status')