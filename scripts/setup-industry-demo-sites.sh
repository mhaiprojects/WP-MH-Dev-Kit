#!/usr/bin/env bash
# Create/seed three industry demo subsites (cafe, factory sales, hospitality supplies).
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

bash "$ROOT/scripts/ensure-multisite-wp-config.sh"

create_and_seed() {
  local slug="$1"
  local title="$2"
  local seed="$3"
  local url="https://${slug}.cursor-wpdev2.ddev.site"

  echo "==> Ensuring subsite: ${slug}"
  if ! ddev wp site list --field=url 2>/dev/null | grep -E -q "https?://${slug}\\.cursor-wpdev2\\.ddev\\.site"; then
    ddev wp site create --slug="${slug}" --title="${title}" --email="admin@example.com"
  fi

  ddev wp option update home "${url}" --url="${url}"
  ddev wp option update siteurl "${url}" --url="${url}"
  ddev wp theme activate Divi --url="${url}"
  ddev wp plugin activate woocommerce --url="${url}"
  ddev wp plugin activate mix-and-match-bundle --url="${url}" || true

  ddev wp option update woocommerce_store_city "Shepparton" --url="${url}"
  ddev wp option update woocommerce_default_country "AU:VIC" --url="${url}"
  ddev wp option update woocommerce_store_postcode "3630" --url="${url}"
  ddev wp option update woocommerce_currency "AUD" --url="${url}"

  echo "==> Seeding ${slug}..."
  ddev wp eval-file "scripts/${seed}" --url="${url}"
  echo "Ready: ${url}"
}

create_and_seed "cafe-takeaway" "Riverbank Cafe & Takeaway" "seed-cafe-takeaway-site.php"
create_and_seed "factory-sales" "Goulburn Factory Sales" "seed-factory-sales-site.php"
create_and_seed "hospitality-supplies" "GV Hospitality Supplies" "seed-hospitality-supplies-site.php"

echo ""
echo "Done. Multisite sites:"
ddev wp site list
