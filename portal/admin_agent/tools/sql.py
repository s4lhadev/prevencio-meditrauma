"""sql_schema + sql_execute. Read-only PG role, denylist for PII tables, hard limits."""
from __future__ import annotations

import logging
import re
import time
from typing import Any, Dict, List, Optional, Tuple

from . import Tool, ToolContext, register
import config as cfg

logger = logging.getLogger(__name__)


# --- DSN parsing for asyncpg ----------------------------------------------------------
def _normalized_dsn() -> str:
    dsn = cfg.AGENT_DB_DSN
    if not dsn:
        return ""
    # asyncpg does not accept SQLAlchemy-style prefix.
    if dsn.startswith("postgresql+asyncpg://"):
        dsn = dsn.replace("postgresql+asyncpg://", "postgresql://", 1)
    return dsn


async def _connect():
    import asyncpg

    dsn = _normalized_dsn()
    if not dsn:
        raise RuntimeError("AGENT_DB_DSN is not configured")
    return await asyncpg.connect(dsn)


def _json_safe(v: Any) -> Any:
    if v is None or isinstance(v, (str, int, float, bool)):
        return v
    return str(v)


# --- denylist ----------------------------------------------------------------------------
def _is_denylisted_table(name: str) -> bool:
    n = (name or "").lower()
    for pat in cfg.SQL_DENYLIST_PATTERNS:
        if pat.lower() in n:
            return True
    return False


_TABLE_REF_RE = re.compile(
    r'(?:from|join|update|into)\s+("?[a-zA-Z_][a-zA-Z0-9_]*"?\s*\.\s*)?"?([a-zA-Z_][a-zA-Z0-9_]*)"?',
    re.IGNORECASE,
)


def _extract_table_refs(sql: str) -> List[str]:
    """Best-effort extraction of table identifiers from a SQL string.
    Used only for denylist defense-in-depth (the agent_ro role is the real barrier).
    """
    out: List[str] = []
    for m in _TABLE_REF_RE.finditer(sql):
        out.append(m.group(2))
    return out


def _validate_select_only(sql: str) -> Optional[str]:
    """Return error message if SQL is not a safe read-only statement."""
    s = sql.strip().rstrip(";").strip()
    if not s:
        return "empty SQL"
    head = s.split(None, 1)[0].upper()
    if head not in ("SELECT", "WITH", "EXPLAIN", "SHOW", "TABLE", "VALUES"):
        return f"only SELECT/WITH/EXPLAIN/SHOW/TABLE/VALUES allowed (got {head})"
    # Block multi-statement (extra ';' followed by content)
    if ";" in s and any(part.strip() for part in s.split(";")[1:]):
        return "multi-statement SQL is not allowed"
    # Crude write-keyword check (defensive; the role already forbids writes)
    bad_kw = re.compile(
        r"\b(insert|update|delete|drop|alter|create|truncate|grant|revoke|copy)\b",
        re.IGNORECASE,
    )
    if bad_kw.search(s):
        return "write/DDL keywords are not allowed in sql_execute"
    return None


# --- schema digest cache -------------------------------------------------------------
_DIGEST_CACHE: Dict[str, Any] = {"text": None, "ts": 0.0}


async def _build_schema_digest() -> str:
    conn = await _connect()
    try:
        await conn.execute(f"SET statement_timeout = '{cfg.SQL_STATEMENT_TIMEOUT_S}s'")
        rows = await conn.fetch(
            """
            SELECT table_schema, table_name, column_name, data_type
            FROM information_schema.columns
            WHERE table_schema NOT IN ('pg_catalog', 'information_schema')
            ORDER BY table_schema, table_name, ordinal_position
            """
        )
    finally:
        await conn.close()

    by_table: Dict[Tuple[str, str], List[str]] = {}
    for r in rows:
        key = (r["table_schema"], r["table_name"])
        by_table.setdefault(key, []).append(f"{r['column_name']}:{r['data_type']}")

    lines: List[str] = []
    for (schema, table), cols in sorted(by_table.items()):
        marker = " [DENYLIST]" if _is_denylisted_table(table) else ""
        lines.append(f'"{schema}"."{table}"{marker} ({", ".join(cols)})')
    return "\n".join(lines)


