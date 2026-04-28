"""run_shell: subprocess local on the VM. Tier 'dev' only. Kill switch via env."""
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
        "name": "run_shell",
        "description": (
            "Run a shell command on the VM (uvicorn process is on the VM, no SSH needed). "
            "bash -lc, pipefail. Use for: docker (none here), systemd, journalctl, curl localhost ports, "
            "ls/cat/grep/tail under repo paths, sudo -n if NOPASSWD permits. "
            f"Timeout {cfg.SHELL_TIMEOUT_S}s, output capped at {cfg.SHELL_MAX_OUTPUT} bytes. "
            "Disabled when AGENT_SHELL_DISABLE=1. Audited."
        ),
        "parameters": {
            "type": "object",
            "properties": {
                "command": {
                    "type": "string",
                    "description": "Complete shell command, e.g. 'systemctl status prevencion-admin-agent | head -40'",
                },
                "cwd": {
                    "type": "string",
                    "description": "Optional working directory (must exist).",
                },
            },
            "required": ["command"],
        },
    },
}


async def run(args: Dict[str, Any], ctx: ToolContext) -> Dict[str, Any]:
    if cfg.SHELL_DISABLED:
        return {"error": "run_shell disabled (AGENT_SHELL_DISABLE=1)"}
    cmd = (args.get("command") or "").strip()
    if not cmd:
        return {"error": "empty command"}
    cwd = (args.get("cwd") or "").strip() or None
    if cwd and not os.path.isdir(cwd):
        return {"error": f"cwd does not exist: {cwd}"}

    try:
        proc = await asyncio.create_subprocess_exec(
            "bash",
            "-lc",
            "set -o pipefail; " + cmd,
            cwd=cwd,
            stdout=asyncio.subprocess.PIPE,
            stderr=asyncio.subprocess.STDOUT,
        )
    except Exception as e:
        return {"error": f"spawn failed: {e}"}

    try:
        out_b, _ = await asyncio.wait_for(proc.communicate(), timeout=cfg.SHELL_TIMEOUT_S)
    except asyncio.TimeoutError:
        try:
            proc.kill()
        except Exception:
            pass
        return {"error": f"command timed out after {cfg.SHELL_TIMEOUT_S}s"}

    out = (out_b or b"").decode("utf-8", errors="replace")
    truncated = False
    if len(out) > cfg.SHELL_MAX_OUTPUT:
        out = out[: cfg.SHELL_MAX_OUTPUT] + "\n…(truncated)"
        truncated = True
    return {
        "exit_code": proc.returncode,
        "output": out,
        "truncated": truncated,
        "cwd": cwd or os.getcwd(),
    }


register(Tool(name="run_shell", schema=SCHEMA, run=run, tier="dev"))
