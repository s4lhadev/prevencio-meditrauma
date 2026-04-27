#!/usr/bin/env bash
# Ejecuta en la VM: bash -s < remote-deploy.sh -- DEPLOY_DIR REPO_URL BRANCH
# DEPLOY_DIR: raíz del repo (donde está .git) o la carpeta portal/ (se detecta el toplevel con git)
# Alinea con origin/BRANCH (reset --hard en servidor: ver medisalut remote-deploy.sh)
set -euo pipefail
START="${1:-}"
REPO_URL="${2:-}"
BRANCH="${3:-}"
if [ -z "$START" ] || [ -z "$REPO_URL" ] || [ -z "$BRANCH" ]; then
  echo "Uso: bash -s < remote-deploy.sh -- DEPLOY_DIR REPO_URL BRANCH"
  exit 1
fi
cd "$START" || exit 1
if [ -d .git ]; then
  TOP="$(pwd -P)"
else
  TOP="$(git rev-parse --show-toplevel 2>/dev/null || true)"
  if [ -z "$TOP" ]; then
    echo "No se encuentra repositorio git cerca de $START"
    exit 1
  fi
  cd "$TOP"
fi

# Solo GitHub: fichero dedicado. Claves oficiales: https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/githubs-ssh-key-fingerprints
_gh_known_hosts_file() {
  local f="$HOME/.ssh/known_hosts.github"
  mkdir -p "$HOME/.ssh"
  chmod 700 "$HOME/.ssh" 2>/dev/null || true
  umask 077
  cat >"$f" <<'END_GH_KH'
github.com ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIOMqqnkVzrm0SdG6UOoqKLsabgH5C9okWi0dh2l9GKJl
github.com ecdsa-sha2-nistp256 AAAAE2VjZHNhLXNoYTItbmlzdHAyNTYAAAAIbmlzdHAyNTYAAABBBEmKSENjQEezOmxkZMy7opKgwFB9nkt5YRrYMjNuG5N87uRgg6CLrbo5wAdT/y6v0mKV0U2w0WZ2YB/++Tpockg=
github.com ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABgQCj7ndNxQowgcQnjshcLrqPEiiphnt+VTTvDP6mHBL9j1aNUkY4Ue1gvwnGLVlOhGeYrnZaMgRK6+PKCUXaDbC7qtbW8gIkhL7aGCsOr/C56SJMy/BCZfxd1nWzAOxSDPgVsmerOBYfNqltV9/hWCqBywINIR+5dIg6JTJ72pcEpEjcYgXkE2YEFXV1JHnsKgbLWNlhScqb2UmyRkQyytRLtL+38TGxkxCflmO+5Z8CSSNY7GidjMIZ7Q4zMjA2n1nGrlTDkzwDCsw+wqFPGQA179cnfGWOWRVruj16z6XyvxvjJwbz0wQZ75XK5tKSb7FNyeIEs4TT4jk+S4dhPeAUC5y+bDYirYgM4GC7uEnztnZyaVWQ7B381AK4Qdrwt51ZqExKbQpTUNn+EjqoTwvqNj4kqx5QUCI0ThS/YkOxJCXmPUWZbhjpCg56i+2aB6CmK2JGhn57K5mj0MNdBXA4/WnwH6XoPWJzK5Nyu2zB3nAZp+S5hpQs+p1vN1/wsjk=
END_GH_KH
  chmod 600 "$f" 2>/dev/null || true
}
_gh_known_hosts_file
KNOWN_HOSTS_GH="$HOME/.ssh/known_hosts.github"
GH_KEY=""
for f in "$HOME/.ssh/github_deploy" "$HOME/.ssh/mdt_debian" "$HOME/.ssh/id_ed25519" "$HOME/.ssh/id_rsa"; do
  [ -f "$f" ] || continue
  GH_KEY="$f"
  break
done
if [ -n "$GH_KEY" ]; then
  # shellcheck disable=SC2139
  export GIT_SSH_COMMAND="ssh -i $GH_KEY -o IdentitiesOnly=yes -o BatchMode=yes -o StrictHostKeyChecking=yes -o UserKnownHostsFile=$KNOWN_HOSTS_GH"
else
  export GIT_SSH_COMMAND="ssh -o BatchMode=yes -o StrictHostKeyChecking=yes -o UserKnownHostsFile=$KNOWN_HOSTS_GH"
  echo "Aviso: no hay ~/.ssh/{github_deploy,mdt_debian,id_ed25519,id_rsa}; fallará si no configuras una clave para Git."
fi

git remote set-url origin "$REPO_URL" 2>/dev/null || true
git fetch --prune origin
if ! git show-ref --verify --quiet "refs/remotes/origin/${BRANCH}"; then
  echo "No existe origin/${BRANCH} tras el fetch. ¿La rama está en GitHub?"
  exit 1
