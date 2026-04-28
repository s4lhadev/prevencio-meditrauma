"""Agentic loop: send to OpenRouter, run tool calls, repeat until final text or max rounds.

Yields SSE events (str, ready to write):
  event: session  → initial metadata (tier, session_id, model)
  event: content  → assistant text delta
  event: tool_call  → assistant requested a tool (name, args_preview)
  event: tool_result → tool finished (name, ok, preview)
  event: error    → fatal error
  event: done     → end of turn

The loop persists user/assistant/tool messages to agent.session_message when a
session_id is provided, so the chat is server-of-record.
"""
from __future__ import annotations

import asyncio
import json
import logging
import time
from typing import Any, AsyncGenerator, Dict, List, Optional

import audit
import config as cfg
import operator_config as opcfg
import prompts as prompts_mod
import session_store
import tools as tools_pkg
from llm import call_openrouter
from tools import ToolContext

logger = logging.getLogger(__name__)


def assistant_message_text(msg: Dict[str, Any]) -> str:
    """Normalize OpenRouter/Anthropic message.content (str or list of content blocks)."""
    c = msg.get("content")
    if c is None:
        return ""
    if isinstance(c, str):
        return c
    if isinstance(c, list):
        parts: List[str] = []
        for block in c:
            if isinstance(block, dict):
                if block.get("type") == "text":
                    parts.append(str(block.get("text") or ""))
                elif "text" in block:
                    parts.append(str(block.get("text") or ""))
            elif isinstance(block, str):
                parts.append(block)
        return "".join(parts)
    return str(c)


def _sse(event: str, data: Dict[str, Any]) -> str:
    return f"event: {event}\ndata: {json.dumps(data, default=str)}\n\n"


def _truncate_for_model(result: Dict[str, Any]) -> Dict[str, Any]:
    """Cap the JSON we feed back to the LLM. Big lists get sliced; long strings cut."""
    out = dict(result)
    if isinstance(out.get("rows"), list) and len(out["rows"]) > 50:
        out["rows"] = out["rows"][:50]
        out["rows_truncated_in_context"] = True
    if isinstance(out.get("results"), list) and len(out["results"]) > 12:
        out["results"] = out["results"][:12]
        out["results_truncated_in_context"] = True
    for k in ("output", "lines", "body", "answer", "digest", "text"):
        v = out.get(k)
        if isinstance(v, str) and len(v) > 80_000:
            out[k] = v[:80_000] + "\n…(truncated)"
    # Final cap on overall serialized size
    try:
        s = json.dumps(out, default=str)
    except Exception:
        return out
    if len(s) > cfg.TOOL_CONTENT_MAX:
        # As a last resort, drop the largest string fields
        for k in sorted(out.keys(), key=lambda kk: len(str(out.get(kk, ""))), reverse=True):
            if isinstance(out[k], str):
                out[k] = out[k][:1000] + f"\n…(field {k} truncated to fit TOOL_CONTENT_MAX)"
            try:
                s = json.dumps(out, default=str)
            except Exception:
                continue
            if len(s) <= cfg.TOOL_CONTENT_MAX:
                break
    return out


def _truncate_for_sse_preview(result: Dict[str, Any]) -> Dict[str, Any]:
    """Even smaller payload for the SSE event so the UI does not balloon."""
    out: Dict[str, Any] = {}
    for k, v in result.items():
        if isinstance(v, str) and len(v) > 4_000:
            out[k] = v[:4_000] + "…(truncated in preview)"
        elif isinstance(v, list) and len(v) > 8:
            out[k] = v[:8] + [f"…(+{len(v)-8} more)"]
        else:
            out[k] = v
    return out


async def _execute_tool(
    name: str,
    args: Dict[str, Any],
    ctx: ToolContext,
    *,
    who: str,
) -> Dict[str, Any]:
    tool = tools_pkg.get(name)
    if tool is None:
        return {"error": f"unknown tool: {name}"}
    if ctx.tier != "dev" and tool.tier == "dev":
        return {"error": f"tool '{name}' requires dev tier (not enabled this session)"}
    t0 = time.monotonic()
    try:
        result = await tool.run(args, ctx)
    except Exception as e:
        logger.exception("tool %s raised", name)
        result = {"error": f"tool exception: {e}"}
    elapsed_ms = int((time.monotonic() - t0) * 1000)
    if not isinstance(result, dict):
        result = {"value": result}
    asyncio.create_task(
        audit.log_tool_call(
            session_id=ctx.session_id,
            who=who,
            tier=ctx.tier,
            tool=name,
            args=args,
            result=result,
            elapsed_ms=elapsed_ms,
        )
    )
    return result


