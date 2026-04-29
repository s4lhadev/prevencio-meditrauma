#!/usr/bin/env bash
# Con INFISICAL_TOKEN: exporta secrets → portal/admin_agent/.env y fusiona ADMIN_AGENT_* en Symfony (.env).
# Machine identity: exporta INFISICAL_PROJECT_ID (UUID) para --projectId en el CLI (paridad Medisalut).
# VM_DEPLOY_SUDO_PASSWORD: se vuelca a ~/.deploy_sudo_password y se elimina del .env del agente (uvicorn no la carga).
# Sin token: conserva admin_agent/.env en disco y hace esa misma fusión (evita 401 si PHP ≠ Python).
# APP_PRODUCT_OVERRIDE: workflow (medisalut / prevencion).
# No exige sudo: npx, binario en ~/.local/bin, o apt si sudo -n.
set -euo pipefail

REPO_ROOT="${1:-}"
if [ -z "$REPO_ROOT" ] || [ ! -d "$REPO_ROOT/.git" ]; then
  echo "Uso: $0 RUTA_REPO" >&2
  exit 1
fi
REPO_ROOT="$(cd "$REPO_ROOT" && pwd -P)"

# Tras = en .env: "" , '' o solo espacios no son "presentes" (Infisical suele poner comillas y '' vacío)
_is_dotenv_value_empty() {
  local s
  s=$(printf '%s' "$1" | tr -d '\r' | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
  [ -z "$s" ] && return 0
  [ "$s" = "''" ] && return 0
  [ "$s" = '""' ] && return 0
  return 1
}

_normalize_merge_dotenv_value() {
  local v="$1"
  v=$(printf '%s' "$v" | tr -d '\r')
  v=$(printf '%s' "$v" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
  case "$v" in
  \"*\")
    v="${v#\"}"
    v="${v%\"}"
    ;;
  \'*\')
    v="${v#\'}"
    v="${v%\'}"
    ;;
  esac
  printf '%s' "$v"
}

AGENT_SUB=""
if [ -f "$REPO_ROOT/portal/admin_agent/requirements.txt" ]; then
  AGENT_SUB="portal/admin_agent"
elif [ -f "$REPO_ROOT/admin_agent/requirements.txt" ]; then
  AGENT_SUB="admin_agent"
else
  echo "Aviso: no hay admin_agent; omitiendo."
  exit 0
fi
OUT="$REPO_ROOT/$AGENT_SUB/.env"

_ensure_symfony_dotenv_bootstrap() {
  local f d
  for f in "$REPO_ROOT/portal/.env" "$REPO_ROOT/current/.env"; do
    [ -f "$f" ] && continue
    d="${f}.dist"
    if [ -f "$d" ]; then
      echo "Aviso: creando $f desde $(basename "$d") (no existía; hace falta .env base para fusionar)." >&2
      cp -a "$d" "$f"
    else
      : > "$f"
      echo "Aviso: creado $f vacío (falta .env.dist en $(dirname "$f") )." >&2
    fi
  done
}

_merge_symfony_dotenv_from_admin_agent() {
  local agent_env k line v f
  agent_env="$OUT"
  [ -f "$agent_env" ] || return 0
  for k in ADMIN_AGENT_INTERNAL_URL ADMIN_AGENT_SECRET ADMIN_AGENT_PAGE_KEY; do
    line=$(grep -m1 "^[[:space:]]*${k}=" "$agent_env" 2>/dev/null || true)
    [ -n "$line" ] || continue
    v="${line#*=}"
    v="$(_normalize_merge_dotenv_value "$v")"
    if [ "$k" = "ADMIN_AGENT_PAGE_KEY" ] && _is_dotenv_value_empty "$v"; then
      continue
    fi
    for f in "$REPO_ROOT/portal/.env" "$REPO_ROOT/current/.env"; do
      [ -f "$f" ] || continue
      if grep -q "^[[:space:]]*${k}=" "$f" 2>/dev/null; then
        grep -v "^[[:space:]]*${k}=" "$f" > "${f}.new" 2>/dev/null || : > "${f}.new"
        mv "${f}.new" "$f"
      fi
      printf '%s=%s\n' "$k" "$v" >> "$f"
    done
  done
}

_ensure_page_key_in_php_env_from_dist() {
  local distline
  distline=$(grep -m1 '^[[:space:]]*ADMIN_AGENT_PAGE_KEY=' "$REPO_ROOT/portal/.env.dist" 2>/dev/null || grep -m1 '^[[:space:]]*ADMIN_AGENT_PAGE_KEY=' "$REPO_ROOT/current/.env.dist" 2>/dev/null || true)
  [ -n "$distline" ] || return 0
  for f in "$REPO_ROOT/portal/.env" "$REPO_ROOT/current/.env"; do
    [ -f "$f" ] || continue
    val=""
    if grep -qE '^[[:space:]]*ADMIN_AGENT_PAGE_KEY=' "$f" 2>/dev/null; then
      val=$(grep -m1 '^[[:space:]]*ADMIN_AGENT_PAGE_KEY=' "$f" 2>/dev/null | cut -d= -f2-)
    fi
    if ! _is_dotenv_value_empty "$val"; then
      continue
    fi
    if grep -qE '^[[:space:]]*ADMIN_AGENT_PAGE_KEY=' "$f" 2>/dev/null; then
      grep -v '^[[:space:]]*ADMIN_AGENT_PAGE_KEY=' "$f" > "${f}.new" 2>/dev/null || : > "${f}.new"
      mv "${f}.new" "$f"
    fi
    printf '%s\n' "$distline" >> "$f"
    echo "Aviso: ADMIN_AGENT_PAGE_KEY rellenado desde .env.dist en $f (pon la clave real en Infisical si no está)" >&2
  done
}

_sync_symfony_admin_keys_from_agent_env() {
  _ensure_symfony_dotenv_bootstrap
  _merge_symfony_dotenv_from_admin_agent
  _ensure_page_key_in_php_env_from_dist
}

if [ -z "${INFISICAL_TOKEN:-}" ]; then
  echo "Aviso: INFISICAL_TOKEN no definido; se usa admin_agent/.env en disco y se sincroniza ADMIN_AGENT_* → portal/current .env." >&2
  if [ ! -f "$OUT" ]; then
    echo "Aviso: no existe $OUT; nada que fusionar a Symfony." >&2
    exit 0
  fi
  _sync_symfony_admin_keys_from_agent_env
  echo "OK: $OUT → Symfony (.env); vuelve a desplegar o ejecuta cache:clear si no corre en CI." >&2
  exit 0
fi

ENV_CANDIDATES="production prod"
INFISICAL_CLI_VERSION="${INFISICAL_CLI_VERSION:-0.43.77}"
NPX_PKG="${INFISICAL_NPX_PACKAGE:-@infisical/cli}"

export PATH="${HOME}/.local/bin:${PATH}"

run_infisical() {
  if [ -n "${INFISICAL_VIA_NPX:-}" ]; then
    # shellcheck disable=SC2086
    npx -y ${NPX_PKG} "$@"
  else
    infisical "$@"
  fi
}

install_infisical_to_local_bin() {
  local arch ver tmp
  case "$(uname -m)" in
  x86_64) arch=amd64 ;;
  aarch64|arm64) arch=arm64 ;;
  *) arch=amd64 ;;
  esac
  ver="$INFISICAL_CLI_VERSION"
  echo "Descargando Infisical CLI v${ver} a ~/.local/bin (sin sudo)…"
  mkdir -p "${HOME}/.local/bin"
  tmp="$(mktemp -d)"
  if ! curl -fsSL "https://github.com/Infisical/cli/releases/download/v${ver}/cli_${ver}_linux_${arch}.tar.gz" -o "$tmp/infisical.tgz" 2>/dev/null; then
    rm -rf "$tmp"
    return 1
  fi
  if ! tar xzf "$tmp/infisical.tgz" -C "$tmp" 2>/dev/null; then
    rm -rf "$tmp"
    return 1
  fi
  local f=""
  f=$(find "$tmp" -name infisical -type f 2>/dev/null | head -1)
  if [ -n "$f" ] && [ -f "$f" ]; then
    cp -f "$f" "${HOME}/.local/bin/infisical"
    chmod 0755 "${HOME}/.local/bin/infisical"
    rm -rf "$tmp"
    return 0
  fi
  rm -rf "$tmp"
  return 1
}

