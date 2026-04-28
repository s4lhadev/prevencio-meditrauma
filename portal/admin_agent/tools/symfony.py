"""symfony_console: bin/console with allowlisted, non-destructive subcommands."""
from __future__ import annotations

import asyncio
import logging
import os
from typing import Any, Dict

from . import Tool, ToolContext, register
import config as cfg

logger = logging.getLogger(__name__)


SCHEMA: Dict[str, Any] = {
    "type": "function",
    "function": {
        "name": "symfony_console",
        "description": (
            "Run an allowlisted Symfony console command in the current/ or portal/ app. "
            "Allowed commands: " + ", ".join(cfg.SYMFONY_CONSOLE_ALLOWED) + ". "
            "Apps: " + ", ".join(os.path.basename(p) for p in cfg.SYMFONY_APP_PATHS) + ". "
            "Always uses --env=prod and --no-interaction. Read-only / introspection only."
        ),
        "parameters": {
            "type": "object",
            "properties": {
                "app": {
                    "type": "string",
                    "description": "App basename: 'current' or 'portal'.",
                },
                "command": {
                    "type": "string",
                    "description": "First token of the console command (e.g. 'debug:router').",
                },
                "args": {
                    "type": "string",
                    "description": "Extra args/flags passed verbatim (no semicolons, no shell metachars).",
                },
            },
            "required": ["app", "command"],
        },
    },
}


_BAD_CHARS = set(";&|`$<>")


def _validate_args(extra: str) -> bool:
    if not extra:
        return True
    return not any(c in _BAD_CHARS for c in extra)


def _resolve_app_path(app: str) -> str:
    app = (app or "").strip().lower()
    for p in cfg.SYMFONY_APP_PATHS:
        if os.path.basename(p).lower() == app:
            return p
    return ""


async def run(args: Dict[str, Any], ctx: ToolContext) -> Dict[str, Any]:
    app = (args.get("app") or "").strip()
    command = (args.get("command") or "").strip()
    extra = (args.get("args") or "").strip()
    if command not in cfg.SYMFONY_CONSOLE_ALLOWED:
        return {
            "error": f"command '{command}' not allowed. Allowed: {cfg.SYMFONY_CONSOLE_ALLOWED}",
        }
    if not _validate_args(extra):
        return {"error": "extra args contain forbidden shell metacharacters"}
    app_path = _resolve_app_path(app)
    if not app_path or not os.path.isfile(os.path.join(app_path, "bin", "console")):
        return {"error": f"app '{app}' not found or no bin/console"}
    cmd = f"php bin/console {command} {extra} --env=prod --no-interaction".strip()
    proc = await asyncio.create_subprocess_shell(
        cmd,
        cwd=app_path,
        stdout=asyncio.subprocess.PIPE,
        stderr=asyncio.subprocess.STDOUT,
    )
    try:
        out_b, _ = await asyncio.wait_for(proc.communicate(), timeout=60.0)
    except asyncio.TimeoutError:
        try:
            proc.kill()
        except Exception:
            pass
        return {"error": "symfony_console timed out"}
    text = (out_b or b"").decode("utf-8", errors="replace")
    if len(text) > 400_000:
        text = text[:400_000] + "\n…(truncated)"
    return {
        "app": app,
        "command": command,
        "exit_code": proc.returncode,
        "output": text,
    }


register(Tool(name="symfony_console", schema=SCHEMA, run=run, tier="dev"))