async def get_schema_digest(force_refresh: bool = False) -> str:
    now = time.time()
    if (
        not force_refresh
        and _DIGEST_CACHE["text"] is not None
        and (now - float(_DIGEST_CACHE["ts"])) < cfg.SCHEMA_DIGEST_TTL_S
    ):
        return _DIGEST_CACHE["text"]
    text = await _build_schema_digest()
    _DIGEST_CACHE["text"] = text
    _DIGEST_CACHE["ts"] = now
    return text


# --- tools ---------------------------------------------------------------------------
SCHEMA_SQL_SCHEMA: Dict[str, Any] = {
    "type": "function",
    "function": {
        "name": "sql_schema",
        "description": (
            "Live PostgreSQL schema digest (information_schema), cached. "
            "Tables marked [DENYLIST] are blocked at sql_execute (PII, medical data) — do not query them. "
            "Use before sql_execute when unsure of table or column names."
        ),
        "parameters": {
            "type": "object",
            "properties": {
                "refresh": {
                    "type": "boolean",
                    "description": "Bypass cache (force re-query). Use after DDL changes only.",
                    "default": False,
                },
            },
        },
    },
}


async def _run_sql_schema(args: Dict[str, Any], ctx: ToolContext) -> Dict[str, Any]:
    try:
        digest = await get_schema_digest(bool(args.get("refresh")))
    except Exception as e:
        logger.exception("sql_schema failed")
        return {"error": str(e)}
    return {
        "digest": digest,
        "denylist_patterns": cfg.SQL_DENYLIST_PATTERNS,
        "max_rows_per_query": cfg.MAX_SQL_ROWS,
    }


SCHEMA_SQL_EXECUTE: Dict[str, Any] = {
    "type": "function",
    "function": {
        "name": "sql_execute",
        "description": (
            "PostgreSQL SELECT (read-only role agent_ro). "
            f"Hard cap {cfg.MAX_SQL_ROWS} rows, {cfg.SQL_STATEMENT_TIMEOUT_S}s timeout. "
            "Schema-qualify tables (use sql_schema first if unsure). "
            "Tables matching denylist patterns (informe_medico, historia_clinica, paciente, "
            "vigilancia, aptitud, reconocimiento) are blocked — do NOT try to bypass. "
            "Use aggregated/anonymized views (agent.v_*) when you need stats over PII."
        ),
        "parameters": {
            "type": "object",
            "properties": {
                "sql": {
                    "type": "string",
                    "description": "Single SELECT/WITH/EXPLAIN statement, no trailing semicolons or multiple stmts.",
                },
            },
            "required": ["sql"],
        },
    },
}


async def _run_sql_execute(args: Dict[str, Any], ctx: ToolContext) -> Dict[str, Any]:
    sql = (args.get("sql") or "").strip()
    err = _validate_select_only(sql)
    if err:
        return {"error": err}
    refs = _extract_table_refs(sql)
    blocked = [r for r in refs if _is_denylisted_table(r)]
    if blocked:
        return {
            "error": (
                f"denylisted table(s) referenced: {sorted(set(blocked))}. "
                f"Use anonymized views or contact data protection."
            ),
            "denylist_patterns": cfg.SQL_DENYLIST_PATTERNS,
        }
    try:
        conn = await _connect()
    except Exception as e:
        return {"error": f"db connect failed: {e}"}
    try:
        await conn.execute(f"SET statement_timeout = '{cfg.SQL_STATEMENT_TIMEOUT_S}s'")
        rows = await conn.fetch(sql)
    except Exception as e:
        return {"error": str(e)}
    finally:
        try:
            await conn.close()
        except Exception:
            pass
    if not rows:
        return {"columns": [], "rows": [], "row_count": 0}
    cols = list(rows[0].keys())
    capped = rows[: cfg.MAX_SQL_ROWS]
    data = [{k: _json_safe(v) for k, v in dict(r).items()} for r in capped]
    return {
        "columns": cols,
        "rows": data,
        "row_count": len(rows),
        "truncated": len(rows) > cfg.MAX_SQL_ROWS,
    }


register(Tool(name="sql_schema", schema=SCHEMA_SQL_SCHEMA, run=_run_sql_schema, tier="user"))
register(Tool(name="sql_execute", schema=SCHEMA_SQL_EXECUTE, run=_run_sql_execute, tier="dev"))
