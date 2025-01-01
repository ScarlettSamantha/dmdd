from typing import Optional
from flask import jsonify, request
from flask_restful import Resource


class SystemEventResource(Resource):
    def get(self, event_id: Optional[str] = None):
        if event_id:
            return jsonify(
                {
                    "status": "success",
                    "data": {"id": event_id, "description": f"Event {event_id}"},
                }
            )
        return jsonify(
            {
                "status": "success",
                "data": {
                    "events": [
                        {"id": "1", "description": "Event 1"},
                        {"id": "2", "description": "Event 2"},
                    ]
                },
            }
        )

    def post(self):
        new_event = request.json
        return jsonify(
            {"status": "success", "data": new_event, "message": "Event created"}
        ), 201

    def put(self, event_id: str):
        updated_data = request.json
        return jsonify(
            {
                "status": "success",
                "data": {"id": event_id, "updated": updated_data},
                "message": f"Event {event_id} updated",
            }
        )

    def delete(self, event_id: str):
        return jsonify(
            {"status": "success", "message": f"Event {event_id} deleted"}
        ), 204
