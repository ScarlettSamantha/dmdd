from typing import Optional
from flask import jsonify, request
from flask_restful import Resource


class PlaylistItemResource(Resource):
    def get(self, playlist_id: str, item_id: Optional[str] = None):
        if item_id:
            return jsonify(
                {
                    "status": "success",
                    "data": {
                        "id": item_id,
                        "name": f"Item {item_id} in Playlist {playlist_id}",
                    },
                }
            )
        return jsonify(
            {
                "status": "success",
                "data": {
                    "items": [
                        {"id": "1", "name": "Item 1"},
                        {"id": "2", "name": "Item 2"},
                    ]
                },
            }
        )

    def post(self, playlist_id: str):
        new_item = request.json
        return jsonify(
            {
                "status": "success",
                "data": new_item,
                "message": f"Item added to Playlist {playlist_id}",
            }
        ), 201

    def put(self, playlist_id: str, item_id: str):
        updated_data = request.json
        return jsonify(
            {
                "status": "success",
                "data": {"id": item_id, "updated": updated_data},
                "message": f"Item {item_id} updated in Playlist {playlist_id}",
            }
        )

    def delete(self, playlist_id: str, item_id: str):
        return jsonify(
            {
                "status": "success",
                "message": f"Item {item_id} deleted from Playlist {playlist_id}",
            }
        ), 204
