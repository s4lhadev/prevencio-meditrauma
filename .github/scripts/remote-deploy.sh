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
git remote set-url origin "$REPO_URL" 2>/dev/null || true
git fetch --prune origin
if ! git show-ref --verify --quiet "refs/remotes/origin/${BRANCH}"; then
  echo "No existe origin/${BRANCH} tras el fetch. ¿La rama está en GitHub?"
  exit 1
fi
git checkout "$BRANCH" 2>/dev/null || git checkout -b "$BRANCH" "origin/${BRANCH}"
git reset --hard "origin/${BRANCH}"
if [ -d portal/admin_agent ] && [ -f portal/admin_agent/requirements.txt ]; then
  (cd portal/admin_agent && (test -d .venv || python3 -m venv .venv) && . .venv/bin/activate && pip install -q -r requirements.txt) || true
elif [ -d admin_agent ] && [ -f admin_agent/requirements.txt ]; then
  (cd admin_agent && (test -d .venv || python3 -m venv .venv) && . .venv/bin/activate && pip install -q -r requirements.txt) || true
fi
if [ -f portal/bin/console ]; then
  (cd portal && php bin/console cache:clear --env=prod --no-warmup) 2>/dev/null || true
elif [ -f bin/console ]; then
  php bin/console cache:clear --env=prod --no-warmup 2>/dev/null || true
fi
if systemctl is-active --quiet prevencion-admin-agent 2>/dev/null; then
  sudo systemctl restart prevencion-admin-agent || true
fi
echo "OK deploy prevencion $TOP"
