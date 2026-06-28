#!/usr/bin/env bash
# Install WooCommerce + Storefront and configure COD-only checkout.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

run_woocommerce_setup() {
  if ! ddev wp core is-installed 2>/dev/null; then
    echo "ERROR: WordPress is not installed. Run ./scripts/setup-wordpress.sh first."
    exit 1
  fi

  echo "==> Installing WooCommerce..."
  if ddev wp plugin is-installed woocommerce 2>/dev/null; then
    ddev wp plugin activate woocommerce
  else
    ddev wp plugin install woocommerce --activate
  fi

  echo "==> Installing Storefront theme..."
  if ddev wp theme is-installed storefront 2>/dev/null; then
    ddev wp theme activate storefront
  else
    ddev wp theme install storefront --activate
  fi

  echo "==> Ensuring WooCommerce store pages exist..."
  if ddev wp wc tool list --user=1 2>/dev/null | grep -q install_pages; then
    ddev wp wc tool run install_pages --user=1 2>/dev/null || true
  fi

  echo "==> Configuring permalinks..."
  ddev wp rewrite structure '/%postname%/' --hard
  ddev wp rewrite flush --hard

  echo "==> Setting baseline WooCommerce options..."
  ddev wp option update woocommerce_store_address "123 Store Street"
  ddev wp option update woocommerce_store_city "Store City"
  ddev wp option update woocommerce_default_country "US:CA"
  ddev wp option update woocommerce_currency "USD"
  ddev wp option update woocommerce_weight_unit "lbs"
  ddev wp option update woocommerce_dimension_unit "in"
  ddev wp option update woocommerce_calc_taxes "no"
  ddev wp option update woocommerce_enable_guest_checkout "yes"
  ddev wp option update woocommerce_enable_checkout_login_reminder "yes"
  ddev wp option update woocommerce_enable_signup_and_login_from_checkout "yes"
  ddev wp option update woocommerce_catalog_hide_out_of_stock_items "no"
  ddev wp option update woocommerce_coming_soon "no"

  echo "==> Configuring payment gateways (COD only)..."
  ddev wp option update woocommerce_cod_settings \
    '{"enabled":"yes","title":"Cash on delivery","description":"Pay with cash upon delivery.","instructions":"Pay with cash when your order arrives.","enable_for_methods":[],"enable_for_virtual":false}' \
    --format=json

  for gateway in bacs cheque; do
    if ddev wp option get "woocommerce_${gateway}_settings" >/dev/null 2>&1; then
      ddev wp option patch update "woocommerce_${gateway}_settings" enabled no 2>/dev/null || true
    fi
  done

  echo "==> Ensuring mh-dev-kit is active..."
  ddev wp plugin activate mh-dev-kit 2>/dev/null || true

  echo ""
  echo "==> Verification"
  echo "--- Active plugins ---"
  ddev wp plugin list --status=active --fields=name,version
  echo ""
  echo "--- Active theme ---"
  ddev wp theme list --status=active --fields=name,version
  echo ""
  echo "--- Payment gateways ---"
  ddev wp wc payment_gateway list --user=1 --fields=id,enabled,title 2>/dev/null || echo "(WooCommerce CLI unavailable)"
  echo ""
  echo "--- Store pages ---"
  ddev wp post list --post_type=page --fields=ID,post_title,post_name --format=table
  echo ""
  echo "Shop:     https://cursor-wpdev2.ddev.site/shop/"
  echo "Cart:     https://cursor-wpdev2.ddev.site/cart/"
  echo "Checkout: https://cursor-wpdev2.ddev.site/checkout/"
  echo "Admin:    https://cursor-wpdev2.ddev.site/wp-admin/ (admin / admin)"
}

if docker info >/dev/null 2>&1; then
  run_woocommerce_setup
elif getent group docker | grep -qE "[[:space:]]${USER}(,|$)"; then
  echo "Docker group membership not active in this shell; re-running via sg docker..."
  exec sg docker -c "cd '$ROOT' && ./scripts/setup-woocommerce.sh"
else
  echo "ERROR: Cannot access Docker. Add your user to the docker group and re-login:"
  echo "  sudo usermod -aG docker \$USER"
  echo "Then log out/in (or reboot) and re-run this script."
  exit 1
fi