async def stream_chat_turn(
    *,
    user_message: str,
    tier: str,
    session_id: Optional[str],
    who: str,
    openrouter_api_key: str,
    model_override: Optional[str] = None,
) -> AsyncGenerator[str, None]:
    """Drive one user turn end-to-end. SSE str chunks are yielded and the caller writes them."""
    ctx = ToolContext(
        tier=tier,
        session_id=session_id,
        openrouter_api_key=openrouter_api_key,
    )

    # 1) load operator config + schema digest
    try:
        op = await opcfg.get_config()
    except Exception:
        op = opcfg.OperatorConfig()
    schema_text = ""
    try:
        from tools.sql import get_schema_digest

        schema_text = await get_schema_digest(False)
    except Exception as e:
        logger.warning("schema digest unavailable: %s", e)

    system_prompt = prompts_mod.compose_system_prompt(
        tier=tier,
        operator_append=op.system_append,
        schema_digest=schema_text,
    )

    # 2) load history (if persistent session) and append the user message
    messages: List[Dict[str, Any]] = [{"role": "system", "content": system_prompt}]
    if session_id and session_store.enabled():
        prior = await session_store.list_messages(session_id, limit=5000)
        windowed = session_store.select_window(
            prior, budget_chars=op.history_budget_chars, min_recent=op.history_min_recent
        )
        for entry in session_store.history_for_openai(windowed):
            messages.append(entry)
        # persist this user turn now (so subsequent turns see it)
        await session_store.append_message(session_id, role="user", content=user_message)
    messages.append({"role": "user", "content": user_message})

    # 3) tools list for this tier
    tool_schemas = tools_pkg.schemas_for_tier(tier)

    # 4) initial SSE session event
    yield _sse(
        "session",
        {
            "tier": tier,
            "session_id": session_id,
            "model": model_override or cfg.OPENROUTER_MODEL,
            "tools": [s["function"]["name"] for s in tool_schemas],
            "max_rounds": op.max_rounds,
        },
    )

    captured_assistant_text_parts: List[str] = []
    captured_tool_calls_for_session: List[Dict[str, Any]] = []
    rounds = max(1, min(64, int(op.max_rounds or cfg.MAX_TOOL_ROUNDS)))

    for round_num in range(rounds):
        try:
            data = await call_openrouter(
                api_key=openrouter_api_key,
                messages=messages,
                tools=tool_schemas,
                model=model_override,
            )
        except Exception as e:
            yield _sse("error", {"message": str(e), "round": round_num})
            yield _sse("done", {})
            return
        choice = (data.get("choices") or [{}])[0]
        msg = choice.get("message") or {}
        tool_calls = msg.get("tool_calls") or None

        # Stream any "preamble" text the assistant emitted alongside tool calls
        raw_text = assistant_message_text(msg)
        preamble = raw_text.strip()
        if preamble:
            captured_assistant_text_parts.append(preamble)
            for i in range(0, len(preamble), cfg.TEXT_CHUNK_SIZE):
                yield _sse("content", {"delta": preamble[i : i + cfg.TEXT_CHUNK_SIZE]})

        if not tool_calls:
            # Final answer round.
            full_text = "".join(captured_assistant_text_parts) or raw_text
            if not (full_text or "").strip():
                yield _sse(
                    "error",
                    {
                        "message": (
                            "La API devolvió un mensaje vacío (sin texto ni tool_calls). "
                            "Revisa OPENROUTER_MODEL, cuota/créditos y logs del agente."
                        ),
                        "finish_reason": choice.get("finish_reason"),
                    },
                )
                yield _sse("done", {"rounds": round_num + 1})
                return
            if session_id and session_store.enabled():
                await session_store.append_message(
                    session_id,
                    role="assistant",
                    content=full_text,
                    tool_calls=captured_tool_calls_for_session or None,
                )
            yield _sse("done", {"finish_reason": choice.get("finish_reason"), "rounds": round_num + 1})
            return

        # We have tool calls; persist the assistant turn that requested them
        if session_id and session_store.enabled():
            await session_store.append_message(
                session_id,
                role="assistant",
                content=preamble,
                tool_calls=tool_calls,
            )

        # Echo back to model: assistant message with tool_calls
        messages.append(
            {
                "role": "assistant",
                "content": preamble,
                "tool_calls": tool_calls,
            }
        )

        # Execute each tool call
        for tc in tool_calls:
            if not isinstance(tc, dict):
                continue
            tc_id = tc.get("id") or ""
            fn = tc.get("function") or {}
            tname = fn.get("name") or ""
            raw_args = fn.get("arguments") or "{}"
            try:
                targs = json.loads(raw_args) if isinstance(raw_args, str) else (raw_args or {})
            except Exception:
                targs = {}
            captured_tool_calls_for_session.append(
                {"id": tc_id, "name": tname, "arguments_preview": str(raw_args)[:1000]}
            )
            yield _sse(
                "tool_call",
                {
                    "id": tc_id,
                    "name": tname,
                    "args_preview": str(raw_args)[:600],
                },
            )

            result = await _execute_tool(tname, targs, ctx, who=who)

            yield _sse(
                "tool_result",
                {
                    "id": tc_id,
                    "name": tname,
                    "ok": "error" not in result,
                    "preview": _truncate_for_sse_preview(result),
                },
            )

            tool_result_for_model = _truncate_for_model(result)
            tool_msg: Dict[str, Any] = {
                "role": "tool",
                "tool_call_id": tc_id,
                "name": tname,
                "content": json.dumps(tool_result_for_model, default=str),
            }
            messages.append(tool_msg)
            if session_id and session_store.enabled():
                await session_store.append_message(
                    session_id,
                    role="tool",
                    content=tool_msg["content"],
                    tool_call_id=tc_id,
                    name=tname,
                )

    # Out of rounds without final answer
    yield _sse("error", {"message": f"max tool rounds reached ({rounds}); cutting turn"})
    yield _sse("done", {"finish_reason": "max_rounds", "rounds": rounds})
