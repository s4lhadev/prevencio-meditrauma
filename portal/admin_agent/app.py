"""Admin assistant API — local only (127.0.0.1).

Endpoints:
  GET   /health                       — quick check + secret fingerprint
  POST  /v1/chat                     — chat + optional codebase RAG (primary UI)
  GET   /v1/index/status              — codebase index status
  POST  /v1/reindex                   — reindex codebase
  GET   /v1/sessions                  — list recent sessions
  POST  /v1/sessions                  — create a new session
  GET   /v1/sessions/{sid}/messages   — list persisted messages
  GET   /v1/operator-config           — current operator config
  PUT   /v1/operator-config           — update operator config (with optimistic version)

Auth: every protected endpoint requires header X-Admin-Agent-Secret matching env.
Optional tiers on session/operator endpoints via X-Admin-Agent-Tier (user|dev).
"""
from __future__ import annotations

import logging
import os
from pathlib import Path
from typing import Any, Dict, List, Optional

from dotenv import load_dotenv
import httpx
from fastapi import FastAPI, Header, HTTPException
from pydantic import BaseModel, Field

import config as cfg
import operator_config as opcfg
import session_store
from agent_loop import assistant_message_text
from codebase_index import get_index

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

_env_path = Path(__file__).resolve().parent / ".env"
if _env_path.is_file():
    load_dotenv(_env_path, override=True)
else:
    logger.warning(
        "Missing %s — relying on process environment (systemd EnvironmentFile, export, etc.)",
        _env_path,
    )

# Re-evaluate after env load (config module captured initial values; this is a soft refresh
# for the secret which we use for fingerprint logging at boot).
_admin_secret = (os.getenv("ADMIN_AGENT_SECRET") or "").strip()
if not _admin_secret:
    logger.warning(
        "ADMIN_AGENT_SECRET is empty — every protected request will return 401."
    )
else:
    logger.info("ADMIN_AGENT_SECRET loaded (%s)", cfg.secret_fingerprint(_admin_secret))


OPENROUTER_URL = "https://openrouter.ai/api/v1/chat/completions"

app = FastAPI(title="MDT Admin Agent (Prevencion)", version="2.0.0")


# --- auth ----------------------------------------------------------------------------
def _require_secret(x_admin_agent_secret: Optional[str]) -> None:
    expected = (os.getenv("ADMIN_AGENT_SECRET") or "").strip()
    if not expected or (x_admin_agent_secret or "").strip() != expected:
        raise HTTPException(401, "Invalid or missing X-Admin-Agent-Secret")


def _resolve_tier(header_value: Optional[str]) -> str:
    v = (header_value or "").strip().lower()
    return "dev" if v == "dev" else "user"


def _openrouter_key() -> str:
    k = (os.getenv("OPENROUTER_API_KEY") or "").strip()
    if not k:
        raise HTTPException(503, "OPENROUTER_API_KEY is not configured")
    return k


# --- pydantic models -----------------------------------------------------------------
class SessionCreateRequest(BaseModel):
    title: Optional[str] = None


class OperatorConfigUpdateBody(BaseModel):
    expected_version: Optional[int] = None
    system_append: Optional[str] = None
    max_rounds: Optional[int] = None
    history_budget_chars: Optional[int] = None
    history_min_recent: Optional[int] = None
    updated_by: Optional[str] = "operator"


# --- /v1/chat: RAG one-shot, no tools -------------------------------------------------
class LegacyChatRequest(BaseModel):
    message: str = Field(..., min_length=1, max_length=50000)
    messages: Optional[List[Dict[str, Any]]] = None
    use_codebase: bool = True


class ReindexRequest(BaseModel):
    full: bool = False


