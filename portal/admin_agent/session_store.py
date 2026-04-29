"""Persistent sessions for the agent (server-of-record).

Schema lives in agent.session and agent.session_message (see migrations/001_agent_init.sql).
All ops are best-effort: if AGENT_DB_DSN is missing, every function silently no-ops so the
chat still works (just stateless).
"""
from __future__ import annotations

import json
import logging
import time
import uuid
from typing import Any, Dict, List, Optional

import config as cfg

logger = logging.getLogger(__name__)


def enabled() -> bool:
    return bool(cfg.AGENT_DB_DSN)


async def _connect():
    import asyncpg

    dsn = cfg.AGENT_DB_DSN
    if dsn.startswith("postgresql+asyncpg://"):
        dsn = dsn.replace("postgresql+asyncpg://", "postgresql://", 1)
    return await asyncpg.connect(dsn)


async def create_session(*, who: str, tier: str, title: str = "") -> Optional[str]:
    if not enabled():
        return None
    sid = str(uuid.uuid4())
    try:
        conn = await _connect()
    except Exception as e:
        logger.warning("create_session: db connect failed: %s", e)
        return None
    try:
        await conn.execute(
            """
            INSERT INTO agent.session (id, who, tier, title, created_at, updated_at)
            VALUES ($1, $2, $3, $4, now(), now())
            """,
            sid,
            who,
            tier,
            title or None,
        )
        return sid
    except Exception as e:
        logger.warning("create_session: insert failed: %s", e)
        return None
    finally:
        try:
            await conn.close()
        except Exception:
            pass


async def append_message(
    session_id: str,
    *,
    role: str,
    content: str,
    tool_calls: Optional[List[Dict[str, Any]]] = None,
    tool_call_id: Optional[str] = None,
    name: Optional[str] = None,
) -> None:
    """Persist a chat-completions style message."""
    if not enabled() or not session_id:
        return
    try:
        conn = await _connect()
    except Exception as e:
        logger.warning("append_message: db connect failed: %s", e)
        return
    try:
        seq_row = await conn.fetchrow(
            "SELECT COALESCE(MAX(seq), 0) + 1 AS next_seq FROM agent.session_message WHERE session_id = $1",
            session_id,
        )
        seq = int(seq_row["next_seq"]) if seq_row else 1
        await conn.execute(
            """
            INSERT INTO agent.session_message
              (session_id, seq, role, content, tool_calls, tool_call_id, name, created_at)
            VALUES ($1, $2, $3, $4, $5::jsonb, $6, $7, now())
            """,
            session_id,
            seq,
            role,
            content,
            json.dumps(tool_calls) if tool_calls else None,
            tool_call_id,
            name,
        )
        await conn.execute(
            "UPDATE agent.session SET updated_at = now() WHERE id = $1",
            session_id,
        )
    except Exception as e:
        logger.warning("append_message: insert failed: %s", e)
    finally:
        try:
            await conn.close()
        except Exception:
            pass


async def list_messages(session_id: str, *, limit: int = 5000) -> List[Dict[str, Any]]:
    if not enabled() or not session_id:
        return []
    try:
        conn = await _connect()
    except Exception as e:
        logger.warning("list_messages: db connect failed: %s", e)
        return []
    try:
        rows = await conn.fetch(
            """
            SELECT seq, role, content, tool_calls, tool_call_id, name, created_at
            FROM agent.session_message
            WHERE session_id = $1
            ORDER BY seq ASC
            LIMIT $2
            """,
            session_id,
            limit,
        )
    except Exception as e:
        logger.warning("list_messages: query failed: %s", e)
        return []
    finally:
        try:
            await conn.close()
        except Exception:
            pass
    out: List[Dict[str, Any]] = []
    for r in rows:
        item: Dict[str, Any] = {
            "seq": int(r["seq"]),
            "role": r["role"],
            "content": r["content"] or "",
            "created_at": r["created_at"].isoformat() if r["created_at"] else None,
        }
        tc = r["tool_calls"]
        if tc:
            try:
                item["tool_calls"] = json.loads(tc) if isinstance(tc, str) else tc
            except Exception:
                pass
        if r["tool_call_id"]:
            item["tool_call_id"] = r["tool_call_id"]
        if r["name"]:
            item["name"] = r["name"]
        out.append(item)
    return out


def history_for_openai(messages: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
    """Map persisted rows to chat-completions message dicts."""
    out: List[Dict[str, Any]] = []
    for m in messages:
        role = m.get("role")
        if role not in ("user", "assistant", "tool", "system"):
            continue
        entry: Dict[str, Any] = {"role": role, "content": m.get("content") or ""}
        if role == "assistant" and m.get("tool_calls"):
            entry["tool_calls"] = m["tool_calls"]
        if role == "tool":
            entry["tool_call_id"] = m.get("tool_call_id") or ""
            if m.get("name"):
                entry["name"] = m["name"]
        out.append(entry)
    return out


def strip_leading_orphan_tool_messages(messages: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
    """select_window() puede dejar un sufijo que empieza por role=tool sin el assistant padre."""
    out = list(messages)
    while out and out[0].get("role") == "tool":
        out.pop(0)
    return out


def select_window(
    messages: List[Dict[str, Any]],
    *,
    budget_chars: int = cfg.HISTORY_BUDGET_CHARS,
    min_recent: int = cfg.HISTORY_MIN_RECENT,
) -> List[Dict[str, Any]]:
    """Drop oldest entries when total exceeds budget; preserve last min_recent."""
    if not messages:
        return []
    keep: List[Dict[str, Any]] = []
    used = 0
    for entry in reversed(messages):
        cost = len(entry.get("content") or "") + 64
        if used + cost > budget_chars and len(keep) >= min_recent:
            break
        keep.append(entry)
        used += cost
    keep.reverse()
    return keep


async def list_sessions(*, who: Optional[str] = None, limit: int = 50) -> List[Dict[str, Any]]:
    if not enabled():
        return []
    try:
        conn = await _connect()
    except Exception as e:
        logger.warning("list_sessions: db connect failed: %s", e)
        return []
    try:
        if who:
            rows = await conn.fetch(
                """
                SELECT id, who, tier, title, created_at, updated_at
                FROM agent.session
                WHERE who = $1
                ORDER BY updated_at DESC
                LIMIT $2
                """,
                who,
                limit,
            )
        else:
            rows = await conn.fetch(
                """
                SELECT id, who, tier, title, created_at, updated_at
                FROM agent.session
                ORDER BY updated_at DESC
                LIMIT $1
                """,
                limit,
            )
    except Exception as e:
        logger.warning("list_sessions: query failed: %s", e)
        return []
    finally:
        try:
            await conn.close()
        except Exception:
            pass
    return [
        {
            "id": r["id"],
            "who": r["who"],
            "tier": r["tier"],
            "title": r["title"],
            "created_at": r["created_at"].isoformat() if r["created_at"] else None,
            "updated_at": r["updated_at"].isoformat() if r["updated_at"] else None,
        }
        for r in rows
    ]
