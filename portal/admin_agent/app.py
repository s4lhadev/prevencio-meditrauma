"""Admin assistant API — local only (127.0.0.1).

Endpoints:
  GET   /health                       — quick check + secret fingerprint
  POST  /v1/chat                     — RAG (una ida) o agentico con tools (JSON único, sin SSE)
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

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

_env_path = Path(__file__).resolve().parent / ".env"


def _log_agent_env_diagnostics(dotenv_path: Path) -> None:
    """Sin volcar secretos: ayuda cuando Infisical/deploy no deja AGENT_DB_DSN efectivo."""
    if not dotenv_path.is_file():
        return
    try:
        raw = dotenv_path.read_text(encoding="utf-8-sig", errors="replace")
    except OSError as e:
        logger.warning("admin_agent: no se puede leer %s (%s); el usuario del servicio ¿permiso?", dotenv_path, e)
        return
    found_line = False
    value_non_empty = False
    for line in raw.splitlines():
        s = line.strip()
        if not s or s.startswith("#"):
            continue
        if s.lower().startswith("export "):
            s = s[7:].strip()
        if s.startswith("AGENT_DB_DSN=") or s.startswith("AGENT_DATABASE_DSN="):
            found_line = True
            _, _, rhs = s.partition("=")
            v = rhs.strip().strip('"').strip("'")
            value_non_empty = bool(v)
            break
    dsn_after_dotenv = bool((os.getenv("AGENT_DB_DSN") or "").strip() or (os.getenv("AGENT_DATABASE_DSN") or "").strip())
    logger.info(
        "admin_agent .env path=%s size=%s AGENT_DB_DSN line in file=%s file value non-empty=%s "
        "os.environ after load_dotenv=%s",
        dotenv_path,
        len(raw),
        found_line,
        value_non_empty if found_line else None,
        dsn_after_dotenv,
    )
    if found_line and value_non_empty and not dsn_after_dotenv:
        logger.error(
            "admin_agent: .env declares AGENT_DB_DSN pero load_dotenv no la aplicó "
            "(¿línea rota, comillas mal, o variable AGENT_DB_DSN vacía también en el proceso?)."
        )
    if not found_line:
        keys = []
        for line in raw.splitlines():
            s = line.strip()
            if not s or s.startswith("#") or "=" not in s:
                continue
            if s.lower().startswith("export "):
                s = s[7:].strip()
            k = s.split("=", 1)[0].strip().replace("\ufeff", "")
            if k and k not in keys:
                keys.append(k)
        keys.sort()
        preview = ", ".join(keys[:35]) + ("…" if len(keys) > 35 else "")
        logger.warning(
            "admin_agent: no hay AGENT_DB_DSN= en %s; añádela en Infisical (clave exacta AGENT_DB_DSN) "
            "o alias AGENT_DATABASE_DSN. Claves presentes en .env (solo nombres): %s",
            dotenv_path,
            preview or "(ninguna con formato KEY=)",
        )


if _env_path.is_file():
    load_dotenv(_env_path, override=True)
else:
    logger.warning(
        "Missing %s — relying on process environment (systemd EnvironmentFile, export, etc.)",
        _env_path,
    )

_log_agent_env_diagnostics(_env_path)

# config (and modules that import it) must load only after .env is applied, or
# AGENT_DB_DSN and other module-level settings stay empty forever.
import httpx
from fastapi import FastAPI, Header, HTTPException
from pydantic import BaseModel, Field

import config as cfg
import operator_config as opcfg
import session_store
from agent_loop import assistant_message_text, collect_agent_turn
from codebase_index import get_index

_admin_secret = (os.getenv("ADMIN_AGENT_SECRET") or "").strip()
if not _admin_secret:
    logger.warning(
        "ADMIN_AGENT_SECRET is empty — every protected request will return 401."
    )
else:
    logger.info("ADMIN_AGENT_SECRET loaded (%s)", cfg.secret_fingerprint(_admin_secret))

logger.info("AGENT_DB_DSN configured in process=%s", bool(cfg.AGENT_DB_DSN))


OPENROUTER_URL = "https://openrouter.ai/api/v1/chat/completions"

app = FastAPI(title="MDT Admin Agent (Prevencion)", version="2.0.0")


# --- auth ----------------------------------------------------------------------------
def _require_secret(x_admin_agent_secret: Optional[str]) -> None:
    expected = (os.getenv("ADMIN_AGENT_SECRET") or "").strip()
    if not expected:
        raise HTTPException(
            503,
            "ADMIN_AGENT_SECRET not set — ensure admin_agent/.env or systemd EnvironmentFile.",
        )
    if (x_admin_agent_secret or "").strip() != expected:
        raise HTTPException(401, "Invalid or missing X-Admin-Agent-Secret")


def _resolve_tier(header_value: Optional[str]) -> str:
    v = (header_value or "").strip().lower()
    return "dev" if v == "dev" else "user"


def _openrouter_key() -> str:
    k = (os.getenv("OPENROUTER_API_KEY") or "").strip()
    if not k:
        raise HTTPException(503, "OPENROUTER_API_KEY is not configured")
    return k


def _header_truthy(val: Optional[str]) -> bool:
    return (val or "").strip().lower() in ("1", "true", "yes", "on")


# --- pydantic models -----------------------------------------------------------------
class SessionCreateRequest(BaseModel):
    title: Optional[str] = None


class OperatorConfigUpdateBody(BaseModel):
    expected_version: Optional[int] = None
    system_append: Optional[str] = None
    max_rounds: Optional[int] = None
    max_tool_rounds: Optional[int] = None
    history_budget_chars: Optional[int] = None
    history_min_recent: Optional[int] = None
    openrouter_model: Optional[str] = None
    temperature: Optional[float] = None
    updated_by: Optional[str] = "operator"


class OperatorConfigFlatBody(BaseModel):
    """Alias Medisalut /v1/operator/config (mismo secreto; sin versión obligatoria)."""

    max_tool_rounds: Optional[int] = Field(None, ge=1, le=64)
    openrouter_model: Optional[str] = Field(None, max_length=500)
    system_append: Optional[str] = Field(None, max_length=50000)
    temperature: Optional[float] = Field(None, ge=0.0, le=2.0)
    history_budget_chars: Optional[int] = Field(None, ge=1000, le=500_000)
    history_min_recent: Optional[int] = Field(None, ge=1, le=500)
    expected_version: Optional[int] = None


# --- /v1/chat: RAG one-shot o agentico (tools, sin streaming HTTP) --------------------
class LegacyChatRequest(BaseModel):
    message: str = Field(..., min_length=1, max_length=50000)
    messages: Optional[List[Dict[str, Any]]] = None
    use_codebase: bool = True
    agentic: bool = False
    session_id: Optional[str] = None
    create_session: bool = True
    title: Optional[str] = None
    model: Optional[str] = Field(None, max_length=500)


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
    x_admin_agent_tier: Optional[str] = Header(default="user", alias="X-Admin-Agent-Tier"),
    x_admin_agent_who: Optional[str] = Header(default="anon", alias="X-Admin-Agent-Who"),
    x_admin_agent_agentic: Optional[str] = Header(default=None, alias="X-Admin-Agent-Agentic"),
) -> Dict[str, Any]:
    """Modo legacy: RAG + una ida al modelo. Modo agentico: bucle con herramientas, misma respuesta JSON."""
    _require_secret(x_admin_agent_secret)
    api_key = _openrouter_key()
    product = (os.getenv("APP_PRODUCT") or "prevencion").strip() or "prevencion"

    use_agentic = bool(body.agentic) or _header_truthy(x_admin_agent_agentic)
    logger.info(
        "v1/chat use_agentic=%s body.agentic=%s header X-Admin-Agent-Agentic=%r",
        use_agentic,
        body.agentic,
        x_admin_agent_agentic,
    )

    if use_agentic:
        tier = _resolve_tier(x_admin_agent_tier)
        who = (x_admin_agent_who or "anon").strip()[:200] or "anon"
        sid = (body.session_id or "").strip() or None
        if not sid and body.create_session and session_store.enabled():
            sid = await session_store.create_session(who=who, tier=tier, title=body.title or "")
        op = await opcfg.get_config()
        mo = (body.model or "").strip() or (op.openrouter_model or "").strip() or None
        out = await collect_agent_turn(
            user_message=body.message,
            tier=tier,
            session_id=sid,
            who=who,
            openrouter_api_key=api_key,
            model_override=mo,
        )
        out["product"] = product
        return out

    model = (os.getenv("OPENROUTER_MODEL_LEGACY") or "openai/gpt-4o-mini").strip()
    system = (
        "Eres el asistente de administración de Prevención. Responde en el idioma del usuario. "
        "Sé breve, profesional y práctico.\n\n"
        "Cuando exista la sección «## Context (semantic code search)», úsala: enlaza rutas de "
        "archivo y explica con ese código. No inventes secretos, ni datos de producción, ni "
        "estado en vivo que no salga del mensaje ni del contexto pegado.\n\n"
        "Prioridad: resolver la pregunta con rutas, configuración y lógica del repositorio. "
        "Si hace falta comprobar algo en un servidor, propón comandos o pasos concretos que un "
        "admin podría ejecutar (infórmalos a partir del código cuando sea posible).\n\n"
        "Prohibición explícita: no empieces la respuesta con negativas tipo «no tengo acceso a la VM», "
        "«no tengo acceso a SQL», «no puedo acceder a sistemas externos» ni variantes, salvo que "
        "el usuario pregunte únicamente si tú ejecutas órdenes en su infraestructura: en ese caso "
        "una sola frase breve (que tú no ejecutas nada en su servidor) y seguidamente la ayuda "
        "práctica a su tarea."
    )
    messages: List[Dict[str, Any]] = [{"role": "system", "content": system}]
    if body.messages:
        for m in body.messages[-24:]:
            r = (m.get("role") or "user").strip()
            c = (m.get("content") or "").strip()
            if c and r in ("user", "assistant", "system"):
                messages.append({"role": r, "content": c})
    codebase_hits = 0
    if body.use_codebase:
        try:
            hits = await get_index().search(body.message, api_key, top_k=8)
            codebase_hits = len(hits) if hits else 0
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
    return {
        "reply": text,
        "model": model,
        "mode": "legacy",
        "product": product,
        "codebase_hits": codebase_hits,
    }


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
@app.get("/v1/operator/config")
async def get_operator_cfg(
    x_admin_agent_secret: Optional[str] = Header(default=None, alias="X-Admin-Agent-Secret"),
) -> Dict[str, Any]:
    _require_secret(x_admin_agent_secret)
    op = await opcfg.get_config()
    return {
        "version": op.version,
        "system_append": op.system_append,
        "max_rounds": op.max_rounds,
        "max_tool_rounds": op.max_rounds,
        "openrouter_model": op.openrouter_model,
        "temperature": op.temperature,
        "history_budget_chars": op.history_budget_chars,
        "history_min_recent": op.history_min_recent,
        "env_model_default": cfg.OPENROUTER_MODEL,
        "env_max_tool_rounds_cap": cfg.MAX_TOOL_ROUNDS,
        "store": "postgres" if cfg.AGENT_DB_DSN else "none",
    }


@app.put("/v1/operator/config")
async def put_operator_cfg_slash(
    body: OperatorConfigFlatBody,
    x_admin_agent_secret: Optional[str] = Header(default=None, alias="X-Admin-Agent-Secret"),
) -> Dict[str, Any]:
    _require_secret(x_admin_agent_secret)
    patch = body.model_dump(exclude_unset=True)
    exp = patch.pop("expected_version", None)
    try:
        await opcfg.update_config(
            system_append=patch.get("system_append"),
            max_rounds=patch.get("max_tool_rounds"),
            history_budget_chars=patch.get("history_budget_chars"),
            history_min_recent=patch.get("history_min_recent"),
            openrouter_model=patch.get("openrouter_model"),
            temperature=patch.get("temperature"),
            expected_version=exp,
            updated_by="operator",
        )
    except RuntimeError as e:
        msg = str(e)
        if "version conflict" in msg:
            raise HTTPException(409, msg) from e
        raise HTTPException(503, msg) from e
    return await get_operator_cfg(x_admin_agent_secret=x_admin_agent_secret)


@app.put("/v1/operator-config")
async def put_operator_cfg(
    body: OperatorConfigUpdateBody,
    x_admin_agent_secret: Optional[str] = Header(default=None, alias="X-Admin-Agent-Secret"),
) -> Dict[str, Any]:
    _require_secret(x_admin_agent_secret)
    mr = body.max_rounds if body.max_rounds is not None else body.max_tool_rounds
    try:
        op = await opcfg.update_config(
            system_append=body.system_append,
            max_rounds=mr,
            history_budget_chars=body.history_budget_chars,
            history_min_recent=body.history_min_recent,
            openrouter_model=body.openrouter_model,
            temperature=body.temperature,
            expected_version=body.expected_version,
            updated_by=body.updated_by or "operator",
        )
    except RuntimeError as e:
        msg = str(e)
        if "version conflict" in msg:
            raise HTTPException(409, msg) from e
        raise HTTPException(503, msg) from e
    return {
        "version": op.version,
        "system_append": op.system_append,
        "max_rounds": op.max_rounds,
        "max_tool_rounds": op.max_rounds,
        "openrouter_model": op.openrouter_model,
        "temperature": op.temperature,
        "history_budget_chars": op.history_budget_chars,
        "history_min_recent": op.history_min_recent,
        "env_model_default": cfg.OPENROUTER_MODEL,
        "env_max_tool_rounds_cap": cfg.MAX_TOOL_ROUNDS,
        "store": "postgres" if cfg.AGENT_DB_DSN else "none",
    }
