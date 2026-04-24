#!/usr/bin/env bash
# Genera admin_agent/.env con solo INFISICAL_TOKEN (service token). Prueba slugs production / prod.
# APP_PRODUCT_OVERRIDE lo fija el workflow (prevencion). Sin token, sale 0 y no toca nada.
set -euo pipefail

REPO_ROOT="${1:-}"
if [ -z "$REPO_ROOT" ] || [ ! -d "$REPO_ROOT/.git" ]; then
  echo "Uso: $0 RUTA_REPO" >&2
  exit 1
fi
REPO_ROOT="$(cd "$REPO_ROOT" && pwd -P)"

if [ -z "${INFISICAL_TOKEN:-}" ]; then
  echo "Aviso: INFISICAL_TOKEN no definido; se conserva admin_agent/.env local si existe."
  exit 0
fi

AGENT_SUB=""
if [ -f "$REPO_ROOT/portal/admin_agent/requirements.txt" ]; then
  AGENT_SUB="portal/admin_agent"
elif [ -f "$REPO_ROOT/admin_agent/requirements.txt" ]; then
  AGENT_SUB="admin_agent"
else
  echo "Aviso: no hay admin_agent; omitiendo Infisical."
  exit 0
fi

OUT="$REPO_ROOT/$AGENT_SUB/.env"
ENV_CANDIDATES="production prod"

if ! command -v infisical &>/dev/null; then
  echo "Instalando Infisical CLI (apt)…"
  if ! command -v sudo &>/dev/null; then
    echo "ERROR: hace falta 'sudo' para instalar infisical o instálalo en la VM a mano." >&2
    exit 1
  fi
  if ! sudo -n true 2>/dev/null; then
    echo "ERROR: sudo requiere contraseña. Instala: curl … setup.deb.sh && apt install infisical" >&2
    exit 1
  fi
  curl -1sLf 'https://dl.cloudsmith.io/public/infisical/infisical-cli/setup.deb.sh' | sudo -E bash
  sudo -n apt-get update -qq && sudo -n apt-get install -y infisical
fi

export INFISICAL_TOKEN
rm -f "$OUT"
set +e
ok=0
for ENV_NAME in $ENV_CANDIDATES; do
  infisical export --env="$ENV_NAME" --format=dotenv --output-file="$OUT" --token="$INFISICAL_TOKEN" 2>/dev/null
  if [ -s "$OUT" ]; then ok=1; break; fi
  rm -f "$OUT"
  (cd "$REPO_ROOT/$AGENT_SUB" && infisical run --env="$ENV_NAME" --command="env" > "$OUT" 2>/dev/null)
  if [ -s "$OUT" ]; then ok=1; break; fi
  rm -f "$OUT"
done
set -e

if [ "$ok" -ne 1 ] || [ ! -s "$OUT" ]; then
  echo "ERROR: no se pudo generar $OUT (token o slug de entorno: prueba slugs production / prod en Infisical)." >&2
  exit 1
fi

if [ -n "${APP_PRODUCT_OVERRIDE:-}" ]; then
  tmp="${OUT}.__tmp__"
  grep -v '^[[:space:]]*APP_PRODUCT=' "$OUT" > "$tmp" 2>/dev/null || : > "$tmp"
  mv "$tmp" "$OUT"
  echo "APP_PRODUCT=$APP_PRODUCT_OVERRIDE" >> "$OUT"
fi

echo "OK Infisical → $OUT"
