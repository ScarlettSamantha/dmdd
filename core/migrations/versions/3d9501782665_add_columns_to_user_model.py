"""add columns to user model

Revision ID: 3d9501782665
Revises: c629e537b8c9
Create Date: 2024-12-26 19:41:33.793030

"""
from typing import Sequence, Union

from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision: str = '3d9501782665'
down_revision: Union[str, None] = 'c629e537b8c9'
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    op.add_column('users', sa.Column('is_confirmed', sa.Boolean(), nullable=True, default=False))


def downgrade() -> None:
    pass
