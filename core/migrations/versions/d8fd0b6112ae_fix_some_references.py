"""
fix some references

Revision ID: d8fd0b6112ae
Revises: 2805fc99aa94
Create Date: 2024-12-28 08:24:43.824879

"""
from typing import Sequence, Union

from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision: str = 'd8fd0b6112ae'
down_revision: Union[str, None] = '2805fc99aa94'
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    # Use batch mode for SQLite compatibility
    with op.batch_alter_table('thumbnails') as batch_op:
        batch_op.drop_column('library_item_id')


def downgrade() -> None:
    # Use batch mode to restore the column and foreign key
    with op.batch_alter_table('thumbnails') as batch_op:
        batch_op.add_column(sa.Column('library_item_id', sa.VARCHAR(length=36), nullable=True))
        batch_op.create_foreign_key(
            'fk_thumbnails_library_item_id',  # Explicit constraint name
            'library', 
            ['library_item_id'], 
            ['id']
        )