fi
# Si php/nginx escribió en portal/public/ como www-data, git no puede "unlink" sin ser dueño
if command -v sudo >/dev/null 2>&1; then
  sudo -n chown -R "$(id -u -n):$(id -g -n)" "$TOP" 2>/dev/null || {
    echo "Falta poder hacer chown del árbol (p. ej. sudo sin contraseña) o ejecuta UNA VEZ en el servidor:"
    echo "  sudo chown -R $(id -u -n):$(id -g -n) $TOP"
  }
fi
git checkout "$BRANCH" 2>/dev/null || git checkout -b "$BRANCH" "origin/${BRANCH}"
git reset --hard "origin/${BRANCH}"

# admin_agent/.env desde Infisical (INFISICAL_TOKEN vía CI; ver CICD-SETUP)
if [ -f "$TOP/.github/scripts/infisical-admin-agent-env.sh" ]; then
  bash "$TOP/.github/scripts/infisical-admin-agent-env.sh" "$TOP" || exit 1
fi
# No ejecutar "composer dump-env prod" en deploy: en varios entornos rompe .env / .env.local.php y deja HTTP 500.
# Si usas .env.local.php, tras un deploy con secretos nuevos ejecuta a mano en la VM, en portal/ y current/:
#   composer dump-env prod
# o renombra/elimina .env.local.php un momento y deja que bootstrap cargue .env (cuidado con secretos inexistentes en .env).

# vendor/ — app.mdtprevencion.com usa current/public; portal.mdt* usa portal/public. Ambas apps necesitan autoload al día.
# Si falla (p. ej. "Could not resolve host: flex.symfony.com"), reintentar con --no-plugins y --no-scripts:
# sin plugins no existe symfony-cmd (lo pone flex); --no-scripts evita @auto-scripts (cache:clear/assets:install vía flex).
# cache:clear y assets:install se hacen más abajo con bin/console.
_composer_install_dir() {
  local d="$1"
  (cd "$d" && composer install --no-dev --no-interaction --optimize-autoloader) && return 0
  echo "Aviso: composer falló en $d (a menudo DNS a flex.symfony.com). Reintentando con --no-plugins --no-scripts…" >&2
  (cd "$d" && composer install --no-dev --no-interaction --optimize-autoloader --no-plugins --no-scripts) || return 1
  return 0
}
if command -v composer >/dev/null 2>&1; then
  for _c in "$TOP/current" "$TOP/portal"; do
    [ -f "$_c/composer.json" ] || continue
    _composer_install_dir "$_c" || {
      echo "ERROR: composer install falló en $_c (revisa red/DNS; en la VM: echo nameserver 8.8.8.8 | sudo tee /etc/resolv.conf.d/… o arregla resolved)." >&2
      exit 1
    }
  done
  # Sustituye auto-scripts (symfony-cmd) si el reintento fue sin plugins/scripts; idempotente si ya corrieron.
  for _a in "$TOP/current" "$TOP/portal"; do
    [ -f "$_a/bin/console" ] || continue
    (cd "$_a" && php bin/console assets:install public --env=prod --no-interaction) 2>/dev/null || true
  done
else
  echo "Aviso: composer no está en PATH; omite composer install (riesgo de 500 si vendor desactualizado)." >&2
fi

# Webpack Encore: public/build/ en .gitignore. El vhost a menudo apunta a current/; hay que
# construir en *cada* ruta con package.json (current antes que portal), sin duplicar misma ruta (realpath).
# Webpack/Encore con Dart Sass (paquete "sass") no requiere node-gyp; dejamos python por si otro módulo lo pide
if command -v python3 >/dev/null 2>&1; then
  export PYTHON="${PYTHON:-$(command -v python3)}"
  export npm_config_python="$PYTHON"
fi
NPM_BUILT=0
SEEN=" "
if command -v npm >/dev/null 2>&1; then
  for d in "$TOP/current" "$TOP/portal" "$TOP"; do
    [ -f "$d/package.json" ] && [ -d "$d/public" ] || continue
    r="$(readlink -f "$d" 2>/dev/null || echo "$d")"
    case "$SEEN" in *" ${r} "*) continue ;; esac
    SEEN="${SEEN}${r} "
    (cd "$d" && {
      if [ -f package-lock.json ]; then
        if ! npm ci --no-audit --no-fund; then
          echo "ERROR: npm ci falló en $d. Suele deberse a: npm<7 con lock v3, o lock no commiteado/actualizado."
          echo "En la VM: node -v; npm -v; (npm 7+ o actualiza: sudo npm i -g npm@9)"
          exit 1
        fi
      else
        echo "Aviso: no hay package-lock.json en $d — npm install puede traer Babel 7.2x y romper Encore. Genera y sube el lock."
        npm install --no-audit --no-fund
      fi
      npm run build
    }) || {
      echo "ERROR: npm run build falló en $d (realpath: $r)"
      exit 1
    }
    NPM_BUILT=$((NPM_BUILT + 1))
  done
  if [ "$NPM_BUILT" -eq 0 ]; then
    echo "Aviso: no se ejecutó 'npm run build' (falta package.json+public bajo $TOP/current, $TOP/portal o $TOP?)"
  fi
