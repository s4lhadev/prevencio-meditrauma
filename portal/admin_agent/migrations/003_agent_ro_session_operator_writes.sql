-- agent_ro: además de SELECT (sql_execute read-only sobre el resto de tablas),
-- el propio proceso admin_agent usa AGENT_DB_DSN para escribir en tablas del schema agent.
-- Sin estos GRANT, /agent falla al guardar operator_config o al persistir sesiones/audit.

\echo '--- agent_ro: escritura en tablas internas del agente ---'

GRANT INSERT, UPDATE ON TABLE agent.session TO agent_ro;
GRANT INSERT, UPDATE ON TABLE agent.session_message TO agent_ro;
GRANT INSERT ON TABLE agent.audit TO agent_ro;
GRANT INSERT, UPDATE ON TABLE agent.operator_config TO agent_ro;

-- bigserial / serial en agent
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA agent TO agent_ro;
ALTER DEFAULT PRIVILEGES IN SCHEMA agent GRANT USAGE, SELECT ON SEQUENCES TO agent_ro;

\echo '--- agent_ro agent writes: done ---'
