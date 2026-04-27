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

# Caché Symfony en .symfony-cache/run-<id>/ (id único por ejecución): evita cache:clear sobre ficheros www-data
# cuando sudo no puede chown/rm (ver deploy.yml: DEPLOY_SYMFONY_CACHE_STAMP).
: "${DEPLOY_SYMFONY_CACHE_STAMP:=${GITHUB_RUN_ID:-$(date +%s)}}"
export DEPLOY_SYMFONY_CACHE_STAMP

WEB_USER="${DEPLOY_WEB_USER:-www-data}"

# secure_path de sudo a veces no incluye chown; 99-prevencion-deploy lista /usr/bin/chown y /bin/chown explícitos.
# Con DEPLOY_SUDO_DEBUG=1 (variable en el paso SSH del workflow) se imprime sudo -l si falla.
_deploy_sudo_chown_r() {
  local target="$1"
  local ug="$2"
  [ -e "$target" ] || return 1
  local _ch
  for _ch in /usr/bin/chown /bin/chown; do
    [ -x "$_ch" ] || continue
    if sudo -n "$_ch" -R "$ug" "$target" 2>/dev/null; then
      return 0
    fi
  done
  if sudo -n chown -R "$ug" "$target" 2>/dev/null; then
    return 0
  fi
  if [ "${DEPLOY_SUDO_DEBUG:-}" = 1 ]; then
    echo "DEPLOY_SUDO_DEBUG: sudo -l (comprueba NOPASSWD y rutas de chown)" >&2
    sudo -n -l 2>&1 | head -40 >&2 || true
  fi
  return 1
}

# Cuando NOPASSWD para chown no está (o falla), a veces sí lo están find+chmod+chgrp. Dirs 755 www-data impiden
# unlink aunque uses sg www-data (uid sigue siendo deploy); 2775 + grupo WEB_USER + deploy∈WEB_USER sí.
_sudo_repair_dir_group_writable() {
  local root="$1"
  [ -d "$root" ] || return 0
  command -v sudo >/dev/null 2>&1 || return 1
  if ! sudo -n find "$root" -xdev -type d -exec chmod 2775 {} + 2>/dev/null; then
    return 1
  fi
  sudo -n find "$root" -xdev -type f -exec chmod 664 {} + 2>/dev/null || true
  local _cg
  for _cg in /usr/bin/chgrp /bin/chgrp; do
    [ -x "$_cg" ] || continue
    if sudo -n "$_cg" -R "$WEB_USER" "$root" 2>/dev/null; then
      return 0
    fi
  done
  sudo -n chgrp -R "$WEB_USER" "$root" 2>/dev/null
}

# Caché del kernel: .symfony-cache/run-<STAMP>/ + APP_CACHE_DIR en .env (STAMP único → dir vacío sin sudo rm).
# Con .env.local.php, bootstrap.php fusiona APP_CACHE_DIR desde .env; export aquí para composer/console.
_export_app_cache_dir_if_present() {
  local app_root="$1"
  local xcd="${app_root}/.symfony-cache/run-${DEPLOY_SYMFONY_CACHE_STAMP}"
  [ -d "$xcd" ] || return 0
  APP_CACHE_DIR="$(cd "$xcd" && pwd -P)"
  export APP_CACHE_DIR
}

_symfony_external_cache_setup() {
  local app="$1"
  [ -d "$app" ] || return 0
  [ -f "$app/bin/console" ] || return 0
  local xcd="${app}/.symfony-cache/run-${DEPLOY_SYMFONY_CACHE_STAMP}"
  mkdir -p "$xcd"
  chmod 2775 "$xcd" 2>/dev/null || chmod 775 "$xcd" || true
  if ! chgrp "$WEB_USER" "$xcd" 2>/dev/null; then
    echo "Aviso: chgrp $WEB_USER $xcd falló; chmod 2777 solo en .symfony-cache para Apache, o: sudo usermod -aG $WEB_USER $(id -u -n)" >&2
    chmod 2777 "$xcd" 2>/dev/null || true
  fi
  local envf="${app}/.env"
  if [ ! -f "$envf" ]; then
    if [ -f "${envf}.dist" ]; then
      cp -a "${envf}.dist" "$envf"
    else
      echo "Aviso: no hay $envf ni .env.dist; omito APP_CACHE_DIR en $(basename "$app")." >&2
      return 0
    fi
  fi
  local abs
  abs="$(cd "$xcd" && pwd -P)"
  if grep -q '^[[:space:]]*APP_CACHE_DIR=' "$envf" 2>/dev/null; then
    grep -v '^[[:space:]]*APP_CACHE_DIR=' "$envf" > "${envf}.new.pcache" && mv "${envf}.new.pcache" "$envf"
  fi
  printf 'APP_CACHE_DIR=%s\n' "$abs" >> "$envf"
  echo "APP_CACHE_DIR=$abs ($(basename "$app"); stamp=$DEPLOY_SYMFONY_CACHE_STAMP)" >&2
}

