from flask import jsonify
from flask_restful import Resource


class VersionResource(Resource):
    def get(self):
        return jsonify(
            {
                "status": "success",
                "data": {
                    "version": "0.0.1",
                    "releaselevel": "alpha",
                    "serial": "2025-01-01",
                },
            }
        )