else
  for d in "$TOP/current" "$TOP/portal" "$TOP"; do
    if [ -f "$d/package.json" ]; then
      echo "Aviso: hace falta 'npm' en el PATH para generar public/build/ en $d"
    fi
  done
fi
# Tras Encore, manifest.json es obligatorio en current/ (config/packages/assets.yaml → json_manifest_path)
if [ -f "$TOP/current/package.json" ] && [ ! -f "$TOP/current/public/build/manifest.json" ]; then
  echo "ERROR: falta $TOP/current/public/build/manifest.json tras el build. ¿npm run build no generó public/build/?" >&2
  exit 1
fi
# Fallar el deploy si manifest.json existe pero no es JSON válido (500: JsonManifestVersionStrategy)
if command -v python3 >/dev/null 2>&1; then
  for _mf in "$TOP/current/public/build/manifest.json" "$TOP/portal/public/build/manifest.json"; do
    [ -f "$_mf" ] || continue
    if ! python3 -c "import json,sys; json.load(open(sys.argv[1],encoding='utf-8'))" "$_mf" 2>/dev/null; then
      echo "ERROR: manifest.json corrupto o no es JSON válido: $_mf (ejecuta en la VM: cd ... && npm run build)" >&2
      exit 1
    fi
  done
fi
# venv: si .venv/ existe roto (sin bin/activate), recrear; en Debian hace falta el paquete python3-venv (ensurepip)
_admin_agent_venv() {
  local d="$1"
  (cd "$d" && {
    if [ ! -f .venv/bin/activate ]; then
      rm -rf .venv
      if ! python3 -m venv .venv; then
        echo "Aviso: no se pudo crear .venv en $d (falta ensurepip)." >&2
        echo "  En la VM (una vez, como root): apt install -y python3-venv" >&2
        echo "  — o la variante concreta, p. ej. python3.13-venv, según el mensaje de 'python3 -m venv' arriba." >&2
        return 0
      fi
    fi
    if [ -f .venv/bin/activate ]; then
      # shellcheck source=/dev/null
      . .venv/bin/activate
      python3 -m pip install -q -U pip setuptools wheel
      if ! python3 -m pip install -q -r requirements.txt; then
        echo "Aviso: pip en $d falló. Si compila numpy: apt install -y build-essential pkg-config python3-dev" >&2
        echo "  (o python3.13-dev) y pega requirements.txt con numpy>=2.1 (rueda en Py3.13, sin compilar)." >&2
      fi
    fi
  }) || true
}
if [ -d portal/admin_agent ] && [ -f portal/admin_agent/requirements.txt ]; then
  _admin_agent_venv "portal/admin_agent"
elif [ -d admin_agent ] && [ -f admin_agent/requirements.txt ]; then
  _admin_agent_venv "admin_agent"
fi
# var/ a veces queda con dueño www-data (deploy anterior); hace falta vuelve a u:g primario
# para cache:clear. Sin sudo: chown a tu uid solo si eres el dueño; con sudo: cualquier mezcla.
U_PRE="$(id -u -n)"
G_PRE="$(id -g -n)"
if mkdir -p "$TOP/current/var" "$TOP/portal/var" 2>/dev/null; then
  if ! chown -R "$U_PRE:$G_PRE" "$TOP/current/var" "$TOP/portal/var" 2>/dev/null; then
    command -v sudo >/dev/null 2>&1 && sudo -n chown -R "$U_PRE:$G_PRE" "$TOP/current/var" "$TOP/portal/var" 2>/dev/null || true
  fi
fi
# Tras Infisical → .env de portal/current, limpiar caché en *cada* app Symfony
# No ocultar fallos: caché basura o permisos suelen causar HTTP 500 en app.mdtprevencion.com
for _symf in "$TOP/current" "$TOP/portal" "$TOP"; do
  [ -f "$_symf/bin/console" ] || continue
  if ! (cd "$_symf" && php bin/console cache:clear --env=prod --no-warmup); then
    echo "ERROR: cache:clear falló en $_symf. Prueba: sudo chown -R $(id -u -n):$(id -g -n) $_symf/var" >&2
  fi
