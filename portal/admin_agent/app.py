"""
Admin assistant API — local only (127.0.0.1). OpenRouter for chat + embeddings; SQLite RAG for codebase.
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

from codebase_index import get_index

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

_env_path = Path(__file__).resolve().parent / ".env"
if _env_path.is_file():
    # Default load_dotenv(override=False) leaves stale/empty values from systemd/shell;
    # those would ignore this file and cause 401 even when .env is correct.
    load_dotenv(_env_path, override=True)
else:
    logger.warning("Missing %s — using only process environment (systemd EnvironmentFile, export, etc.)", _env_path)

_admin_secret_len = len((os.getenv("ADMIN_AGENT_SECRET") or "").strip())
if _admin_secret_len == 0:
    logger.warning(
        "ADMIN_AGENT_SECRET is empty — every request with _require_secret will return 401. "
        "Create %s or set the variable in the service unit.",
        _env_path,
    )
else:
    logger.info("ADMIN_AGENT_SECRET loaded (length %s)", _admin_secret_len)

OPENROUTER_URL = "https://openrouter.ai/api/v1/chat/completions"

app = FastAPI(title="MDT Admin Agent (Prevención)", version="1.0.0")

PRODUCT_PROMPTS: Dict[str, str] = {
    "medisalut": (
        "Eres el asistente de administración de Medisalut (centro médico, Symfony 2, Sonata Admin). "
        "Respondes en el idioma del usuario, de forma concisa y profesional. "
        "No inventes datos de pacientes; si falta contexto, di qué comprobar en la aplicación. "
        "Si se incluyen fragmentos de código, úsalos para fundamentar, sin inventar."
    ),
    "prevencion": (
        "Eres el asistente de administración del portal de Prevención (Symfony 4, intranet, Sonata). "
        "Respondes en el idioma del usuario, de forma concisa y profesional. "
        "No inventes datos; si falta contexto, indica qué revisar en el admin. "
        "Si se incluyen fragmentos de código, úsalos para fundamentar, sin inventar."
    ),
}


def _system_prompt() -> str:
    product = (os.getenv("APP_PRODUCT") or "prevencion").strip().lower()
    return PRODUCT_PROMPTS.get(product) or PRODUCT_PROMPTS["prevencion"]


class ChatRequest(BaseModel):
    message: str = Field(..., min_length=1, max_length=50000)
    messages: Optional[List[Dict[str, Any]]] = None
    use_codebase: bool = True


class ReindexRequest(BaseModel):
    full: bool = False


def _openrouter_key() -> str:
    k = (os.getenv("OPENROUTER_API_KEY") or "").strip()
    if not k:
        raise HTTPException(503, "OPENROUTER_API_KEY is not configured")
    return k


def _require_secret(x_admin_agent_secret: Optional[str]) -> None:
    expected = (os.getenv("ADMIN_AGENT_SECRET") or "").strip()
    if not expected or (x_admin_agent_secret or "").strip() != expected:
        raise HTTPException(401, "Invalid or missing X-Admin-Agent-Secret")


@app.get("/health")
def health() -> Dict[str, str]:
    import hashlib
    s = (os.getenv("ADMIN_AGENT_SECRET") or "").strip()
    fp = "empty" if not s else f"len={len(s)} sha256_8={hashlib.sha256(s.encode()).hexdigest()[:8]}"
    return {
        "status": "ok",
        "product": (os.getenv("APP_PRODUCT") or "prevencion").strip() or "prevencion",
        "secret_fingerprint": fp,
    }


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
    key = _openrouter_key()
    try:
        return await get_index().reindex(key, full=bool(body.full))
    except Exception as e:
        logger.exception("reindex failed")
        raise HTTPException(500, str(e)) from e


@app.post("/v1/chat")
async def chat(
    body: ChatRequest,
    x_admin_agent_secret: Optional[str] = Header(default=None, alias="X-Admin-Agent-Secret"),
) -> Dict[str, Any]:
    _require_secret(x_admin_agent_secret)
    api_key = _openrouter_key()

    model = (os.getenv("OPENROUTER_MODEL") or "openai/gpt-4o-mini").strip()
    system = _system_prompt()
    user_messages: List[Dict[str, str]] = []

    if body.messages:
        for m in body.messages[-24:]:
            r = (m.get("role") or "user").strip()
            c = (m.get("content") or "").strip()
            if c and r in ("user", "assistant", "system"):
                user_messages.append({"role": r, "content": c})
    out_messages: List[Dict[str, str]] = [{"role": "system", "content": system}]
    hits: List[Dict[str, Any]] = []

    if body.use_codebase:
        st = get_index().status()
        if st.get("chunk_count", 0) > 0:
            try:
                hits = await get_index().search(body.message, api_key, top_k=8)
            except Exception as e:
                logger.warning("codebase search skipped: %s", e)
                hits = []
            if hits:
                parts = [f"### {h['path']}\n{h['chunk']}" for h in hits]
                block = "\n\n---\n\n".join(parts)
                rag = (
                    "\n\n## Contexto del repositorio indexado (búsqueda semántica; verifica siempre con el fichero real)\n\n"
                    + block
                )
                out_messages[0]["content"] = out_messages[0]["content"] + rag

    out_messages.extend(user_messages)
    out_messages.append({"role": "user", "content": body.message})

    headers = {
        "Content-Type": "application/json",
        "Authorization": f"Bearer {api_key}",
        "HTTP-Referer": (os.getenv("OPENROUTER_HTTP_REFERER") or "https://github.com/s4lhadev/prevencio-meditrauma")[:200],
        "X-Title": "prevencion-admin-agent",
    }

    payload = {
        "model": model,
        "messages": out_messages,
        "temperature": 0.2,
    }

    async with httpx.AsyncClient(timeout=httpx.Timeout(120.0, connect=15.0)) as client:
        try:
            r = await client.post(OPENROUTER_URL, json=payload, headers=headers)
        except httpx.RequestError as e:
            logger.exception("OpenRouter request failed: %s", e)
            raise HTTPException(502, f"LLM request failed: {e!s}") from e

    if r.status_code >= 400:
        detail = (r.text or "")[:2000]
        logger.warning("OpenRouter error %s: %s", r.status_code, detail)
        raise HTTPException(502, f"LLM error {r.status_code}: {detail}")

    data = r.json()
    try:
        text = (data.get("choices") or [{}])[0].get("message", {}).get("content") or ""
    except (IndexError, TypeError, AttributeError):
        text = ""
    if not str(text).strip():
        text = "(Sin respuesta del modelo. Revisa OPENROUTER_API_KEY y el modelo en OPENROUTER_MODEL.)"

    return {
        "reply": text,
        "model": model,
        "product": (os.getenv("APP_PRODUCT") or "prevencion").strip() or "prevencion",
        "codebase_hits": len(hits) if body.use_codebase else 0,
    }
