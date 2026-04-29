"""Centralized config: env vars, defaults, allowlists. Single source of truth."""
from __future__ import annotations

import os
from pathlib import Path
from typing import List, Optional


def _env_int(name: str, default: int) -> int:
    raw = (os.getenv(name) or "").strip()
    if not raw:
        return default
    try:
        return int(raw)
    except ValueError:
        return default


def _env_bool(name: str, default: bool = False) -> bool:
    raw = (os.getenv(name) or "").strip().lower()
    if raw in ("1", "true", "yes", "on"):
        return True
    if raw in ("0", "false", "no", "off"):
        return False
    return default


def _env_list(name: str, default: Optional[List[str]] = None, sep: str = ",") -> List[str]:
    raw = (os.getenv(name) or "").strip()
    if not raw:
        return list(default or [])
    return [p.strip() for p in raw.split(sep) if p.strip()]


# --- model / openrouter ---------------------------------------------------------------
OPENROUTER_MODEL = (os.getenv("OPENROUTER_MODEL") or "anthropic/claude-sonnet-4.6").strip()
OPENROUTER_MODEL_FALLBACK = (
    os.getenv("OPENROUTER_MODEL_FALLBACK") or "openai/gpt-4o-mini"
).strip()
OPENROUTER_HTTP_REFERER = (
    os.getenv("OPENROUTER_HTTP_REFERER")
    or "https://github.com/s4lhadev/prevencio-meditrauma"
).strip()
OPENROUTER_API_URL = "https://openrouter.ai/api/v1/chat/completions"


# --- agentic loop limits --------------------------------------------------------------
MAX_TOOL_ROUNDS = _env_int("AGENT_MAX_TOOL_ROUNDS", 12)
TOOL_CONTENT_MAX = _env_int("AGENT_TOOL_CONTENT_MAX", 600_000)  # bytes per tool result fed to LLM
HISTORY_BUDGET_CHARS = _env_int("AGENT_HISTORY_BUDGET_CHARS", 60_000)
HISTORY_MIN_RECENT = _env_int("AGENT_HISTORY_MIN_RECENT", 10)
TEXT_CHUNK_SIZE = 16  # SSE chunk size (chars)


# --- sql ------------------------------------------------------------------------------
def _read_agent_db_dsn_from_dotenv_file() -> str:
    """Si load_dotenv no pobló os.environ (clave ausente, formato raro), último recurso: leer el .env junto a este módulo."""
    p = Path(__file__).resolve().parent / ".env"
    if not p.is_file():
        return ""
    try:
        raw = p.read_text(encoding="utf-8-sig", errors="replace")
    except OSError:
        return ""
    candidates = (
        "AGENT_DB_DSN",
        "AGENT_DATABASE_DSN",  # alias posible en Infisical / otros equipos
    )
    for line in raw.splitlines():
        s = line.strip()
        if not s or s.startswith("#"):
            continue
        if s.lower().startswith("export "):
            s = s[7:].strip()
        if "=" not in s:
            continue
        key, _, rhs = s.partition("=")
        key = key.strip().replace("\ufeff", "")
        if key not in candidates:
            continue
        v = rhs.strip()
        if len(v) >= 2 and v[0] == v[-1] and v[0] in "\"'":
            v = v[1:-1]
        return v.strip()
    return ""


_env_ag = (os.getenv("AGENT_DB_DSN") or "").strip()
_env_alias = (os.getenv("AGENT_DATABASE_DSN") or "").strip()
AGENT_DB_DSN = _env_ag or _env_alias or _read_agent_db_dsn_from_dotenv_file()
SQL_STATEMENT_TIMEOUT_S = _env_int("AGENT_SQL_TIMEOUT_S", 30)
MAX_SQL_ROWS = _env_int("AGENT_MAX_SQL_ROWS", 200)
SCHEMA_DIGEST_TTL_S = _env_int("AGENT_SCHEMA_DIGEST_TTL_S", 300)

# Pattern fragments matched (case-insensitive, substring) against table names.
# A table name containing ANY of these strings is denied at sql_execute level
# (the agent_ro role is the real barrier; this is defense in depth + clear errors).
SQL_DENYLIST_PATTERNS: List[str] = _env_list(
    "AGENT_SQL_DENYLIST_PATTERNS",
    default=[
        "informe_medico",
        "historia_clinica",
        "paciente",
        "vigilancia",
        "aptitud",
        "reconocimiento",
    ],
)


