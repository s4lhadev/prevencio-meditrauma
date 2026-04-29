"""read_log: tail of allowlisted streams (apache, symfony, agent, journalctl unit)."""
from __future__ import annotations

import asyncio
import logging
import os
from typing import Any, Dict

from . import Tool, ToolContext, register
import config as cfg

logger = logging.getLogger(__name__)


def _streams_description() -> str:
    files = ", ".join(sorted(cfg.LOG_PATHS.keys()))
    units = ", ".join(sorted(cfg.JOURNAL_UNITS_ALLOWED))
    return f"Allowed file streams: {files}. Allowed journalctl units: {units}."


SCHEMA: Dict[str, Any] = {
    "type": "function",
    "function": {
        "name": "read_log",
        "description": (
            "Tail the last N lines of an allowlisted log stream. "
            + _streams_description()
            + " For 'journal:UNIT' the unit must be in the allowed list. "
            "For 'file:NAME' the name must match an allowed file stream."
        ),
        "parameters": {
            "type": "object",
            "properties": {
                "stream": {
                    "type": "string",
                    "description": "Either 'file:<name>' (e.g. 'file:apache_error') or 'journal:<unit>' (e.g. 'journal:prevencion-admin-agent').",
                },
                "lines": {
                    "type": "integer",
                    "description": "Tail size (1-2000). Default 200.",
                    "default": 200,
                },
                "grep": {
                    "type": "string",
                    "description": "Optional regex (POSIX) to filter lines (egrep). Applied AFTER tail.",
                },
            },
            "required": ["stream"],
        },
    },
}


async def _tail_file(path: str, lines: int, grep: str) -> Dict[str, Any]:
    if not os.path.isfile(path):
        return {"error": f"file does not exist: {path}"}
    if not os.access(path, os.R_OK):
        return {"error": f"file not readable by agent process: {path}"}
    cmd = f"tail -n {int(lines)} {path!r}"
    if grep:
        # -E for extended regex; quote single quotes in pattern
        safe = grep.replace("'", "'\"'\"'")
        cmd += f" | grep -E -- '{safe}' || true"
    proc = await asyncio.create_subprocess_shell(
        cmd, stdout=asyncio.subprocess.PIPE, stderr=asyncio.subprocess.STDOUT
    )
    try:
        out_b, _ = await asyncio.wait_for(proc.communicate(), timeout=30.0)
    except asyncio.TimeoutError:
        try:
            proc.kill()
        except Exception:
            pass
        return {"error": "tail timed out"}
    text = (out_b or b"").decode("utf-8", errors="replace")
    if len(text) > 400_000:
        text = text[:400_000] + "\n…(truncated)"
    return {"path": path, "lines": text}


async def _journal(unit: str, lines: int, grep: str) -> Dict[str, Any]:
    cmd = f"journalctl -u {unit!r} -n {int(lines)} --no-pager"
    if grep:
        safe = grep.replace("'", "'\"'\"'")
        cmd += f" | grep -E -- '{safe}' || true"
    # Try without sudo first; if no permission, fall back to sudo -n.
    proc = await asyncio.create_subprocess_shell(
        cmd, stdout=asyncio.subprocess.PIPE, stderr=asyncio.subprocess.STDOUT
    )
    try:
        out_b, _ = await asyncio.wait_for(proc.communicate(), timeout=30.0)
    except asyncio.TimeoutError:
        try:
            proc.kill()
        except Exception:
            pass
        return {"error": "journalctl timed out"}
    text = (out_b or b"").decode("utf-8", errors="replace")
    if "permission" in text.lower() or proc.returncode != 0:
        # Retry with sudo: password via VM_DEPLOY_SUDO_PASSWORD or NOPASSWD (-n).
        if cfg.VM_DEPLOY_SUDO_PASSWORD:
            proc2 = await asyncio.create_subprocess_exec(
                "bash",
                "-lc",
                cfg.bash_lc_with_optional_sudo_shim("sudo " + cmd),
                stdout=asyncio.subprocess.PIPE,
                stderr=asyncio.subprocess.STDOUT,
            )
        else:
            proc2 = await asyncio.create_subprocess_shell(
                "sudo -n " + cmd,
                stdout=asyncio.subprocess.PIPE,
                stderr=asyncio.subprocess.STDOUT,
            )
        try:
            out_b2, _ = await asyncio.wait_for(proc2.communicate(), timeout=30.0)
        except asyncio.TimeoutError:
            try:
                proc2.kill()
            except Exception:
                pass
            return {"error": "journalctl (sudo) timed out"}
        if proc2.returncode == 0:
            text = (out_b2 or b"").decode("utf-8", errors="replace")
    if len(text) > 400_000:
        text = text[:400_000] + "\n…(truncated)"
    return {"unit": unit, "lines": text}


async def run(args: Dict[str, Any], ctx: ToolContext) -> Dict[str, Any]:
    stream = (args.get("stream") or "").strip()
    if not stream or ":" not in stream:
        return {"error": "stream must be 'file:<name>' or 'journal:<unit>'"}
    kind, name = stream.split(":", 1)
    kind = kind.strip().lower()
    name = name.strip()
    try:
        lines = max(1, min(2000, int(args.get("lines") or 200)))
    except (TypeError, ValueError):
        lines = 200
    grep = (args.get("grep") or "").strip()

    if kind == "file":
        path = cfg.LOG_PATHS.get(name)
        if not path:
            return {
                "error": f"unknown file stream '{name}'. Allowed: {sorted(cfg.LOG_PATHS.keys())}",
            }
        return await _tail_file(path, lines, grep)
    if kind == "journal":
        if name not in cfg.JOURNAL_UNITS_ALLOWED:
            return {
                "error": f"journal unit '{name}' not allowed. Allowed: {cfg.JOURNAL_UNITS_ALLOWED}",
            }
        return await _journal(name, lines, grep)
    return {"error": f"unknown stream kind '{kind}', expected 'file' or 'journal'"}


register(Tool(name="read_log", schema=SCHEMA, run=run, tier="user"))