ensure_infisical() {
  command -v infisical &>/dev/null && return 0
  if [ -x "${HOME}/.local/bin/infisical" ]; then
    return 0
  fi
  if command -v npx &>/dev/null; then
    echo "Usando npx (sin sudo): ${NPX_PKG}"
    export INFISICAL_VIA_NPX=1
    return 0
  fi
  if install_infisical_to_local_bin; then
    command -v infisical &>/dev/null && return 0
  fi
  if command -v sudo &>/dev/null && sudo -n true 2>/dev/null; then
    echo "Instalando Infisical CLI con apt (sudo sin contraseña)…"
    curl -1sLf 'https://dl.cloudsmith.io/public/infisical/infisical-cli/setup.deb.sh' | sudo -E bash
    sudo -n apt-get update -qq && sudo -n apt-get install -y infisical
    unset INFISICAL_VIA_NPX
  fi
  if command -v infisical &>/dev/null; then
    return 0
  fi
  echo "ERROR: no se encontró / instaló el CLI (prueba: npx o curl a GitHub, o: sudo apt install; ver PATH ~/.local/bin)." >&2
  return 1
}

ensure_infisical
export INFISICAL_TOKEN
# Machine identity: CLI exige --projectId (o variable en versiones recientes). Paridad con medisalut/.github/scripts/infisical-admin-agent-env.sh
infisical_project_args=()
if [ -n "${INFISICAL_PROJECT_ID:-}" ]; then
  infisical_project_args+=(--projectId "$INFISICAL_PROJECT_ID")
