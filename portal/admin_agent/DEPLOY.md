# Despliegue en producción (Prevención)

## Para qué sirve `ADMIN_AGENT_SECRET`

Es una **clave compartida** entre:

- el **microservicio Python** (`uvicorn`, `ADMIN_AGENT_SECRET` en `portal/admin_agent/.env`), y  
- **Symfony** (`ADMIN_AGENT_SECRET` en `portal/.env` o `.env.local`, vía `config/packages/admin_agent.yaml`).

El proxy PHP envía `X-Admin-Agent-Secret` en cada `POST` al servicio local. Sin el secreto correcto, el Python responde **401**. El servicio debe escuchar solo **`127.0.0.1:9102`**, no exponerse a internet.

`OPENROUTER_API_KEY` va **solo** en `admin_agent/.env` (no en el `.env` de Symfony).

## Checklist producción

1. `portal/admin_agent/.env` en el servidor con `ADMIN_AGENT_SECRET`, `OPENROUTER_API_KEY`, `APP_PRODUCT=prevencion`.
2. Mismo valor de `ADMIN_AGENT_SECRET` en `portal/.env` (o `.env.local` en prod):
   - `ADMIN_AGENT_INTERNAL_URL=http://127.0.0.1:9102`
   - `ADMIN_AGENT_SECRET=...`
3. `curl -s http://127.0.0.1:9102/health`
4. Tras cambiar `.env`: `php bin/console cache:clear --env=prod`

## Generar un secreto nuevo

```bash
python3 -c "import secrets; print(secrets.token_urlsafe(48))"
```

Usa **otro** distinto al de Medisalut.

## systemd (ejemplo)

Ver `systemd/prevencion-admin-agent.service.example`.

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now prevencion-admin-agent.service
```
