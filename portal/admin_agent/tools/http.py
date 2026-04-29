"""http_request: arbitrary HTTP. Restricted to allowlisted hosts."""
from __future__ import annotations

import logging
from typing import Any, Dict
from urllib.parse import urlparse

import httpx

from . import Tool, ToolContext, register
import config as cfg

logger = logging.getLogger(__name__)


def _host_allowed(url: str) -> bool:
    try:
        host = (urlparse(url).hostname or "").lower()
    except Exception:
        return False
    if not host:
        return False
    for allowed in cfg.HTTP_ALLOWED_HOSTS:
        a = allowed.lower()
        if host == a or host.endswith("." + a):
            return True
    return False


SCHEMA: Dict[str, Any] = {
    "type": "function",
    "function": {
        "name": "http_request",
        "description": (
            "HTTP from the agent process. Restricted to allowlisted hosts: "
            + ", ".join(cfg.HTTP_ALLOWED_HOSTS)
            + " (and subdomains). Use to call the Symfony app's own endpoints, local services, etc. "
            f"Body capped at {cfg.HTTP_MAX_BODY} bytes."
        ),
        "parameters": {
            "type": "object",
            "properties": {
                "method": {
                    "type": "string",
                    "enum": ["GET", "POST", "PUT", "PATCH", "DELETE", "HEAD"],
                    "default": "GET",
                },
                "url": {"type": "string"},
                "headers": {
                    "type": "object",
                    "additionalProperties": {"type": "string"},
                    "description": "Optional HTTP headers.",
                },
                "body": {
                    "type": "string",
                    "description": "Optional raw request body.",
                },
            },
            "required": ["url"],
        },
    },
}


async def run(args: Dict[str, Any], ctx: ToolContext) -> Dict[str, Any]:
    method = (args.get("method") or "GET").upper()
    url = (args.get("url") or "").strip()
    if not url:
        return {"error": "url is required"}
    if not _host_allowed(url):
        return {
            "error": (
                f"host not in allowlist. Allowed: {cfg.HTTP_ALLOWED_HOSTS}. "
                "Adjust AGENT_HTTP_ALLOWED_HOSTS if needed."
            )
        }
    headers = args.get("headers") or None
    body = args.get("body")
    body_bytes = body.encode("utf-8") if isinstance(body, str) else None
    try:
        async with httpx.AsyncClient(timeout=60.0, follow_redirects=True) as client:
            resp = await client.request(method, url, headers=headers, content=body_bytes)
            text = resp.text or ""
            truncated = False
            if len(text) > cfg.HTTP_MAX_BODY:
                text = text[: cfg.HTTP_MAX_BODY] + "\n…(truncated)"
                truncated = True
            return {
                "status_code": resp.status_code,
                "headers": dict(resp.headers),
                "body": text,
                "truncated": truncated,
            }
    except Exception as e:
        return {"error": str(e)}


register(Tool(name="http_request", schema=SCHEMA, run=run, tier="user"))
