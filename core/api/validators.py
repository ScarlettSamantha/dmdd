from typing import Any, Callable, Dict, Iterable, List, Optional, Type, Union
from models.model import T


class InputValidator:
    @staticmethod
    def verify_input(
        input_data: Optional[Union[Dict[Any, Any], Any]],
        model: Union[Type[T], Dict[str, Type]],
        exclude_internal: bool = True,
        type_validation: Callable[[object, Type], bool] = lambda value,
        expected_type: isinstance(value, expected_type),
    ) -> List[str]:
        """
        Verify input data matches the model fields and types or a provided schema, including nested validation.

        :param input_data: The data to verify
        :param model: The SQLAlchemy model or a dictionary defining required fields and types
        :param exclude_internal: If True, excludes internal columns (e.g., primary key, timestamps)
        :param type_validation: A custom callable for type validation
        :return: A list of validation error messages
        """
        errors = []
        internal_columns = (
            ["id", "created_at", "updated_at"] if exclude_internal else []
        )

        def get_allowed_api_fields(model_class: Type[T]) -> Optional[Iterable[str]]:
            """
            Retrieve the allowed API fields for the given model.

            :param model_class: The SQLAlchemy model class
            :return: An iterable of allowed field names
            """
            if not hasattr(model_class, "get_allowed_fields"):
                return None

            allowed_fields: Optional[Iterable[str]] = getattr(
                model_class, "get_allowed_fields"
            )

            if not allowed_fields:
                # Default to all column names if __ALLOWED_API_FIELDS__ is None or empty
                return [column.name for column in model_class.__table__.columns]
            return allowed_fields

        def validate_field(value: Any, expected_type: Any, field_name: str) -> None:
            """Perform validation for a single field, including nested objects."""
            if isinstance(expected_type, dict):
                # Recursive validation for nested dictionaries
                if not isinstance(value, dict):
                    errors.append(f"Field '{field_name}' should be a dictionary.")
                else:
                    nested_errors = InputValidator.verify_input(
                        value, expected_type, exclude_internal, type_validation
                    )
                    errors.extend([f"{field_name}.{err}" for err in nested_errors])
            elif isinstance(expected_type, list):
                # Validation for lists of a specific type
                if not isinstance(value, list):
                    errors.append(f"Field '{field_name}' should be a list.")
                else:
                    for idx, item in enumerate(value):
                        for subtype in expected_type:
                            if not type_validation(item, subtype):
                                errors.append(
                                    f"Field '{field_name}[{idx}]' should be of type '{subtype.__name__}'."
                                )
            else:
                # Basic type validation
                if not type_validation(value, expected_type):
                    errors.append(
                        f"Field '{field_name}' should be of type '{expected_type.__name__}'."
                    )

        if isinstance(model, dict):
            # Validate against a provided dictionary schema
            for field, field_type in model.items():
                if not isinstance(input_data, dict) or field not in input_data:
                    errors.append(f"Field '{field}' is required.")
                    continue

                validate_field(input_data[field], field_type, field)

            if input_data is not None:
                for key in input_data.keys():
                    if key not in model:
                        errors.append(
                            f"Field '{key}' is not valid for the provided schema."
                        )
        else:
            # Validate against SQLAlchemy model
            allowed_fields: Optional[Iterable[str]] = get_allowed_api_fields(model)

            for column in model.__table__.columns:
                column_name: str = column.name

                if column_name in internal_columns or column_name not in allowed_fields:
                    continue

                if column_name not in input_data:
                    if not column.nullable and column.default is None:
                        errors.append(f"Field '{column_name}' is required.")
                    continue

                value: Optional[str] = input_data[column_name] if input_data else None

                # Type validation
                expected_type = column.type.python_type
                validate_field(value, expected_type, column_name)

            # Additional validation for unexpected fields
            for key in input_data.keys() if input_data is not None else []:
                if (
                    key not in [column.name for column in model.__table__.columns]
                    or key not in allowed_fields
                ):
                    errors.append(
                        f"Field '{key}' is not valid for model '{model.__name__}'."
                    )

        return errors
