# Prevención — asistente admin (local API)

El panel en `/agent` hace de proxy a este servicio en **127.0.0.1** (no exponer a internet).

**Producción:** [DEPLOY.md](DEPLOY.md) y `systemd/prevencion-admin-agent.service.example`.

## Arranque

```bash
cd admin_agent
python3 -m venv .venv && source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
uvicorn app:app --host 127.0.0.1 --port 9102
```

Configuración PHP: `config/packages/admin_agent.yaml` y variables en `.env` (ver abajo). El secreto debe coincidir con `ADMIN_AGENT_SECRET` de este `.env`.

**Puerto por defecto:** 9102 (Medisalut usa 9101 en la misma VM).

## .env (Symfony)

Añade a `.env` / `.env.local`:

```
ADMIN_AGENT_INTERNAL_URL=http://127.0.0.1:9102
ADMIN_AGENT_SECRET=el_mismo_que_en_admin_agent/.env
```

## Health

`GET http://127.0.0.1:9102/health`

## Índice (RAG)

Mismo criterio que en Medisalut: `POST /v1/reindex` incremental o `full`, estado en `GET /v1/index/status`. El índice vive en `admin_agent/.codebase_index.sqlite`. Por defecto se indexa el directorio `portal/`; para monorepo completo define `CODEBASE_ROOT` en `.env`.
