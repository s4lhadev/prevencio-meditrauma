#!/usr/bin/env bash
# Genera admin_agent/.env con solo INFISICAL_TOKEN. Prueba slugs production / prod.
# APP_PRODUCT_OVERRIDE: workflow (medisalut / prevencion). Sin token, sale 0.
# No exige sudo: npx, binario en ~/.local/bin, o apt si sudo -n.
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
rm -f "$OUT"
set +e
ok=0
for ENV_NAME in $ENV_CANDIDATES; do
  run_infisical export --env="$ENV_NAME" --format=dotenv --output-file="$OUT" --token="$INFISICAL_TOKEN" 2>/dev/null
  if [ -s "$OUT" ]; then ok=1; break; fi
  rm -f "$OUT"
  (cd "$REPO_ROOT/$AGENT_SUB" && run_infisical run --env="$ENV_NAME" --command="env" > "$OUT" 2>/dev/null)
  if [ -s "$OUT" ]; then ok=1; break; fi
  rm -f "$OUT"
done
set -e

if [ "$ok" -ne 1 ] || [ ! -s "$OUT" ]; then
  echo "ERROR: no se pudo generar $OUT (token o slug de entorno production / prod en Infisical)." >&2
  exit 1
fi

if [ -n "${APP_PRODUCT_OVERRIDE:-}" ]; then
  tmp="${OUT}.__tmp__"
  grep -v '^[[:space:]]*APP_PRODUCT=' "$OUT" > "$tmp" 2>/dev/null || : > "$tmp"
  mv "$tmp" "$OUT"
  echo "APP_PRODUCT=$APP_PRODUCT_OVERRIDE" >> "$OUT"
fi

# Si no hay .env de Symfony, el merge a continuación no hace nada; Infisical solo llena admin_agent/.env
_ensure_symfony_dotenv_bootstrap() {
  local f d
  for f in "$REPO_ROOT/portal/.env" "$REPO_ROOT/current/.env"; do
    [ -f "$f" ] && continue
    d="${f}.dist"
    if [ -f "$d" ]; then
      echo "Aviso: creando $f desde $(basename "$d") (no existía; Infisical no puede fusionar a Symfony sin .env base)." >&2
      cp -a "$d" "$f"
    else
      : > "$f"
      echo "Aviso: creado $f vacío (falta .env.dist en $(dirname "$f") )." >&2
    fi
  done
}
_ensure_symfony_dotenv_bootstrap

_merge_symfony_dotenv_from_admin_agent() {
  local agent_env k line v f
  agent_env="$REPO_ROOT/$AGENT_SUB/.env"
  [ -f "$agent_env" ] || return 0
  for k in ADMIN_AGENT_INTERNAL_URL ADMIN_AGENT_SECRET ADMIN_AGENT_PAGE_KEY; do
    line=$(grep -m1 "^[[:space:]]*${k}=" "$agent_env" 2>/dev/null || true)
    [ -n "$line" ] || continue
    v="${line#*=}"
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
_merge_symfony_dotenv_from_admin_agent

# Infisical a veces no define ADMIN_AGENT_PAGE_KEY en el secret; evita el flash "Falta…" hasta que se suba la clave
_ensure_page_key_in_php_env_from_dist() {
  local distline
  distline=$(grep -m1 '^[[:space:]]*ADMIN_AGENT_PAGE_KEY=' "$REPO_ROOT/portal/.env.dist" 2>/dev/null || grep -m1 '^[[:space:]]*ADMIN_AGENT_PAGE_KEY=' "$REPO_ROOT/current/.env.dist" 2>/dev/null || true)
  [ -n "$distline" ] || return 0
  for f in "$REPO_ROOT/portal/.env" "$REPO_ROOT/current/.env"; do
    [ -f "$f" ] || continue
    val=""
    if grep -qE '^[[:space:]]*ADMIN_AGENT_PAGE_KEY=' "$f" 2>/dev/null; then
      val=$(grep -m1 '^[[:space:]]*ADMIN_AGENT_PAGE_KEY=' "$f" 2>/dev/null | cut -d= -f2-)
      val=$(printf '%s' "$val" | tr -d '\r' | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
    fi
    [ -n "$val" ] && continue
    if grep -qE '^[[:space:]]*ADMIN_AGENT_PAGE_KEY=' "$f" 2>/dev/null; then
      grep -v '^[[:space:]]*ADMIN_AGENT_PAGE_KEY=' "$f" > "${f}.new" 2>/dev/null || : > "${f}.new"
      mv "${f}.new" "$f"
    fi
    printf '%s\n' "$distline" >> "$f"
    echo "Aviso: ADMIN_AGENT_PAGE_KEY rellenado desde .env.dist en $f (pon la clave real en Infisical si no está)" >&2
  done
}
_ensure_page_key_in_php_env_from_dist

echo "OK Infisical → $OUT"