# --- endpoints ------------------------------------------------------------------------
@app.get("/health")
def health() -> Dict[str, Any]:
    s = (os.getenv("ADMIN_AGENT_SECRET") or "").strip()
    return {
        "status": "ok",
        "product": (os.getenv("APP_PRODUCT") or "prevencion").strip() or "prevencion",
        "secret_fingerprint": cfg.secret_fingerprint(s),
        "model": cfg.OPENROUTER_MODEL,
        "shell_disabled": cfg.SHELL_DISABLED,
        "agent_db_configured": bool(cfg.AGENT_DB_DSN),
    }


@app.post("/v1/chat")
async def legacy_chat(
    body: LegacyChatRequest,
    x_admin_agent_secret: Optional[str] = Header(default=None, alias="X-Admin-Agent-Secret"),
) -> Dict[str, Any]:
    """Chat with optional semantic codebase context (RAG). No tool calling — one model round-trip."""
    _require_secret(x_admin_agent_secret)
    api_key = _openrouter_key()
    model = (os.getenv("OPENROUTER_MODEL_LEGACY") or "openai/gpt-4o-mini").strip()
    system = (
        "You are the Prevencion admin assistant. Answer in the user's language, concise, "
        "professional. Do not invent data; cite paths when referring to files.\n\n"
        "**This HTTP mode is simple:** you may receive optional codebase snippets from "
        "semantic search (see ## Context below), but you do **not** receive live tool "
        "calls — no sql_execute, run_shell, read_log, http_request, or symfony_console. "
        "If the user asks for live DB/VM/log access, explain honestly that you only have "
        "the pasted code context here; suggest they run diagnostics on the server or use "
        "their usual admin tooling."
    )
    messages: List[Dict[str, Any]] = [{"role": "system", "content": system}]
    if body.messages:
        for m in body.messages[-24:]:
            r = (m.get("role") or "user").strip()
            c = (m.get("content") or "").strip()
            if c and r in ("user", "assistant", "system"):
                messages.append({"role": r, "content": c})
    if body.use_codebase:
        try:
            hits = await get_index().search(body.message, api_key, top_k=8)
            if hits:
                rag = "\n\n## Context (semantic code search):\n\n" + "\n\n---\n\n".join(
                    f"### {h['path']}\n{h['chunk']}" for h in hits
                )
                messages[0]["content"] = messages[0]["content"] + rag
        except Exception as e:
            logger.warning("legacy chat RAG skipped: %s", e)
    messages.append({"role": "user", "content": body.message})
    payload = {"model": model, "messages": messages, "temperature": 0.2}
    headers = {
        "Authorization": f"Bearer {api_key}",
        "Content-Type": "application/json",
        "HTTP-Referer": cfg.OPENROUTER_HTTP_REFERER,
        "X-Title": "prevencion-admin-agent-legacy",
    }
    async with httpx.AsyncClient(timeout=httpx.Timeout(120.0, connect=15.0)) as client:
        try:
            r = await client.post(OPENROUTER_URL, json=payload, headers=headers)
        except httpx.RequestError as e:
            raise HTTPException(502, f"LLM request failed: {e!s}") from e
    if r.status_code >= 400:
        raise HTTPException(502, f"LLM error {r.status_code}: {(r.text or '')[:1000]}")
    data = r.json()
    msg = (data.get("choices") or [{}])[0].get("message") or {}
    text = assistant_message_text(msg).strip() or "(vacío del modelo)"
    return {"reply": text, "model": model, "mode": "legacy"}


# --- codebase index ------------------------------------------------------------------
@app.get("/v1/index/status")
def index_status(
    x_admin_agent_secret: Optional[str] = Header(default=None, alias="X-Admin-Agent-Secret"),
) -> Dict[str, Any]:
    _require_secret(x_admin_agent_secret)
    st = get_index().status()
    st["ok"] = True
    return st


