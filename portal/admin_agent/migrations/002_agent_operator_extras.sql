-- Paridad Medisalut/Prevención: modelo y temperatura en agent.operator_config
\echo '--- agent.operator_config: openrouter_model, temperature ---'

ALTER TABLE agent.operator_config
  ADD COLUMN IF NOT EXISTS openrouter_model text NOT NULL DEFAULT '';

ALTER TABLE agent.operator_config
  ADD COLUMN IF NOT EXISTS temperature double precision NOT NULL DEFAULT 0.2;

\echo '--- 002 done ---'
