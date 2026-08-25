#!/usr/bin/env bash
# Create divi-demo multisite subsite and seed comprehensive Divi Builder showcase pages.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DEMO_URL="https://divi-demo.cursor-wpdev2.ddev.site"
DEMO_SLUG="divi-demo"
WP_CONFIG="$ROOT/wp-config.php"

ensure_multisite_constants() {
  bash "$ROOT/scripts/ensure-multisite-wp-config.sh"
}

run() {
  echo "==> Extracting Divi module catalog..."
  ddev exec php scripts/extract-divi-module-reference.php

  echo "==> Ensuring multisite is configured..."
  ensure_multisite_constants

  if ! ddev wp core is-installed --network 2>/dev/null; then
    echo "==> Converting to multisite..."
    ddev wp core multisite-convert --title="Cursor WP Dev Network" --subdomains --skip-config --allow-root
    ensure_multisite_constants
  fi

  echo "==> Ensuring divi-demo subsite exists..."
  if ! ddev wp site list --field=url 2>/dev/null | grep -q "divi-demo"; then
    ddev wp site create \
      --slug="${DEMO_SLUG}" \
      --title="Divi Builder Complete Demo" \
      --email="admin@example.com"
  fi

  ddev wp option update home "${DEMO_URL}" --url="${DEMO_URL}"
  ddev wp option update siteurl "${DEMO_URL}" --url="${DEMO_URL}"

  echo "==> Activating Divi on divi-demo subsite..."
  ddev wp theme activate Divi --url="${DEMO_URL}"

  echo "==> Seeding Divi demo pages..."
  ddev wp eval-file scripts/seed-divi-demo-site.php --url="${DEMO_URL}"

  echo "==> Removing stray Divi demo content from main site..."
  ddev wp eval-file scripts/cleanup-main-site-divi-demo.php --url="https://cursor-wpdev2.ddev.site" 2>/dev/null || true

  echo ""
  echo "Done."
  echo "  Divi demo site:  ${DEMO_URL}"
  echo "  Divi demo admin: ${DEMO_URL}/wp-admin/ (admin / admin)"
  echo "  Agent skill:     .cursor/skills/divi-builder/SKILL.md"
  echo ""
  ddev wp post list --post_type=page --fields=ID,post_title,post_name --url="${DEMO_URL}"
}

if docker info >/dev/null 2>&1; then
  run
else
  exec sg docker -c "cd '$ROOT' && ./scripts/setup-divi-demo-multisite.sh"
fi
