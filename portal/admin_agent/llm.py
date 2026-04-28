"""OpenRouter chat-completions client (with tool-calling). Single function."""
from __future__ import annotations

import logging
from typing import Any, Dict, List, Optional

import httpx

import config as cfg

logger = logging.getLogger(__name__)


async def call_openrouter(
    *,
    api_key: str,
    messages: List[Dict[str, Any]],
    tools: Optional[List[Dict[str, Any]]] = None,
    model: Optional[str] = None,
    temperature: float = 0.2,
    max_tokens: int = 4096,
) -> Dict[str, Any]:
    """One non-streaming chat-completions call. Returns raw response JSON.

    The agent loop streams its own SSE to the client; we don't use OpenRouter's
    streaming directly because tool-call deltas across providers are inconsistent.
    """
    if not api_key:
        raise RuntimeError("OPENROUTER_API_KEY is empty")
    payload: Dict[str, Any] = {
        "model": model or cfg.OPENROUTER_MODEL,
        "messages": messages,
        "max_tokens": max_tokens,
        "temperature": temperature,
    }
    if tools:
        payload["tools"] = tools
        payload["tool_choice"] = "auto"
    async with httpx.AsyncClient(timeout=180.0) as client:
        resp = await client.post(
            cfg.OPENROUTER_API_URL,
            headers={
                "Authorization": f"Bearer {api_key}",
                "Content-Type": "application/json",
                "HTTP-Referer": cfg.OPENROUTER_HTTP_REFERER,
                "X-Title": "prevencion-admin-agent",
            },
            json=payload,
        )
    if resp.status_code >= 400:
        # Surface short error for the caller; full body is logged.
        logger.warning("OpenRouter %s on model %s: %s", resp.status_code, payload["model"], resp.text[:1000])
        raise RuntimeError(f"OpenRouter {resp.status_code}: {resp.text[:600]}")
    return resp.json()
