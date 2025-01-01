from typing import Dict, Optional, Any, Union, List
from flask import jsonify, Response
from flask_restful import Resource as BaseFlaskResource
import logging
import os


class Resource(BaseFlaskResource):
    def __init__(
        self, *args: Any, logger: Optional[logging.Logger] = None, **kwargs: Any
    ):
        """
        Initialize the Resource class with optional arguments, including a logger.
        """
        super().__init__(*args, **kwargs)
        class_name = self.__class__.__name__
        self.logger = logger or logging.getLogger(f"{__name__}.{class_name}")
        self.logger.info(
            "%s initialized with args: %s, kwargs: %s", class_name, args, kwargs
        )

    def make_response(
        self,
        data: Any,
        status_code: int = 200,
        headers: Optional[Dict[str, Any]] = None,
    ) -> Response:
        """
        Ensure responses are always JSON serializable and compatible with Flask's Response.
        """
        if isinstance(data, (dict, list)):
            response = jsonify(data)
        elif isinstance(data, (str, int, float, bool, type(None))):
            # Directly support primitives that are JSON-compatible
            response = jsonify(data=data)
        else:
            self.logger.error("Non-serializable response data provided.")
            response = Response("Invalid response data", mimetype="text/plain")
            status_code = 500

        response.status_code = status_code

        if headers:
            response.headers.extend(headers)

        return response

    def success_response(
        self,
        data: Optional[Union[str, List[Any], Dict[Any, Any]]] = None,
        message: Optional[str] = None,
        status_code: int = 200,
        headers: Optional[Dict[str, str]] = None,
    ) -> Response:
        response_data: Dict[str, Any] = {"status": "success"}
        if data:
            response_data["data"] = data
        if message:
            response_data["message"] = message
        return self.make_response(response_data, status_code, headers)

    def failure_response(
        self,
        message: Optional[str] = None,
        errors: Optional[Union[Dict, List]] = None,
        error_code: Optional[int] = None,
        status_code: int = 400,
        headers: Optional[Dict[str, str]] = None,
    ) -> Response:
        if not message:
            message = "Your request has failed."
        response_data: Dict[str, Any] = {
            "status": "error",
            "message": message,
            "error_code": error_code or status_code,
        }
        if errors:
            response_data["errors"] = errors
        return self.make_response(response_data, status_code, headers)

    def exception_response(
        self, exception: Exception, status_code: int = 500
    ) -> Response:
        self.logger.error("Exception occurred", exc_info=True)
        debug_mode = os.getenv("FLASK_DEBUG", False)
        response_data: Dict[str, Any] = {"status": "error"}

        if debug_mode:
            response_data["message"] = str(exception)
        else:
            response_data["message"] = "An internal server error occurred."

        return self.make_response(response_data, status_code)
