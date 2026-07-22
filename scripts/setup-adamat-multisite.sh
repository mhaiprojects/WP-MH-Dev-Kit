#!/usr/bin/env bash
# Convert DDEV WordPress to subdomain multisite and seed the adamat.local site.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

ADAMAT_URL="https://adamat.cursor-wpdev2.ddev.site"
ADAMAT_SLUG="adamat"
WP_CONFIG="$ROOT/wp-config.php"

ensure_multisite_constants() {
  if grep -q "define( 'MULTISITE', true )" "$WP_CONFIG"; then
    return
  fi

  echo "==> Adding multisite constants to wp-config.php..."
  sed -i "/stop editing/i\\
define( 'WP_ALLOW_MULTISITE', true );\\
define( 'MULTISITE', true );\\
define( 'SUBDOMAIN_INSTALL', true );\\
define( 'DOMAIN_CURRENT_SITE', 'cursor-wpdev2.ddev.site' );\\
define( 'PATH_CURRENT_SITE', '/' );\\
define( 'SITE_ID_CURRENT_SITE', 1 );\\
define( 'BLOG_ID_CURRENT_SITE', 1 );\\
" "$WP_CONFIG"
}

run() {
  echo "==> Restarting DDEV with multisite hostnames..."
  ddev restart

  echo "==> Converting to multisite (if needed)..."
  if ! ddev wp core is-installed --network 2>/dev/null; then
    ddev wp core multisite-convert --title="Cursor WP Dev Network" --subdomains --skip-config --allow-root
    ensure_multisite_constants
  fi

  ensure_multisite_constants

  echo "==> Ensuring adamat subsite exists..."
  if ! ddev wp site list --field=url --url="${ADAMAT_URL}" 2>/dev/null | grep -q "adamat"; then
    ddev wp site create \
      --slug="${ADAMAT_SLUG}" \
      --title="adamat | Web design, Graphic Design, eCommerce & AI" \
      --email="admin@example.com"
  fi

  ddev wp option update home "${ADAMAT_URL}" --url="${ADAMAT_URL}"
  ddev wp option update siteurl "${ADAMAT_URL}" --url="${ADAMAT_URL}"

  echo "==> Activating Divi on adamat subsite..."
  ddev wp theme activate Divi --url="${ADAMAT_URL}"

  echo "==> Creating adamat pages..."
  ddev wp eval-file "$ROOT/scripts/seed-adamat-site.php" --url="${ADAMAT_URL}"

  echo ""
  echo "Done."
  echo "  Network admin: https://cursor-wpdev2.ddev.site/wp-admin/network/"
  echo "  Main dev site: https://cursor-wpdev2.ddev.site/"
  echo "  adamat site:   ${ADAMAT_URL}"
  echo "  adamat admin:  ${ADAMAT_URL}/wp-admin/ (admin / admin)"
  echo ""
  ddev wp theme list --url="${ADAMAT_URL}" --status=active
  ddev wp post list --post_type=page --fields=ID,post_title,post_name --url="${ADAMAT_URL}"
}

if docker info >/dev/null 2>&1; then
  run
else
  exec sg docker -c "cd '$ROOT' && ./scripts/setup-adamat-multisite.sh"
fi
