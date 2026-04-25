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
if [ -d portal/admin_agent ] && [ -f portal/admin_agent/requirements.txt ]; then
  (cd portal/admin_agent && (test -d .venv || python3 -m venv .venv) && . .venv/bin/activate && pip install -q -r requirements.txt) || true
elif [ -d admin_agent ] && [ -f admin_agent/requirements.txt ]; then
  (cd admin_agent && (test -d .venv || python3 -m venv .venv) && . .venv/bin/activate && pip install -q -r requirements.txt) || true
fi
# Tras Infisical → .env de portal/current, limpiar caché en *cada* app Symfony (antes solo una rama if/elif)
for _symf in "$TOP/current" "$TOP/portal" "$TOP"; do
  [ -f "$_symf/bin/console" ] || continue
  (cd "$_symf" && php bin/console cache:clear --env=prod --no-warmup) 2>/dev/null || true
done
if systemctl is-active --quiet prevencion-admin-agent 2>/dev/null; then
  sudo systemctl restart prevencion-admin-agent || true
fi
# var/ (Symfony: log, caché) — www-data; enlaces p. ej. current/ hacia un release
WEB_USER="${DEPLOY_WEB_USER:-www-data}"
if command -v sudo >/dev/null 2>&1; then
  for v in "$TOP/current/var" "$TOP/portal/var" "$TOP/var"; do
    [ -d "$v" ] || continue
    sudo -n chown -R "$WEB_USER:$WEB_USER" "$v" 2>/dev/null || true
  done
fi
echo "OK deploy prevencion $TOP"
