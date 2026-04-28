"""code_search: semantic search over the Symfony codebase via codebase_index.py."""
from __future__ import annotations

import logging
from typing import Any, Dict

from codebase_index import get_index

from . import Tool, ToolContext, register

logger = logging.getLogger(__name__)


SCHEMA: Dict[str, Any] = {
    "type": "function",
    "function": {
        "name": "code_search",
        "description": (
            "Semantic search over the Prevencion Symfony codebase (current/ + portal/). "
            "Returns top-k matching chunks with file path, similarity score and excerpt. "
            "Use to locate controllers, entities, Twig templates, configs, services. "
            "Paths look like 'current/src/Controller/...' or 'portal/templates/...'. "
            "Run repo reindex from the UI after big code changes."
        ),
        "parameters": {
            "type": "object",
            "properties": {
                "query": {
                    "type": "string",
                    "description": "Natural-language or technical query (e.g. 'how is page key validated', 'AdminAsistenteController unlock').",
                },
                "top_k": {
                    "type": "integer",
                    "description": "Max chunks to return (1-15).",
                    "default": 8,
                },
            },
            "required": ["query"],
        },
    },
}


async def run(args: Dict[str, Any], ctx: ToolContext) -> Dict[str, Any]:
    query = (args.get("query") or "").strip()
    if not query:
        return {"error": "empty query"}
    try:
        top_k = int(args.get("top_k") or 8)
    except (TypeError, ValueError):
        top_k = 8
    top_k = max(1, min(15, top_k))
    if not ctx.openrouter_api_key:
        return {"error": "OPENROUTER_API_KEY missing — embeddings unavailable"}
    try:
        hits = await get_index().search(query, ctx.openrouter_api_key, top_k=top_k)
    except Exception as e:
        logger.exception("code_search failed")
        return {"error": str(e)}
    return {"query": query, "count": len(hits), "results": hits}


register(Tool(name="code_search", schema=SCHEMA, run=run, tier="user"))