# --- shell ----------------------------------------------------------------------------
SHELL_DISABLED = _env_bool("AGENT_SHELL_DISABLE", default=False)
SHELL_TIMEOUT_S = _env_int("AGENT_SHELL_TIMEOUT_S", 180)
SHELL_MAX_OUTPUT = _env_int("AGENT_SHELL_MAX_OUTPUT", 1_500_000)


# --- log allowlist --------------------------------------------------------------------
def _default_log_paths() -> dict:
    """
    Logical name → absolute path. Override individually with AGENT_LOG_PATH_<NAME>=...
    or replace the whole map with AGENT_LOG_PATHS=name1=path1,name2=path2.
    """
    base_paths = {
        "apache_error": "/var/log/apache2/error.log",
        "apache_access": "/var/log/apache2/access.log",
        "symfony_prod_current": "/home/administrador/prevencio/prevencio-meditrauma/current/var/log/prod.log",
        "symfony_prod_portal": "/home/administrador/prevencio/prevencio-meditrauma/portal/var/log/prod.log",
        "agent_uvicorn": "/tmp/prevencion-admin-agent.log",
    }
    raw = (os.getenv("AGENT_LOG_PATHS") or "").strip()
    if raw:
        out = {}
        for kv in raw.split(","):
            if "=" in kv:
                k, v = kv.split("=", 1)
                k, v = k.strip(), v.strip()
                if k and v:
                    out[k] = v
        return out
    for k in list(base_paths.keys()):
        ov = (os.getenv(f"AGENT_LOG_PATH_{k.upper()}") or "").strip()
        if ov:
            base_paths[k] = ov
    return base_paths


LOG_PATHS = _default_log_paths()
JOURNAL_UNITS_ALLOWED: List[str] = _env_list(
    "AGENT_JOURNAL_UNITS",
    default=["prevencion-admin-agent", "apache2", "postgresql"],
)


# --- http_request ---------------------------------------------------------------------
HTTP_ALLOWED_HOSTS: List[str] = _env_list(
    "AGENT_HTTP_ALLOWED_HOSTS",
    default=[
        "127.0.0.1",
        "localhost",
        "app.mdtprevencion.com",
        "portal.mdtprevencion.com",
    ],
)
HTTP_MAX_BODY = _env_int("AGENT_HTTP_MAX_BODY", 500_000)


# --- web search -----------------------------------------------------------------------
WEB_INCLUDE_DOMAINS: Optional[List[str]] = (
    None
    if (os.getenv("AGENT_WEB_INCLUDE_DOMAINS") or "").strip() in ("", "*")
    else _env_list("AGENT_WEB_INCLUDE_DOMAINS")
)
WEB_FETCH_MAX_URLS = _env_int("AGENT_WEB_FETCH_MAX_URLS", 4)
WEB_FETCH_MAX_CHARS_PER_URL = _env_int("AGENT_WEB_FETCH_MAX_CHARS_PER_URL", 50_000)


# --- symfony_console ------------------------------------------------------------------
SYMFONY_CONSOLE_ALLOWED: List[str] = _env_list(
    "AGENT_SYMFONY_CONSOLE_ALLOWED",
    default=[
        "debug:router",
        "debug:container",
        "debug:config",
        "debug:event-dispatcher",
        "debug:translation",
        "doctrine:schema:validate",
        "doctrine:mapping:info",
        "cache:pool:list",
        "about",
    ],
)
SYMFONY_APP_PATHS: List[str] = _env_list(
    "AGENT_SYMFONY_APP_PATHS",
    default=[
        "/home/administrador/prevencio/prevencio-meditrauma/current",
        "/home/administrador/prevencio/prevencio-meditrauma/portal",
    ],
)


# --- repo / paths ---------------------------------------------------------------------
def _default_repo_root() -> str:
    # admin_agent/ is at $REPO/portal/admin_agent. Repo root is two parents up.
    return str(Path(__file__).resolve().parent.parent.parent)


REPO_ROOT = (os.getenv("AGENT_REPO_ROOT") or _default_repo_root()).strip()
CODEBASE_ROOTS: List[str] = _env_list(
    "AGENT_CODEBASE_ROOTS",
    default=[
        os.path.join(REPO_ROOT, "current"),
        os.path.join(REPO_ROOT, "portal"),
    ],
)


# --- secret ---------------------------------------------------------------------------
ADMIN_AGENT_SECRET = (os.getenv("ADMIN_AGENT_SECRET") or "").strip()


def secret_fingerprint(secret: str) -> str:
    """Length + 8-hex SHA-256 prefix (no leak of the secret itself)."""
    if not secret:
        return "empty"
    import hashlib

    return f"len={len(secret)} sha256_8={hashlib.sha256(secret.encode()).hexdigest()[:8]}"
