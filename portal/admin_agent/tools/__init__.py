"""Tool registry for the agent.

A tool is any callable that returns a dict (JSON-serializable). Each tool exports:

- ``NAME``: stable identifier the LLM uses (snake_case).
- ``SCHEMA``: OpenAI/OpenRouter ``tools[i]`` JSON schema (function-calling format).
- ``async def run(args: dict, ctx: ToolContext) -> dict``.
- ``TIER``: minimum tier required ('user' or 'dev').

Tools are imported eagerly here so that ``ALL_TOOLS`` reflects the current build.
"""
from __future__ import annotations

from dataclasses import dataclass
from typing import Any, Awaitable, Callable, Dict, List, Optional, Set


@dataclass
class ToolContext:
    """Per-request context handed to every tool. Avoid stuffing globals in tools."""

    tier: str  # 'user' | 'dev'
    session_id: Optional[str]
    openrouter_api_key: str
    audit_log: Optional[Callable[[Dict[str, Any]], Awaitable[None]]] = None


@dataclass
class Tool:
    name: str
    schema: Dict[str, Any]
    run: Callable[[Dict[str, Any], ToolContext], Awaitable[Dict[str, Any]]]
    tier: str = "dev"  # 'user' lets non-dev sessions call it


_REGISTRY: Dict[str, Tool] = {}


def register(tool: Tool) -> None:
    if tool.name in _REGISTRY:
        raise RuntimeError(f"duplicate tool name: {tool.name}")
    _REGISTRY[tool.name] = tool


def get(name: str) -> Optional[Tool]:
    return _REGISTRY.get(name)


def all_tools() -> List[Tool]:
    return list(_REGISTRY.values())


def schemas_for_tier(tier: str) -> List[Dict[str, Any]]:
    """Filter schemas by tier so the LLM only ever sees what it can call."""
    out: List[Dict[str, Any]] = []
    for t in _REGISTRY.values():
        if tier == "dev" or t.tier == "user":
            out.append(t.schema)
    return out


def names_for_tier(tier: str) -> Set[str]:
    return {t.name for t in _REGISTRY.values() if tier == "dev" or t.tier == "user"}


# Eager imports so registration runs at module load. Keep alphabetical for sanity.
from . import code_search  # noqa: E402,F401
from . import http  # noqa: E402,F401
from . import log as _log  # noqa: E402,F401  (avoid shadow with logging)
from . import shell  # noqa: E402,F401
from . import sql  # noqa: E402,F401
from . import symfony  # noqa: E402,F401
from . import web  # noqa: E402,F401
