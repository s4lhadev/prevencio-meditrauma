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

## Privilegios en la VM (`run_shell` + `sudo`)

### Opción A — Contraseña vía Infisical (elevación amplia)

Si en Infisical defines **`VM_DEPLOY_SUDO_PASSWORD`** (mismo proyecto/entorno que vuelca `admin_agent/.env`), el deploy la copia a **`~/.deploy_sudo_password`** y **la deja en el `.env` del agente**. El servicio Python usará **`sudo -S`** en `run_shell` y en lecturas `journal:` de `read_log` cuando haga falta. **Riesgo:** el modelo + APIs externas pueden ejecutar órdenes como root; la contraseña vive en memoria y en el fichero `.env` del agente.

### Opción B — `sudo` sin contraseña (NOPASSWD)

Para que pueda ejecutar órdenes elevadas **sin contraseña en el agente**, configura el **mismo usuario**
que en la unidad `systemd` (p. ej. `User=administrador`).

#### B.1 — NOPASSWD amplio

Como root, edita sudoers de forma segura:

```bash
sudo visudo -f /etc/sudoers.d/prevencion-admin-agent
```

Ejemplo **amplio** (solo si aceptas el riesgo: el modelo llama APIs externas y el proceso puede ejecutar cualquier orden como root vía sudo):

```sudoers
administrador ALL=(ALL) NOPASSWD: ALL
```

*(Sustituye `administrador` por el usuario real de `User=` en el `.service`.)*

Comprueba:

```bash
sudo -n true && echo OK
```

Con **NOPASSWD**, en `run_shell` conviene **`sudo -n comando`**. Con **VM_DEPLOY_SUDO_PASSWORD**, puedes usar **`sudo comando`** (el agente envuelve `sudo -S`).

### Opción B.2 — Solo `NOPASSWD` para binarios concretos (más seguro)

Restringe a rutas absolutas, p. ej. `ss`, `systemctl`, `journalctl`:

```sudoers
administrador ALL=(root) NOPASSWD: /usr/bin/ss, /usr/bin/systemctl, /usr/bin/journalctl
```

(`which ss` etc. pueden variar en tu Debian.)

### Opción C — Servidor bajo tu responsabilidad

Ejecutar uvicorn como `root` (`User=root` en systemd) da “acceso total” al shell del agente con el mismo riesgo operativo y de cumplimiento; no lo recomendamos salvo entornos totalmente aislados.

### Riesgo que debes asumir

Un agente con **sudo amplio** + modelo remoto puede, en principio, ejecutar comandos destructivos
si el operador o el modelo se equivocan. Aísla la VM, backups, y limita tier/herramientas si
hace falta.


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