done
# Inmediatamente tras cache:clear, var/ suele quedar u:ug sin grupo www-data → Apache no escribe
# (RuntimeException: Unable to write in var/cache/prod). Re-aplicar var/ antes del chown total.
WEB_USER="${DEPLOY_WEB_USER:-www-data}"
U="$(id -u -n)"
# chown U:www-data: sin sudo si el dueño es U y U pertenece a grupo $WEB_USER (id -nG | grep)
_chown_ug() {
  local p="$1"
  [ -e "$p" ] || return 0
  if chown -R "$U:$WEB_USER" "$p" 2>/dev/null; then
    return 0
  fi
  if command -v sudo >/dev/null 2>&1 && sudo -n chown -R "$U:$WEB_USER" "$p" 2>/dev/null; then
    return 0
  fi
  return 1
}
_chmod_ug_dir() {
  local p="$1"
  [ -d "$p" ] || return 0
  find "$p" -type d -exec chmod 2775 {} \; 2>/dev/null || true
  find "$p" -type f -exec chmod 664 {} \; 2>/dev/null || true
  if command -v sudo >/dev/null 2>&1; then
    sudo -n find "$p" -type d -exec chmod 2775 {} \; 2>/dev/null || true
    sudo -n find "$p" -type f -exec chmod 664 {} \; 2>/dev/null || true
  fi
}
if getent group "$WEB_USER" >/dev/null 2>&1; then
  for _app in "$TOP/current" "$TOP/portal"; do
    [ -d "$_app/var" ] || continue
    if _chown_ug "$_app/var"; then
      _chmod_ug_dir "$_app/var"
    else
      echo "Aviso: chown a $U:$WEB_USER falló en $_app/var. Ejecuta UNA VEZ: sudo usermod -aG $WEB_USER $U; nueva sesión SSH; o: sudo chown -R $U:$WEB_USER $_app/var" >&2
    fi
  done
fi
if systemctl is-active --quiet prevencion-admin-agent 2>/dev/null; then
  sudo -n systemctl restart prevencion-admin-agent 2>/dev/null || true
fi
# Apache (www-data) y el usuario de deploy: mismo grupo en todo current/ y portal/
#  - NUNCA dejar var/ solo como www-data:www-data: el deploy no podrá cache:clear (portal lo mostró)
#  - Dirs 2775 (setgid) + files 664: _chown_ug sin sudo si $U∈$WEB_USER; si no, NOPASSWD o chown a mano
if ! getent group "$WEB_USER" >/dev/null 2>&1; then
  echo "ERROR: no existe el grupo de sistema $WEB_USER." >&2
  exit 1
fi
for _app in "$TOP/current" "$TOP/portal"; do
  [ -d "$_app" ] || continue
  if ! _chown_ug "$_app"; then
    echo "ERROR: chown a $U:$WEB_USER falló en $_app. 1) sudo usermod -aG $WEB_USER $U (nueva sesión SSH) 2) o sudoers: .github/sudoers/99-prevencion-deploy 3) o: sudo chown -R $U:$WEB_USER $_app" >&2
    exit 1
  fi
  find "$_app" -type d -exec chmod 2775 {} \; 2>/dev/null || true
  find "$_app" -type f -exec chmod 664 {} \; 2>/dev/null || true
  if command -v sudo >/dev/null 2>&1; then
    sudo -n find "$_app" -type d -exec chmod 2775 {} \; 2>/dev/null || true
    sudo -n find "$_app" -type f -exec chmod 664 {} \; 2>/dev/null || true
  fi
  for _f in "$_app/.env" "$_app/.env.local" "$_app/.env.local.php"; do
    [ -f "$_f" ] || continue
    chgrp "$WEB_USER" "$_f" 2>/dev/null && chmod 640 "$_f" 2>/dev/null || true
    sudo -n chgrp "$WEB_USER" "$_f" 2>/dev/null && sudo -n chmod 640 "$_f" 2>/dev/null || true
  done
done
if [ -d "$TOP/var" ]; then
  _chown_ug "$TOP/var" 2>/dev/null || true
fi
# Comprobar grupo en var/ (setgid 2775 + grupo www-data)
_vg="$(stat -c '%G' "$TOP/current/var" 2>/dev/null || true)"
if [ "$_vg" != "$WEB_USER" ]; then
  echo "ERROR: $TOP/current/var debería tener grupo $WEB_USER; ahora: ${_vg:-desconocido}. Tras: sudo usermod -aG $WEB_USER $U, cierra y abre la sesión SSH (o: sudo chown -R $U:$WEB_USER $TOP/current/var). ve .github/CICD-SETUP.md" >&2
  exit 1
fi
echo "OK deploy prevencion $TOP"
