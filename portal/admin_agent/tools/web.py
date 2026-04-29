"""web_search (OpenRouter web plugin) + fetch_web_page (httpx HTML→text)."""
from __future__ import annotations

import html as html_stdlib
import logging
import re
from typing import Any, Dict, List, Optional
from urllib.parse import urlparse

import httpx

from . import Tool, ToolContext, register
import config as cfg

logger = logging.getLogger(__name__)


# --- web_search -----------------------------------------------------------------------
SCHEMA_WEB_SEARCH: Dict[str, Any] = {
    "type": "function",
    "function": {
        "name": "web_search",
        "description": (
            "Public web search via OpenRouter web plugin. Use for vendor docs, Symfony / Sonata "
            "questions, error codes, third-party API references. NOT for our own codebase "
            "(use code_search) or our DB (use sql_*)."
        ),
        "parameters": {
            "type": "object",
            "properties": {
                "query": {"type": "string"},
            },
            "required": ["query"],
        },
    },
}


def _build_web_plugins() -> List[Dict[str, Any]]:
    plugin: Dict[str, Any] = {"id": "web"}
    if cfg.WEB_INCLUDE_DOMAINS:
        plugin["include_domains"] = list(cfg.WEB_INCLUDE_DOMAINS)
    return [plugin]


async def _run_web_search(args: Dict[str, Any], ctx: ToolContext) -> Dict[str, Any]:
    query = (args.get("query") or "").strip()
    if not query:
        return {"error": "empty query"}
    if not ctx.openrouter_api_key:
        return {"error": "OPENROUTER_API_KEY missing"}
    model = (ctx.llm_model or cfg.OPENROUTER_MODEL).strip()
    payload = {
        "model": model,
        "messages": [{"role": "user", "content": query}],
        "max_tokens": 1500,
        "temperature": 0.2,
        "plugins": _build_web_plugins(),
    }
    try:
        async with httpx.AsyncClient(timeout=120.0) as client:
            resp = await client.post(
                cfg.OPENROUTER_API_URL,
                headers={
                    "Authorization": f"Bearer {ctx.openrouter_api_key}",
                    "Content-Type": "application/json",
                    "HTTP-Referer": cfg.OPENROUTER_HTTP_REFERER,
                },
                json=payload,
            )
    except Exception as e:
        return {"error": f"web_search request failed: {e}"}
    if resp.status_code >= 400:
        return {"error": f"OpenRouter web_search {resp.status_code}: {resp.text[:600]}"}
    data = resp.json()
    msg = (data.get("choices") or [{}])[0].get("message", {}) or {}
    content = msg.get("content") or ""
    citations = _citation_urls_from_message(msg)
    enriched = await _enrich_with_fetched_pages(content, citations)
    return {"query": query, "answer": enriched, "citations": citations}


def _citation_urls_from_message(msg: Dict[str, Any]) -> List[str]:
    urls: List[str] = []
    annots = msg.get("annotations") or []
    for a in annots:
        if isinstance(a, dict) and a.get("type") == "url_citation":
            uc = a.get("url_citation") or {}
            u = uc.get("url")
            if isinstance(u, str) and u.startswith("http"):
                urls.append(u)
    seen = set()
    out: List[str] = []
    for u in urls:
        if u not in seen:
            out.append(u)
            seen.add(u)
    return out


_HTML_TAGS_RE = re.compile(r"<script\b.*?</script>|<style\b.*?</style>", re.IGNORECASE | re.DOTALL)
_TAG_RE = re.compile(r"<[^>]+>")
_WHITESPACE_RE = re.compile(r"\s+")


def _html_to_text(html: str) -> str:
    no_scripts = _HTML_TAGS_RE.sub(" ", html)
    no_tags = _TAG_RE.sub(" ", no_scripts)
    decoded = html_stdlib.unescape(no_tags)
    return _WHITESPACE_RE.sub(" ", decoded).strip()


async def _fetch_one(url: str, max_chars: int) -> Optional[Dict[str, Any]]:
    try:
        async with httpx.AsyncClient(timeout=30.0, follow_redirects=True) as client:
            resp = await client.get(url, headers={"User-Agent": "Mozilla/5.0 (compatible; PrevencionAdminAgent/1.0)"})
    except Exception as e:
        return {"url": url, "error": str(e)}
    ct = (resp.headers.get("content-type") or "").lower()
    text = resp.text or ""
    if "html" in ct:
        text = _html_to_text(text)
    if len(text) > max_chars:
        text = text[:max_chars] + "…(truncated)"
    return {"url": url, "status_code": resp.status_code, "text": text}


async def _enrich_with_fetched_pages(content: str, citations: List[str]) -> str:
    if not citations:
        return content
    take = citations[: cfg.WEB_FETCH_MAX_URLS]
    parts: List[str] = [content.strip()]
    for u in take:
        page = await _fetch_one(u, cfg.WEB_FETCH_MAX_CHARS_PER_URL)
        if not page:
            continue
        if page.get("error"):
            parts.append(f"\n\n--- Fetched {u} ---\n[error] {page['error']}")
        else:
            parts.append(f"\n\n--- Fetched {u} (status {page['status_code']}) ---\n{page['text']}")
    return "\n".join(parts)


# --- fetch_web_page -------------------------------------------------------------------
SCHEMA_FETCH_PAGE: Dict[str, Any] = {
    "type": "function",
    "function": {
        "name": "fetch_web_page",
        "description": (
            "HTTPS GET a public URL and return readable text (HTML stripped, JSON pretty-printed). "
            "Use when you already have a doc URL or web_search did not retrieve it."
        ),
        "parameters": {
            "type": "object",
            "properties": {
                "url": {"type": "string"},
            },
            "required": ["url"],
        },
    },
}


async def _run_fetch_web_page(args: Dict[str, Any], ctx: ToolContext) -> Dict[str, Any]:
    url = (args.get("url") or "").strip()
    if not url:
        return {"error": "url is required"}
    parsed = urlparse(url)
    if parsed.scheme not in ("http", "https"):
        return {"error": "only http/https URLs are allowed"}
    page = await _fetch_one(url, cfg.WEB_FETCH_MAX_CHARS_PER_URL)
    if page is None:
        return {"error": "fetch failed"}
    return page


register(Tool(name="web_search", schema=SCHEMA_WEB_SEARCH, run=_run_web_search, tier="user"))
register(Tool(name="fetch_web_page", schema=SCHEMA_FETCH_PAGE, run=_run_fetch_web_page, tier="user"))
