# CI/CD: GitHub Actions + Tailscale + VM

Misma idea que en Medisalut: el runner entra al tailnet, luego **SSH** a la VM (IP `100.x` o Magic DNS).

**Secrets** (repositorio → *Settings* → *Secrets and variables* → *Actions*):

| Secret | Contenido |
|--------|------------|
| `TAILSCALE_AUTHKEY` | `tskey-auth-...` (o usa OAuth con tags, ver [tailscale/github-action](https://github.com/tailscale/github-action)) |
| `DEPLOY_HOST` | IP o hostname en Tailscale (p. ej. `100.77.237.64`) |
| `DEPLOY_SSH_PRIVATE_KEY` | Clave **privada** (completa, sin contraseña en el fichero; ver sección SSH en `medisalut/.github/CICD-SETUP.md` si falla *libcrypto*) |
| `INFISICAL_TOKEN` | Service token de Infisical (entorno **Production**) | Opcional. Sin él, no se regenera `portal/admin_agent/.env` |

**`DEPLOY_PATH` y `DEPLOY_USER`:** en *Secrets* **o** en *Variables* (mismo nombre). Si solo los tienes en *Secrets*, el workflow los usa (antes solo se leía la pestaña *Variables*).

**Clave `/agent`:** define `ADMIN_AGENT_PAGE_KEY` en el `.env` del despliegue (mismo concepto que `admin_agent.page_key` en Symfony). Formulario de acceso independiente del login; `php bin/console cache:clear --env=prod` tras cambiar.

**Infisical:** solo hace falta el token. Tras el export fuerza `APP_PRODUCT=prevencion`. Mismo orden de instalación del CLI que en `medisalut` (`npx` → `~/.local/bin` → `apt` con `sudo -n`).

En el **mismo** proyecto/entorno de Infisical que inyecta `portal/admin_agent/.env` (p. ej. `production` o `prod`), el nombre de la clave **debe** coincidir: `ADMIN_AGENT_INTERNAL_URL`, `ADMIN_AGENT_SECRET`, `ADMIN_AGENT_PAGE_KEY` (y lo que tenga el Python). Si `ADMIN_AGENT_PAGE_KEY` no existe o está vacía, Symfony se queda con valor vacío y verás *Falta ADMIN_AGENT_PAGE_KEY*; el script intenta rellenarla desde `current/.env.dist` si el fichero `current/.env` o `portal/.env` **existía o se crea copiando** `.env.dist` cuando faltan (p. ej. primer deploy). Comprueba en la VM, tras un deploy, que `current/.env` tenga `ADMIN_AGENT_PAGE_KEY=…` (no vacío) y que en GitHub *Actions* el secret `INFISICAL_TOKEN` esté definido y sea del proyecto correcto.

**`ensurepip is not available` / fallo al crear `portal/admin_agent/.venv`:** en Debian, instala el venv de ese Python, p. ej. `sudo apt install -y python3-venv` o el paquete concreto que sugiere el error (`python3.13-venv`, etc.). Sin eso, `pip install` del admin agent no se ejecuta; el resto del deploy (Symfony, npm) sigue.

**`pip` compila `numpy` y falla (meson / “Python dependency not found”):** con Python 3.13, `numpy` 2.0.x a menudo no trae rueda y exige compilar; el repo fija `numpy>=2.1` para usar ruedas. Si aun así falla: `apt install -y build-essential pkg-config python3.13-dev` (ajusta a tu versión de `python3 -V`). El fallo de **sudo** al final (chown) es independiente: hace falta `NOPASSWD` como en el párrafo de abajo.

En la VM: remoto `git@github.com:…` (SSH a GitHub) y, si aplica, `sudo` para el servicio. El script hace `reset --hard` a `origin` (pierde divergencias y cambios locales *trackeados* en el server). **Node.js + npm** deben existir en el `PATH` del usuario de deploy: tras cada deploy se ejecuta `npm ci` (o `install`) y **`npm run build`** en el directorio Symfony (`portal/`, o `current/` si es tu layout) para generar `public/build/manifest.json` (no se versiona; sin esto, error 500 en twig/encore). `node-sass` usa **node-gyp**: hace falta **Python 3** (`apt install python3` en Debian) y, para compilar, `build-essential` (`make`/`g++`). El repositorio incluye `portal/.npmrc` con `python=python3` y el script exporta `PYTHON` por si el binario `python` no existe.

**Si el deploy falla con `unable to unlink … Permission denied`:** suele ser que bajo `portal/public/` (o similar) haya archivos de **otro usuario** (p. ej. `www-data`). **Una vez** en el servidor, como root: `sudo chown -R administrador:administrador <DEPLOY_PATH>`. El script intenta el mismo `chown` con `sudo -n` (requiere que `administrador` pueda usar `sudo` a ese path sin contraseña, o el comando manual sigue haciendo falta al menos una vez.

**HTTP 500 y en el log de Apache: `Unable to write in the "cache" directory` (`var/cache/prod`):** Apache corre como `www-data`; el script deja `var/` con `chown` **usuario_de_deploy:www-data** y directorios `2775` para que el grupo pueda escribir. Hace falta `sudo` (en CI suele ser `sudo -n` → en la VM configura `NOPASSWD` para el usuario de deploy sobre ese path) o, **una vez** a mano: `sudo chown -R deploy:www-data RUTA/current/var` y `sudo find RUTA/current/var -type d -exec chmod 2775 {} \;`. No uses solo `www-data:www-data` en todo el repo o el siguiente `cache:clear` vía deploy puede fallar. En **producción** `APP_ENV=prod`; el `.env` en el server no se commitea.

**Permisos sin tocar `sudoers` (recomendado en Debian):** el usuario con el que hace SSH el workflow debe ser miembro de **`www-data`**: `sudo usermod -aG www-data administrador` (ajusta el nombre). La **siguiente** conexión SSH (cada run de GitHub) carga el grupo. El script hace `chown usuario:www-data` **sin** `sudo` para los ficheros del deploy; basta con eso en muchas instalaciones. Si aún mezclan dueños (p. ej. raíz bajo `vendor/`), un `sudo chown` puntual o el fichero de `sudoers` de abajo.

**`ERROR: sudo -n chown falló` / chown a `…:www-data` falló** (Actions): o bien aplicas lo anterior (`usermod -aG www-data` + nuevo deploy) o dejas **NOPASSWD** para que el script use `sudo -n` cuando haga falta. Hay un ejemplo en **`.github/sudoers/99-prevencion-deploy`**. Instala con:

`sudo install -m 440 -o root -g root /ruta/al/repo/.github/sudoers/99-prevencion-deploy /etc/sudoers.d/99-prevencion-deploy`

Edítalo antes si el usuario de deploy no se llama `administrador`. Valida con `sudo visudo -c -f /etc/sudoers.d/99-prevencion-deploy`. Prueba: `sudo -n chown -h` (no debe pedir clave). Si un binario falla, `type chown chmod find chgrp systemctl` y añade la ruta que use tu sistema. Mientras no esté esto, el job termina en rojo y el sitio puede dar 500 sin el `chown` manual.

**Falta `public/build/manifest.json` (Webpack):** el build debe ejecutarse en el **mismo** directorio que el docroot; si Nginx/Apache apunta a `.../current/public` y en el clone solo hay `portal/`, deja alineado con `cd /ruta/prevencio && ln -sfn portal current`. El script ahora hace `npm run build` en `current/` (primero) y en `portal/`, sin duplicar la misma ruta real. Si aún no hay manifiesto, en la VM: `(cd ruta-symfony && npm ci && npm run build)`.

**Probar:** *Actions* → *Deploy (Tailscale + SSH)* → *Run workflow*.
