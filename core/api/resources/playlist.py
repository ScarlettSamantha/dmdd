from typing import Optional
from flask import jsonify, request
from flask_restful import Resource


class PlaylistResource(Resource):
    def get(self, playlist_id: Optional[str] = None):
        if playlist_id:
            return jsonify(
                {
                    "status": "success",
                    "data": {"id": playlist_id, "name": f"Playlist {playlist_id}"},
                }
            )
        return jsonify(
            {
                "status": "success",
                "data": {
                    "playlists": [
                        {"id": "1", "name": "Playlist 1"},
                        {"id": "2", "name": "Playlist 2"},
                    ]
                },
            }
        )

    def post(self):
        new_playlist = request.json
        return jsonify(
            {"status": "success", "data": new_playlist, "message": "Playlist created"}
        ), 201

    def put(self, playlist_id: str):
        updated_data = request.json
        return jsonify(
            {
                "status": "success",
                "data": {"id": playlist_id, "updated": updated_data},
                "message": "Playlist updated",
            }
        )

    def delete(self, playlist_id: str):
        return jsonify(
            {"status": "success", "message": f"Playlist {playlist_id} deleted"}
        ), 204