fi
rm -f "$OUT"
set +e
ok=0
for ENV_NAME in $ENV_CANDIDATES; do
  run_infisical export --env="$ENV_NAME" --format=dotenv --output-file="$OUT" --token="$INFISICAL_TOKEN" "${infisical_project_args[@]}" 2>/dev/null
  if [ -s "$OUT" ]; then ok=1; break; fi
  rm -f "$OUT"
  (cd "$REPO_ROOT/$AGENT_SUB" && run_infisical run --env="$ENV_NAME" --command="env" "${infisical_project_args[@]}" > "$OUT" 2>/dev/null)
  if [ -s "$OUT" ]; then ok=1; break; fi
  rm -f "$OUT"
done
set -e

if [ "$ok" -ne 1 ] || [ ! -s "$OUT" ]; then
  echo "ERROR: no se pudo generar $OUT (revisa INFISICAL_TOKEN, INFISICAL_PROJECT_ID si usas machine identity, y slug production/prod)." >&2
  exit 1
fi

if [ -n "${APP_PRODUCT_OVERRIDE:-}" ]; then
  tmp="${OUT}.__tmp__"
  grep -v '^[[:space:]]*APP_PRODUCT=' "$OUT" > "$tmp" 2>/dev/null || : > "$tmp"
  mv "$tmp" "$OUT"
  echo "APP_PRODUCT=$APP_PRODUCT_OVERRIDE" >> "$OUT"
fi

# Contraseña sudo: copia a ~/.deploy_sudo_password (remote-deploy) y se conserva en admin_agent/.env
# para que uvicorn/run_shell puedan usar sudo -S (riesgo: secreto en proceso + fichero .env del agente).
_extract_vm_deploy_sudo_password() {
  local line v
  line=$(grep -m1 -iE '^[[:space:]]*VM_DEPLOY_SUDO_PASSWORD[[:space:]]*=' "$OUT" 2>/dev/null || true)
  [ -n "$line" ] || return 0
  v="${line#*=}"
  v="${v%\"}"
  v="${v#\"}"
  v="${v%\'}"
  v="${v#\'}"
  v="${v#"${v%%[![:space:]]*}"}"
  v="${v%"${v##*[![:space:]]}"}"
  [ -n "$v" ] || return 0
  printf '%s\n' "$v" >"${HOME}/.deploy_sudo_password"
  chmod 600 "${HOME}/.deploy_sudo_password" 2>/dev/null || true
  echo "OK VM_DEPLOY_SUDO_PASSWORD → ${HOME}/.deploy_sudo_password (también en $OUT para el agente)" >&2
}
_extract_vm_deploy_sudo_password

_sync_symfony_admin_keys_from_agent_env
echo "OK Infisical → $OUT"
