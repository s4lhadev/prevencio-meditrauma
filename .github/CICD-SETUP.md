# CI/CD: GitHub Actions + Tailscale + VM

Misma idea que en Medisalut: el runner entra al tailnet, luego **SSH** a la VM (IP `100.x` o Magic DNS).

**Secrets** (repositorio → *Settings* → *Secrets and variables* → *Actions*):

| Secret | Contenido |
|--------|------------|
| `TAILSCALE_AUTHKEY` | `tskey-auth-...` (o usa OAuth con tags, ver [tailscale/github-action](https://github.com/tailscale/github-action)) |
| `DEPLOY_HOST` | IP o hostname en Tailscale (p. ej. `100.77.237.64`) |
| `DEPLOY_SSH_PRIVATE_KEY` | Clave **privada** del usuario de deploy |

**`DEPLOY_PATH` y `DEPLOY_USER`:** en *Secrets* **o** en *Variables* (mismo nombre). Si solo los tienes en *Secrets*, el workflow los usa (antes solo se leía la pestaña *Variables*).

En la VM: el usuario debe poder `git pull` (deploy key o token) y, si aplica, `sudo systemctl restart prevencion-admin-agent`.

**Probar:** *Actions* → *Deploy (Tailscale + SSH)* → *Run workflow*.