_console_cache_clear_prod() {
  local d="$1"
  _export_app_cache_dir_if_present "$d"
  (cd "$d" && php bin/console cache:clear --env=prod --no-warmup) && return 0
  if [ -d "$d/var/cache" ]; then
    _sudo_repair_dir_group_writable "$d/var/cache" 2>/dev/null || true
    (cd "$d" && php bin/console cache:clear --env=prod --no-warmup) && return 0
  fi
  if id -nG 2>/dev/null | tr ' ' '\n' | grep -qx "$WEB_USER"; then
    if command -v sg >/dev/null 2>&1; then
      echo "Aviso: cache:clear en $d falló como $(id -u -n); reintento con sg $WEB_USER (dirs 755: usar find+chmod; CICD-SETUP)." >&2
      (cd "$d" && sg "$WEB_USER" -c "php bin/console cache:clear --env=prod --no-warmup") && return 0
    fi
  fi
  return 1
}

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
U_DEPLOY="$(id -u -n):$(id -g -n)"
mkdir -p "$TOP/current/var" "$TOP/portal/var" 2>/dev/null || true
if command -v sudo >/dev/null 2>&1; then
  if ! _deploy_sudo_chown_r "$TOP" "$U_DEPLOY"; then
    echo "Aviso: sudo chown -R del repo falló (¿NOPASSWD? ¿Defaults requiretty? CICD-SETUP; prueba env DEPLOY_SUDO_DEBUG=1 en SSH)." >&2
  fi
else
  chown -R "$U_DEPLOY" "$TOP" 2>/dev/null || {
    echo "Falta sudo o chown: no se pudo normalizar dueños de $TOP" >&2
  }
fi
git checkout "$BRANCH" 2>/dev/null || git checkout -b "$BRANCH" "origin/${BRANCH}"
git reset --hard "origin/${BRANCH}"

# admin_agent/.env desde Infisical (INFISICAL_TOKEN vía CI; ver CICD-SETUP)
if [ -f "$TOP/.github/scripts/infisical-admin-agent-env.sh" ]; then
  bash "$TOP/.github/scripts/infisical-admin-agent-env.sh" "$TOP" || exit 1
fi
# .env.local.php (composer dump-env) puede tener un ADMIN_AGENT_SECRET viejo y pisar el nuevo de .env
# (bootstrap.php carga .env.local.php y no relee .env). Borrarlo aquí evita 401 entre PHP y uvicorn.
# bootstrap.php cae en Dotenv y carga .env tranquilamente; no hace falta regenerar el dump.
for _envcache in "$TOP/current/.env.local.php" "$TOP/portal/.env.local.php"; do
  if [ -f "$_envcache" ]; then
    rm -f "$_envcache" && echo "Eliminado $_envcache (evita ADMIN_AGENT_SECRET cacheado obsoleto)." >&2 || true
  fi
done
# Sin sudo NOPASSWD, var/cache no se vacía bien; APP_CACHE_DIR → .symfony-cache/run-<stamp>/ (nuevo por deploy; ver Kernel.php).
for _extc in "$TOP/current" "$TOP/portal"; do
  [ -f "$_extc/bin/console" ] || continue
  _symfony_external_cache_setup "$_extc"
