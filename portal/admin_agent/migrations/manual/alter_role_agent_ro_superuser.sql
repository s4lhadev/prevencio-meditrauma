-- Ejecutar UNA VEZ como superusuario (p. ej. postgres), no en CI con AGENT_DB_ADMIN_DSN.
-- Si falla "permission denied to alter role", ignora: el agente aplica timeouts en cada query.

\echo '--- ALTER ROLE agent_ro (opcional, solo owner del rol) ---'

ALTER ROLE agent_ro SET statement_timeout = '60s';
ALTER ROLE agent_ro SET idle_in_transaction_session_timeout = '30s';
ALTER ROLE agent_ro SET log_statement = 'none';

\echo '--- ok ---'
