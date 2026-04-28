"""Operator config table (1 row, edit without redeploy).

Schema: agent.operator_config (id=1 PRIMARY KEY, version int, system_append text,
max_rounds int, history_budget_chars int, updated_at, updated_by).

If the table is missing or DSN absent, get_config() returns defaults so the agent
keeps working.
"""
from __future__ import annotations

import logging
from dataclasses import dataclass
from typing import Optional

import config as cfg

logger = logging.getLogger(__name__)


@dataclass
class OperatorConfig:
    version: int = 0
    system_append: str = ""
    max_rounds: int = cfg.MAX_TOOL_ROUNDS
    history_budget_chars: int = cfg.HISTORY_BUDGET_CHARS
    history_min_recent: int = cfg.HISTORY_MIN_RECENT


async def _connect():
    import asyncpg

    dsn = cfg.AGENT_DB_DSN
    if dsn.startswith("postgresql+asyncpg://"):
        dsn = dsn.replace("postgresql+asyncpg://", "postgresql://", 1)
    return await asyncpg.connect(dsn)


async def get_config() -> OperatorConfig:
    if not cfg.AGENT_DB_DSN:
        return OperatorConfig()
    try:
        conn = await _connect()
    except Exception as e:
        logger.warning("operator_config: connect failed: %s", e)
        return OperatorConfig()
    try:
        row = await conn.fetchrow(
            "SELECT version, system_append, max_rounds, history_budget_chars, history_min_recent "
            "FROM agent.operator_config WHERE id = 1"
        )
    except Exception as e:
        logger.warning("operator_config: query failed: %s", e)
        row = None
    finally:
        try:
            await conn.close()
        except Exception:
            pass
    if not row:
        return OperatorConfig()
    return OperatorConfig(
        version=int(row["version"] or 0),
        system_append=row["system_append"] or "",
        max_rounds=int(row["max_rounds"] or cfg.MAX_TOOL_ROUNDS),
        history_budget_chars=int(row["history_budget_chars"] or cfg.HISTORY_BUDGET_CHARS),
        history_min_recent=int(row["history_min_recent"] or cfg.HISTORY_MIN_RECENT),
    )


async def update_config(
    *,
    system_append: Optional[str] = None,
    max_rounds: Optional[int] = None,
    history_budget_chars: Optional[int] = None,
    history_min_recent: Optional[int] = None,
    expected_version: Optional[int] = None,
    updated_by: str = "operator",
) -> OperatorConfig:
    if not cfg.AGENT_DB_DSN:
        raise RuntimeError("AGENT_DB_DSN is not configured")
    conn = await _connect()
    try:
        cur = await conn.fetchrow(
            "SELECT version FROM agent.operator_config WHERE id = 1"
        )
        cur_version = int(cur["version"]) if cur else 0
        if expected_version is not None and cur_version != int(expected_version):
            raise RuntimeError(
                f"version conflict: expected {expected_version}, current {cur_version}"
            )
        new_version = cur_version + 1
        if cur:
            await conn.execute(
                """
                UPDATE agent.operator_config SET
                  version = $1,
                  system_append = COALESCE($2, system_append),
                  max_rounds = COALESCE($3, max_rounds),
                  history_budget_chars = COALESCE($4, history_budget_chars),
                  history_min_recent = COALESCE($5, history_min_recent),
                  updated_at = now(),
                  updated_by = $6
                WHERE id = 1
                """,
                new_version,
                system_append,
                max_rounds,
                history_budget_chars,
                history_min_recent,
                updated_by,
            )
        else:
            await conn.execute(
                """
                INSERT INTO agent.operator_config
                  (id, version, system_append, max_rounds, history_budget_chars,
                   history_min_recent, updated_at, updated_by)
                VALUES (1, $1, $2, $3, $4, $5, now(), $6)
                """,
                new_version,
                system_append or "",
                max_rounds or cfg.MAX_TOOL_ROUNDS,
                history_budget_chars or cfg.HISTORY_BUDGET_CHARS,
                history_min_recent or cfg.HISTORY_MIN_RECENT,
                updated_by,
            )
    finally:
        try:
            await conn.close()
        except Exception:
            pass
    return await get_config()
