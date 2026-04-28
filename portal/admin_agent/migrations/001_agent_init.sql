-- Schema for the admin assistant ("Hugo"-style).
-- Idempotent: safe to re-run on every deploy.
--
-- Run as a Postgres user with CREATE on the database, e.g.:
--   psql "postgresql://postgres:****@127.0.0.1:5432/prevencion" -f 001_agent_init.sql
-- The deploy script wraps this with:
--   psql -v ON_ERROR_STOP=1 -f 001_agent_init.sql

\echo '--- agent: schema + tables ---'

CREATE SCHEMA IF NOT EXISTS agent;

CREATE TABLE IF NOT EXISTS agent.session (
    id          uuid PRIMARY KEY,
    who         text NOT NULL,
    tier        text NOT NULL CHECK (tier IN ('user', 'dev')),
    title       text,
    created_at  timestamptz NOT NULL DEFAULT now(),
    updated_at  timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS ix_agent_session_who_updated
    ON agent.session (who, updated_at DESC);

CREATE TABLE IF NOT EXISTS agent.session_message (
    id            bigserial PRIMARY KEY,
    session_id    uuid NOT NULL REFERENCES agent.session(id) ON DELETE CASCADE,
    seq           integer NOT NULL,
    role          text NOT NULL CHECK (role IN ('system', 'user', 'assistant', 'tool')),
    content       text NOT NULL DEFAULT '',
    tool_calls    jsonb,
    tool_call_id  text,
    name          text,
    created_at    timestamptz NOT NULL DEFAULT now(),
    UNIQUE (session_id, seq)
);

CREATE INDEX IF NOT EXISTS ix_agent_msg_session_seq
    ON agent.session_message (session_id, seq);

CREATE TABLE IF NOT EXISTS agent.audit (
    id                 bigserial PRIMARY KEY,
    session_id         uuid REFERENCES agent.session(id) ON DELETE SET NULL,
    who                text NOT NULL,
    tier               text NOT NULL,
    tool               text NOT NULL,
    args_hash          text NOT NULL,
    args_preview       text,
    result_hash        text NOT NULL,
    result_size_bytes  integer NOT NULL,
    elapsed_ms         integer NOT NULL,
    ok                 boolean NOT NULL,
    error_text         text,
    created_at         timestamptz NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS ix_agent_audit_created
    ON agent.audit (created_at DESC);
CREATE INDEX IF NOT EXISTS ix_agent_audit_tool
    ON agent.audit (tool, created_at DESC);

CREATE TABLE IF NOT EXISTS agent.operator_config (
    id                    smallint PRIMARY KEY DEFAULT 1 CHECK (id = 1),
    version               integer NOT NULL DEFAULT 0,
    system_append         text NOT NULL DEFAULT '',
    max_rounds            integer NOT NULL DEFAULT 12,
    history_budget_chars  integer NOT NULL DEFAULT 60000,
    history_min_recent    integer NOT NULL DEFAULT 10,
    updated_at            timestamptz NOT NULL DEFAULT now(),
    updated_by            text NOT NULL DEFAULT 'init'
);

INSERT INTO agent.operator_config (id, version, system_append, max_rounds, history_budget_chars, history_min_recent)
VALUES (1, 0, '', 12, 60000, 10)
ON CONFLICT (id) DO NOTHING;

\echo '--- agent: read-only role ---'

-- Create the role idempotently (no-op when it already exists).
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_roles WHERE rolname = 'agent_ro') THEN
        -- IMPORTANT: change this password manually after creation:
        --   ALTER ROLE agent_ro WITH PASSWORD 'NEW_PASSWORD';
        -- Or pre-create the role in your DB before running this script.
        EXECUTE 'CREATE ROLE agent_ro LOGIN PASSWORD ''CHANGE_ME_AFTER_CREATE''';
    END IF;
END$$;

-- Read access to every existing schema EXCEPT agent.audit (writes), and never grant
-- write/DDL. The Python sql_execute tool also enforces a denylist of PII patterns;
-- the role is the real barrier.
GRANT USAGE ON SCHEMA public TO agent_ro;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO agent_ro;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT SELECT ON TABLES TO agent_ro;

GRANT USAGE ON SCHEMA agent TO agent_ro;
GRANT SELECT ON ALL TABLES IN SCHEMA agent TO agent_ro;
ALTER DEFAULT PRIVILEGES IN SCHEMA agent GRANT SELECT ON TABLES TO agent_ro;

-- Revoke SELECT on PII tables (defense in depth on top of the Python denylist).
-- These DO blocks only revoke if the table actually exists, so the script is
-- safe across environments where the schema has not been deployed yet.
DO $$
DECLARE
    r record;
BEGIN
    FOR r IN
        SELECT n.nspname AS schema_name, c.relname AS table_name
        FROM pg_class c
        JOIN pg_namespace n ON n.oid = c.relnamespace
        WHERE c.relkind IN ('r', 'v', 'm')
          AND n.nspname NOT IN ('pg_catalog', 'information_schema', 'agent')
          AND (
              c.relname ILIKE '%informe_medico%'
              OR c.relname ILIKE '%historia_clinica%'
              OR c.relname ILIKE '%paciente%'
              OR c.relname ILIKE '%vigilancia%'
              OR c.relname ILIKE '%aptitud%'
              OR c.relname ILIKE '%reconocimiento%'
          )
    LOOP
        EXECUTE format('REVOKE ALL ON %I.%I FROM agent_ro',
                       r.schema_name, r.table_name);
        RAISE NOTICE 'Revoked agent_ro on %.%', r.schema_name, r.table_name;
    END LOOP;
END$$;

-- Per-statement timeout for any session as agent_ro (cap at 60s; the tool also
-- enforces a per-call SET statement_timeout that may be shorter).
ALTER ROLE agent_ro SET statement_timeout = '60s';
ALTER ROLE agent_ro SET idle_in_transaction_session_timeout = '30s';
ALTER ROLE agent_ro SET log_statement = 'none';

\echo '--- agent: done ---'