@app.post("/v1/reindex")
async def reindex(
    body: ReindexRequest,
    x_admin_agent_secret: Optional[str] = Header(default=None, alias="X-Admin-Agent-Secret"),
) -> Dict[str, Any]:
    _require_secret(x_admin_agent_secret)
    api_key = _openrouter_key()
    try:
        return await get_index().reindex(api_key, full=bool(body.full))
    except Exception as e:
        logger.exception("reindex failed")
        raise HTTPException(500, str(e)) from e


# --- sessions ------------------------------------------------------------------------
@app.get("/v1/sessions")
async def list_sessions_ep(
    who: Optional[str] = None,
    limit: int = 50,
    x_admin_agent_secret: Optional[str] = Header(default=None, alias="X-Admin-Agent-Secret"),
) -> Dict[str, Any]:
    _require_secret(x_admin_agent_secret)
    rows = await session_store.list_sessions(who=who, limit=max(1, min(200, int(limit))))
    return {"sessions": rows, "store_enabled": session_store.enabled()}


@app.post("/v1/sessions")
async def create_session_ep(
    body: SessionCreateRequest,
    x_admin_agent_secret: Optional[str] = Header(default=None, alias="X-Admin-Agent-Secret"),
    x_admin_agent_tier: Optional[str] = Header(default="user", alias="X-Admin-Agent-Tier"),
    x_admin_agent_who: Optional[str] = Header(default="anon", alias="X-Admin-Agent-Who"),
) -> Dict[str, Any]:
    _require_secret(x_admin_agent_secret)
    sid = await session_store.create_session(
        who=(x_admin_agent_who or "anon").strip()[:200] or "anon",
        tier=_resolve_tier(x_admin_agent_tier),
        title=body.title or "",
    )
    if not sid:
        raise HTTPException(503, "session store not available (AGENT_DB_DSN missing or db down)")
    return {"session_id": sid}


@app.get("/v1/sessions/{sid}/messages")
async def list_session_messages_ep(
    sid: str,
    limit: int = 5000,
    x_admin_agent_secret: Optional[str] = Header(default=None, alias="X-Admin-Agent-Secret"),
) -> Dict[str, Any]:
    _require_secret(x_admin_agent_secret)
    rows = await session_store.list_messages(sid, limit=max(1, min(20000, int(limit))))
    return {"session_id": sid, "messages": rows, "count": len(rows)}


# --- operator config -----------------------------------------------------------------
@app.get("/v1/operator-config")
async def get_operator_cfg(
    x_admin_agent_secret: Optional[str] = Header(default=None, alias="X-Admin-Agent-Secret"),
) -> Dict[str, Any]:
    _require_secret(x_admin_agent_secret)
    op = await opcfg.get_config()
    return {
        "version": op.version,
        "system_append": op.system_append,
        "max_rounds": op.max_rounds,
        "history_budget_chars": op.history_budget_chars,
        "history_min_recent": op.history_min_recent,
    }


@app.put("/v1/operator-config")
async def put_operator_cfg(
    body: OperatorConfigUpdateBody,
    x_admin_agent_secret: Optional[str] = Header(default=None, alias="X-Admin-Agent-Secret"),
    x_admin_agent_tier: Optional[str] = Header(default="user", alias="X-Admin-Agent-Tier"),
) -> Dict[str, Any]:
    _require_secret(x_admin_agent_secret)
    if _resolve_tier(x_admin_agent_tier) != "dev":
        raise HTTPException(403, "operator config requires dev tier")
    try:
        op = await opcfg.update_config(
            system_append=body.system_append,
            max_rounds=body.max_rounds,
            history_budget_chars=body.history_budget_chars,
            history_min_recent=body.history_min_recent,
            expected_version=body.expected_version,
            updated_by=body.updated_by or "operator",
        )
    except Exception as e:
        raise HTTPException(409, str(e)) from e
    return {
        "version": op.version,
        "system_append": op.system_append,
        "max_rounds": op.max_rounds,
        "history_budget_chars": op.history_budget_chars,
        "history_min_recent": op.history_min_recent,
    }
