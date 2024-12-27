"""
added library items

Revision ID: 2805fc99aa94
Revises: 3d9501782665
Create Date: 2024-12-27 13:29:41.474152
"""

from typing import Sequence, Union

from alembic import op
import sqlalchemy as sa


# revision identifiers, used by Alembic.
revision: str = '2805fc99aa94'
down_revision: Union[str, None] = '3d9501782665'
branch_labels: Union[str, Sequence[str], None] = None
depends_on: Union[str, Sequence[str], None] = None


def upgrade() -> None:
    """
    Upgrade database schema by creating new tables and modifying the 'users' table.
    This includes using batch_alter_table for SQLite compatibility.
    """
    # ### commands auto generated by Alembic - please adjust! ###
    op.create_table(
        'library',
        sa.Column('owner_id', sa.String(length=36), nullable=True),
        sa.Column('name', sa.String(length=150), nullable=False),
        sa.Column('description', sa.String(length=255), nullable=False),
        sa.Column('is_public', sa.Boolean(), nullable=False),
        sa.Column('created_at', sa.DateTime(), nullable=False),
        sa.Column('updated_at', sa.DateTime(), nullable=False),
        sa.Column('deleted_at', sa.DateTime(), nullable=True),
        sa.Column('id', sa.String(length=36), nullable=False),
        sa.ForeignKeyConstraint(['owner_id'], ['users.id']),
        sa.PrimaryKeyConstraint('id'),
        sa.UniqueConstraint('id'),
        sa.UniqueConstraint('name')
    )

    op.create_table(
        'libraryItems',
        sa.Column('owner_id', sa.String(length=36), nullable=True),
        sa.Column('library_id', sa.String(length=36), nullable=True),
        sa.Column('mime_type', sa.String(length=150), nullable=False),
        sa.Column('file_size', sa.Integer(), nullable=False),
        sa.Column('file_path', sa.String(length=255), nullable=False),
        sa.Column('is_public', sa.Boolean(), nullable=False),
        sa.Column('name', sa.String(length=150), nullable=False),
        sa.Column('description', sa.Text(length=255), nullable=True),
        sa.Column('raw_data', sa.Text(), nullable=True),
        sa.Column('created_at', sa.DateTime(), nullable=False),
        sa.Column('updated_at', sa.DateTime(), nullable=False),
        sa.Column('deleted_at', sa.DateTime(), nullable=True),
        sa.Column('id', sa.String(length=36), nullable=False),
        sa.ForeignKeyConstraint(['library_id'], ['library.id']),
        sa.ForeignKeyConstraint(['owner_id'], ['users.id']),
        sa.PrimaryKeyConstraint('id'),
        sa.UniqueConstraint('id'),
        sa.UniqueConstraint('name')
    )

    op.create_table(
        'thumbnails',
        sa.Column('owner_id', sa.String(length=36), nullable=True),
        sa.Column('library_id', sa.String(length=36), nullable=True),
        sa.Column('library_item_id', sa.String(length=36), nullable=True),
        sa.Column('mime_type', sa.String(length=150), nullable=False),
        sa.Column('file_size', sa.Integer(), nullable=False),
        sa.Column('file_path', sa.String(length=255), nullable=False),
        sa.Column('is_public', sa.Boolean(), nullable=False),
        sa.Column('name', sa.String(length=150), nullable=False),
        sa.Column('description', sa.Text(length=255), nullable=True),
        sa.Column('raw_data', sa.Text(), nullable=True),
        sa.Column('created_at', sa.DateTime(), nullable=False),
        sa.Column('updated_at', sa.DateTime(), nullable=False),
        sa.Column('deleted_at', sa.DateTime(), nullable=True),
        sa.Column('id', sa.String(length=36), nullable=False),
        sa.ForeignKeyConstraint(['library_id'], ['library.id']),
        sa.ForeignKeyConstraint(['library_item_id'], ['library.id']),
        sa.ForeignKeyConstraint(['owner_id'], ['users.id']),
        sa.PrimaryKeyConstraint('id'),
        sa.UniqueConstraint('id'),
        sa.UniqueConstraint('name')
    )

    # Use batch_alter_table for SQLite-compatible column modifications
    with op.batch_alter_table('users') as batch_op:
        # Add new column
        batch_op.add_column(sa.Column('deleted_at', sa.DateTime(), nullable=True))
        # Change is_confirmed to NOT NULL
        batch_op.alter_column(
            'is_confirmed',
            existing_type=sa.Boolean(),
            nullable=False
        )
    # ### end Alembic commands ###


def downgrade() -> None:
    """
    Downgrade database schema by removing newly created tables and reverting changes
    to the 'users' table.
    """
    # ### commands auto generated by Alembic - please adjust! ###
    with op.batch_alter_table('users') as batch_op:
        batch_op.alter_column(
            'is_confirmed',
            existing_type=sa.Boolean(),
            nullable=True
        )
        batch_op.drop_column('deleted_at')

    op.drop_table('thumbnails')
    op.drop_table('libraryItems')
    op.drop_table('library')
    # ### end Alembic commands ###