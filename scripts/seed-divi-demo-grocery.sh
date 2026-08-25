#!/usr/bin/env bash
# Seed 20 grocery products and categories on the divi-demo subsite.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DEMO_URL="https://divi-demo.cursor-wpdev2.ddev.site"

run() {
  echo "==> Activating WooCommerce on divi-demo..."
  ddev wp plugin activate woocommerce --url="${DEMO_URL}" 2>/dev/null || true

  if ! ddev wp plugin is-active woocommerce --url="${DEMO_URL}"; then
    echo "ERROR: WooCommerce could not be activated on divi-demo."
    exit 1
  fi

  echo "==> Running WooCommerce setup wizard defaults..."
  ddev wp wc tool run install_pages --user=1 --url="${DEMO_URL}" 2>/dev/null || true
  ddev wp option update woocommerce_currency "AUD" --url="${DEMO_URL}"
  ddev wp option update woocommerce_default_country "AU:VIC" --url="${DEMO_URL}"

  echo "==> Seeding grocery catalog..."
  ddev wp eval-file scripts/seed-divi-demo-grocery.php --url="${DEMO_URL}"

  echo "==> Refreshing Divi demo pages, menus, and footer..."
  ddev wp eval-file scripts/seed-divi-demo-site.php --url="${DEMO_URL}"

  echo ""
  echo "Done. Shop: ${DEMO_URL}/shop/"
  ddev wp post list --post_type=product --fields=ID,post_title --url="${DEMO_URL}" | head -25
}

if docker info >/dev/null 2>&1; then
  run
else
  exec sg docker -c "cd '$ROOT' && ./scripts/seed-divi-demo-grocery.sh"
fi
