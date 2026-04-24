# CI/CD: GitHub Actions + Tailscale + VM

Misma idea que en Medisalut: el runner entra al tailnet, luego **SSH** a la VM (IP `100.x` o Magic DNS).

**Secrets** (repositorio → *Settings* → *Secrets and variables* → *Actions*):

| Secret | Contenido |
|--------|------------|
| `TAILSCALE_AUTHKEY` | `tskey-auth-...` (o usa OAuth con tags, ver [tailscale/github-action](https://github.com/tailscale/github-action)) |
| `DEPLOY_HOST` | IP o hostname en Tailscale (p. ej. `100.77.237.64`) |
| `DEPLOY_SSH_PRIVATE_KEY` | Clave **privada** (completa, sin contraseña en el fichero; ver sección SSH en `medisalut/.github/CICD-SETUP.md` si falla *libcrypto*) |

**`DEPLOY_PATH` y `DEPLOY_USER`:** en *Secrets* **o** en *Variables* (mismo nombre). Si solo los tienes en *Secrets*, el workflow los usa (antes solo se leía la pestaña *Variables*).

En la VM: remoto `git@github.com:…` (SSH a GitHub) y, si aplica, `sudo` para el servicio. El script hace `reset --hard` a `origin` (pierde divergencias y cambios locales *trackeados* en el server).

**Probar:** *Actions* → *Deploy (Tailscale + SSH)* → *Run workflow*.
