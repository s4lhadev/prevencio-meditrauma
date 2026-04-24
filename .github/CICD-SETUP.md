# CI/CD: GitHub Actions + Tailscale + VM

Misma idea que en Medisalut: el runner entra al tailnet, luego **SSH** a la VM (IP `100.x` o Magic DNS).

**Secrets** (repositorio → *Settings* → *Secrets and variables* → *Actions*):

| Secret | Contenido |
|--------|------------|
| `TAILSCALE_AUTHKEY` | `tskey-auth-...` (o usa OAuth con tags, ver [tailscale/github-action](https://github.com/tailscale/github-action)) |
| `DEPLOY_HOST` | IP o hostname en Tailscale (p. ej. `100.77.237.64`) |
| `DEPLOY_SSH_PRIVATE_KEY` | Clave **privada** (completa, sin contraseña en el fichero; ver sección SSH en `medisalut/.github/CICD-SETUP.md` si falla *libcrypto*) |

**`DEPLOY_PATH` y `DEPLOY_USER`:** en *Secrets* **o** en *Variables* (mismo nombre). Si solo los tienes en *Secrets*, el workflow los usa (antes solo se leía la pestaña *Variables*).

En la VM: remoto `git@github.com:…` (SSH a GitHub) y, si aplica, `sudo` para el servicio. El script hace `reset --hard` a `origin` (pierde divergencias y cambios locales *trackeados* en el server). **Node.js + npm** deben existir en el `PATH` del usuario de deploy: tras cada deploy se ejecuta `npm ci` (o `install`) y **`npm run build`** en el directorio Symfony (`portal/`, o `current/` si es tu layout) para generar `public/build/manifest.json` (no se versiona; sin esto, error 500 en twig/encore). `node-sass` usa **node-gyp**: hace falta **Python 3** (`apt install python3` en Debian) y, para compilar, `build-essential` (`make`/`g++`). El repositorio incluye `portal/.npmrc` con `python=python3` y el script exporta `PYTHON` por si el binario `python` no existe.

**Si el deploy falla con `unable to unlink … Permission denied`:** suele ser que bajo `portal/public/` (o similar) haya archivos de **otro usuario** (p. ej. `www-data`). **Una vez** en el servidor, como root: `sudo chown -R administrador:administrador <DEPLOY_PATH>`. El script intenta el mismo `chown` con `sudo -n` (requiere que `administrador` pueda usar `sudo` a ese path sin contraseña, o el comando manual sigue haciendo falta al menos una vez.

**Tras el deploy, HTTP 500 en `var/log` (*Permission denied*):** el script deja `var/` (p. ej. `portal/var`, `current/var`) con dueño `www-data` al final. Si aún falla, en el servidor: `sudo chown -R www-data:www-data` sobre esas carpetas. En **producción** usa `APP_ENV=prod` (no `dev.log`); el `.env` en el server no se commitea.

**Falta `public/build/manifest.json` (Webpack):** el build debe ejecutarse en el **mismo** directorio que el docroot; si Nginx/Apache apunta a `.../current/public` y en el clone solo hay `portal/`, deja alineado con `cd /ruta/prevencio && ln -sfn portal current`. El script ahora hace `npm run build` en `current/` (primero) y en `portal/`, sin duplicar la misma ruta real. Si aún no hay manifiesto, en la VM: `(cd ruta-symfony && npm ci && npm run build)`.

**Probar:** *Actions* → *Deploy (Tailscale + SSH)* → *Run workflow*.
