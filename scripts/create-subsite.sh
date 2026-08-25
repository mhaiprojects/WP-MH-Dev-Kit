#!/usr/bin/env bash
# Create or verify a subdomain multisite subsite on DDEV.
# Usage: ./scripts/create-subsite.sh SLUG "Site Title" [setup-command]
#
# Examples:
#   ./scripts/create-subsite.sh divi-demo "Divi Builder Complete Demo"
#   ./scripts/create-subsite.sh my-client "My Client Site" ./scripts/seed-my-client.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

SLUG="${1:?Subsite slug required (e.g. divi-demo)}"
TITLE="${2:?Site title required}"
SETUP_CMD="${3:-}"
NETWORK_DOMAIN="cursor-wpdev2.ddev.site"
SITE_URL="https://${SLUG}.${NETWORK_DOMAIN}"

ensure_multisite() {
  bash "$ROOT/scripts/ensure-multisite-wp-config.sh"

  if ! ddev wp core is-installed --network 2>/dev/null; then
    echo "==> Converting to multisite..."
    ddev wp core multisite-convert --title="Cursor WP Dev Network" --subdomains --skip-config --allow-root
    bash "$ROOT/scripts/ensure-multisite-wp-config.sh"
  fi
}

run() {
  ensure_multisite

  echo "==> Ensuring subsite: ${SLUG}..."
  if ! ddev wp site list --field=url 2>/dev/null | grep -q "${SLUG}.${NETWORK_DOMAIN}"; then
    ddev wp site create \
      --slug="${SLUG}" \
      --title="${TITLE}" \
      --email="admin@example.com"
  fi

  ddev wp option update home "${SITE_URL}" --url="${SITE_URL}"
  ddev wp option update siteurl "${SITE_URL}" --url="${SITE_URL}"

  if [[ -n "${SETUP_CMD}" ]]; then
    echo "==> Running setup: ${SETUP_CMD}..."
    bash -c "${SETUP_CMD}"
  fi

  echo ""
  echo "Subsite ready: ${SITE_URL}"
  echo "Admin:         ${SITE_URL}/wp-admin/"
  ddev wp site list
}

if docker info >/dev/null 2>&1; then
  run
else
  exec sg docker -c "cd '$ROOT' && ./scripts/create-subsite.sh '$SLUG' '$TITLE' '$SETUP_CMD'"
fi
