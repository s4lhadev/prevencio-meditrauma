"""Tool-call audit log. Best-effort: errors are swallowed so they don't break a chat."""
from __future__ import annotations

import hashlib
import json
import logging
import time
from typing import Any, Dict, Optional

import config as cfg

logger = logging.getLogger(__name__)


def _hash_obj(o: Any) -> str:
    try:
        s = json.dumps(o, sort_keys=True, default=str)
    except Exception:
        s = str(o)
    return hashlib.sha256(s.encode("utf-8", errors="replace")).hexdigest()[:16]


async def _connect():
    import asyncpg

    dsn = cfg.AGENT_DB_DSN
    if not dsn:
        raise RuntimeError("no DSN")
    if dsn.startswith("postgresql+asyncpg://"):
        dsn = dsn.replace("postgresql+asyncpg://", "postgresql://", 1)
    return await asyncpg.connect(dsn)


async def log_tool_call(
    *,
    session_id: Optional[str],
    who: str,
    tier: str,
    tool: str,
    args: Dict[str, Any],
    result: Dict[str, Any],
    elapsed_ms: int,
) -> None:
    """Insert one audit row. Never raises."""
    if not cfg.AGENT_DB_DSN:
        return
    args_hash = _hash_obj(args)
    result_hash = _hash_obj(result)
    try:
        result_size = len(json.dumps(result, default=str))
    except Exception:
        result_size = -1
    ok = "error" not in result
    error_text = result.get("error") if isinstance(result, dict) else None
    try:
        args_preview = json.dumps(args, default=str)[:1000]
    except Exception:
        args_preview = ""
    try:
        conn = await _connect()
    except Exception as e:
        logger.warning("audit: connect failed: %s", e)
        return
    try:
        await conn.execute(
            """
            INSERT INTO agent.audit
              (session_id, who, tier, tool, args_hash, args_preview,
               result_hash, result_size_bytes, elapsed_ms, ok, error_text, created_at)
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, now())
            """,
            session_id,
            who,
            tier,
            tool,
            args_hash,
            args_preview,
            result_hash,
            int(result_size),
            int(elapsed_ms),
            bool(ok),
            (str(error_text)[:1000] if error_text else None),
        )
    except Exception as e:
        logger.warning("audit: insert failed (tool=%s): %s", tool, e)
    finally:
        try:
            await conn.close()
        except Exception:
            pass
