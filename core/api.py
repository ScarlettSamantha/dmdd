from flask import Flask
from flask_restful import Api, Resource
from version import VERSION        
        
class VersionResource(Resource):
    def get(self):
        return {
            "version": f"{VERSION[0]}.{VERSION[1]}.{VERSION[2]}",
            "releaselevel": VERSION[3],
            "serial": VERSION[4]
        }, 200

class APIHandler:
    def __init__(self, app: Flask) -> None:
        self.api = Api(app)
        self.setup_routes()

    def setup_routes(self) -> None:
        self.api.add_resource(VersionResource, '/version')