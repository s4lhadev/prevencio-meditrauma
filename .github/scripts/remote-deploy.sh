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

# git por SSH a github.com: actualizar known_hosts (evita "Host key verification failed")
mkdir -p "$HOME/.ssh"
chmod 700 "$HOME/.ssh" 2>/dev/null || true
touch "$HOME/.ssh/known_hosts"
ssh-keygen -R 'github.com' -f "$HOME/.ssh/known_hosts" 2>/dev/null || true
ssh-keygen -R '[github.com]:22' -f "$HOME/.ssh/known_hosts" 2>/dev/null || true
ssh-keyscan -T 25 -t ed25519,ecdsa,rsa github.com >> "$HOME/.ssh/known_hosts" 2>/dev/null || {
  echo "No se pudo ssh-keyscan github.com; revisa DNS/salida a internet en la VM"
  exit 1
}
GH_KEY=""
for f in "$HOME/.ssh/github_deploy" "$HOME/.ssh/mdt_debian" "$HOME/.ssh/id_ed25519" "$HOME/.ssh/id_rsa"; do
  [ -f "$f" ] || continue
  GH_KEY="$f"
  break
done
if [ -n "$GH_KEY" ]; then
  # shellcheck disable=SC2139
  export GIT_SSH_COMMAND="ssh -i $GH_KEY -o IdentitiesOnly=yes -o BatchMode=yes -o StrictHostKeyChecking=yes -o UserKnownHostsFile=$HOME/.ssh/known_hosts"
else
  export GIT_SSH_COMMAND="ssh -o BatchMode=yes -o StrictHostKeyChecking=yes -o UserKnownHostsFile=$HOME/.ssh/known_hosts"
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
if [ -f "$TOP/current/bin/console" ]; then
  (cd "$TOP/current" && php bin/console cache:clear --env=prod --no-warmup) 2>/dev/null || true
elif [ -f "$TOP/portal/bin/console" ]; then
  (cd "$TOP/portal" && php bin/console cache:clear --env=prod --no-warmup) 2>/dev/null || true
elif [ -f "$TOP/bin/console" ]; then
  (cd "$TOP" && php bin/console cache:clear --env=prod --no-warmup) 2>/dev/null || true
fi
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