done
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
  _export_app_cache_dir_if_present "$d"
  (cd "$d" && composer install --no-dev --no-interaction --optimize-autoloader) && return 0
  echo "Aviso: composer falló en $d (a menudo DNS a flex.symfony.com). Reintentando con --no-plugins --no-scripts…" >&2
  _export_app_cache_dir_if_present "$d"
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
    _export_app_cache_dir_if_present "$_a"
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
# var/cache bajo www-data: chown -R var (sudo con rutas absolutas a chown) y luego borrar caché.
U_PRE="$(id -u -n)"
G_PRE="$(id -g -n)"
_prep_var_before_cache_clear() {
  local _app
  for _app in "$TOP/current" "$TOP/portal"; do
    [ -d "$_app" ] || continue
    mkdir -p "$_app/var" 2>/dev/null || true
    if _deploy_sudo_chown_r "$_app/var" "$U_PRE:$G_PRE"; then
      rm -rf "$_app/var/cache"
      mkdir -p "$_app/var/cache"
    else
      echo "Aviso: sudo chown $_app/var falló; reparando permisos con sudo find+chmod+chgrp (mismo 99-prevencion-deploy que chown)…" >&2
      if [ -d "$_app/var/cache" ]; then
        _sudo_repair_dir_group_writable "$_app/var/cache" || echo "Aviso: sudo chmod/chgrp en $_app/var/cache falló (sin NOPASSWD)." >&2
      fi
      if ! chown -R "$U_PRE:$G_PRE" "$_app/var" 2>/dev/null; then
        echo "Aviso: chown sin sudo falló (mezcla www-data)." >&2
      fi
      rm -rf "$_app/var/cache" 2>/dev/null || {
        echo "Aviso: rm -rf caché falló; reparando todo $_app/var…" >&2
        _sudo_repair_dir_group_writable "$_app/var" || true
        rm -rf "$_app/var/cache" 2>/dev/null || true
      }
      mkdir -p "$_app/var/cache"
    fi
  done
}
_prep_var_before_cache_clear
# Tras Infisical → .env de portal/current, limpiar caché en *cada* app Symfony
# (también vía CI: GitHub Actions ejecuta este script en la VM; no hace falta cache:clear a mano.)
# --no-warmup: rápido en deploy; el primer request calienta. Si prefieres cache listo: añade cache:warmup.
for _symf in "$TOP/current" "$TOP/portal" "$TOP"; do
  [ -f "$_symf/bin/console" ] || continue
  if ! _console_cache_clear_prod "$_symf"; then
    echo "ERROR: cache:clear falló en $_symf. Revisa $_symf/var/cache. Caché kernel: $_symf/.symfony-cache/run-* (stamp=$DEPLOY_SYMFONY_CACHE_STAMP). Opciones: NOPASSWD chown (99-prevencion-deploy), usermod -aG $WEB_USER $(id -u -n), o sudo chown -R $(id -u -n):$(id -g -n) $_symf/var" >&2
    exit 1
  fi
done
# Inmediatamente tras cache:clear, var/ suele quedar u:ug sin grupo www-data → Apache no escribe
# (RuntimeException: Unable to write in var/cache/prod). Re-aplicar var/ antes del chown total.
U="$(id -u -n)"
# chown U:www-data: "chown -R" falla si bajo el árbol hay aunque sea un inode de otro dueño
# (root, www-data, copias viejos). Sólo ajustar lo de $U; luego intentar -R y sudo -n.
_chown_ug() {
  local p="$1"
  [ -e "$p" ] || return 0
  find "$p" -xdev -user "$U" -exec chown -h "$U:$WEB_USER" {} + 2>/dev/null || true
  chown -R "$U:$WEB_USER" "$p" 2>/dev/null || true
  if command -v sudo >/dev/null 2>&1; then
    sudo -n chown -R "$U:$WEB_USER" "$p" 2>/dev/null || true
  fi
  return 0
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
    _chown_ug "$_app/var"
    _chmod_ug_dir "$_app/var"
  done
fi
_admin_agent_unit_install_and_restart() {
  # Instala/actualiza la unit si tenemos sudo NOPASSWD; siempre intenta restart.
  # Sin systemd o sin sudo: fallback que mata uvicorn huerfano y lo arranca con .venv del repo.
  local unit_src="$TOP/.github/systemd/prevencion-admin-agent.service"
  local unit_dst="/etc/systemd/system/prevencion-admin-agent.service"
  local agent_dir="$TOP/portal/admin_agent"
  [ -d "$agent_dir" ] || agent_dir="$TOP/admin_agent"
  [ -d "$agent_dir" ] || return 0
  if command -v systemctl >/dev/null 2>&1 && command -v sudo >/dev/null 2>&1 && [ -f "$unit_src" ]; then
    local rendered
    rendered="$(mktemp)"
    sed "s#/home/administrador/prevencio/prevencio-meditrauma/portal/admin_agent#${agent_dir}#g; s#^User=.*#User=${U_PRE}#; s#^Group=.*#Group=${WEB_USER}#" "$unit_src" > "$rendered"
    if [ ! -f "$unit_dst" ] || ! cmp -s "$rendered" "$unit_dst"; then
      if sudo -n install -m 0644 -o root -g root "$rendered" "$unit_dst" 2>/dev/null; then
        sudo -n systemctl daemon-reload 2>/dev/null || true
        sudo -n systemctl enable prevencion-admin-agent 2>/dev/null || true
        echo "OK: unit prevencion-admin-agent instalada/actualizada en $unit_dst" >&2
      else
        echo "Aviso: sudo install de la unit fallo (NOPASSWD?); seguire con fallback manual." >&2
      fi
    fi
    rm -f "$rendered"
    if sudo -n systemctl restart prevencion-admin-agent 2>/dev/null; then
      echo "OK: prevencion-admin-agent reiniciado (recarga .env)." >&2
      return 0
    fi
  fi
  # Fallback sin systemd: mata uvicorn huerfano del agente y lo relanza desde el repo.
  if command -v pgrep >/dev/null 2>&1; then
    local pids
    pids="$(pgrep -f "${agent_dir}/.venv/bin/python.*uvicorn.*app:app" 2>/dev/null || true)"
    [ -z "$pids" ] && pids="$(pgrep -f 'uvicorn.*app:app.*9102' 2>/dev/null || true)"
    if [ -n "$pids" ]; then
      echo "Matando uvicorn huerfano (PIDs: $pids) para recargar .env." >&2
      kill $pids 2>/dev/null || true
      sleep 1
      kill -9 $pids 2>/dev/null || true
    fi
  fi
  if [ -x "${agent_dir}/.venv/bin/python" ]; then
    # SSH del runner cuelga si el hijo hereda fds del workflow. Probamos en orden:
    #   1) systemd-run --user (transient unit; daemonizado de verdad)
    #   2) setsid -f (fork + nueva sesion; cierra todo el TTY)
    #   3) nohup clasico con todos los fds cerrados (3..255) y stdin de /dev/null
    if command -v systemd-run >/dev/null 2>&1 \
        && systemd-run --user --quiet --unit=prevencion-admin-agent \
            --working-directory="$agent_dir" \
            "$agent_dir/.venv/bin/python" -m uvicorn app:app --host 127.0.0.1 --port 9102 2>/dev/null; then
      echo "Aviso: uvicorn arrancado via systemd-run --user (sin sudo). Instala el sudoers para usar la unit del sistema." >&2
    elif command -v setsid >/dev/null 2>&1; then
      ( cd "$agent_dir" && setsid -f .venv/bin/python -m uvicorn app:app --host 127.0.0.1 --port 9102 \
          < /dev/null > /tmp/prevencion-admin-agent.log 2>&1 ) || true
      echo "Aviso: uvicorn relanzado con setsid -f (sin systemd). Instala el sudoers para usar la unit del sistema." >&2
    else
      ( cd "$agent_dir" && nohup .venv/bin/python -m uvicorn app:app --host 127.0.0.1 --port 9102 \
          < /dev/null > /tmp/prevencion-admin-agent.log 2>&1 & disown ) >/dev/null 2>&1 || true
      echo "Aviso: uvicorn relanzado con nohup (sin setsid/systemd). Si el job de Actions cuelga, instala el sudoers." >&2
    fi
  else
    echo "Aviso: no hay $agent_dir/.venv/bin/python; uvicorn no se ha (re)arrancado." >&2
  fi
}
_admin_agent_unit_install_and_restart
# Apache (www-data) y el usuario de deploy: mismo grupo en todo current/ y portal/
#  - NUNCA dejar var/ solo como www-data:www-data: el deploy no podrá cache:clear (portal lo mostró)
#  - Dirs 2775 (setgid) + files 664: _chown_ug sin sudo si $U∈$WEB_USER; si no, NOPASSWD o chown a mano
if ! getent group "$WEB_USER" >/dev/null 2>&1; then
  echo "ERROR: no existe el grupo de sistema $WEB_USER." >&2
  exit 1
fi
for _app in "$TOP/current" "$TOP/portal"; do
  [ -d "$_app" ] || continue
  _chown_ug "$_app"
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
  echo "ERROR: $TOP/current/var debería tener grupo $WEB_USER; ahora: ${_vg:-desconocido}." >&2
  echo "  Normalizar dueños (ficheros de root/www-data mezclados): sudo chown -R $U:$G_PRE $TOP/current $TOP/portal" >&2
  echo "  y luego: sudo chown -R $U:$WEB_USER $TOP/current/var $TOP/portal/var" >&2
  echo "  o .github/sudoers/99-prevencion-deploy. ve .github/CICD-SETUP.md" >&2
  exit 1
fi
echo "OK deploy prevencion $TOP"
