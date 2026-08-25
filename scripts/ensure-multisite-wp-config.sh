#!/usr/bin/env bash
# Ensure wp-config.php includes multisite constants after DDEV regeneration.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
WP_CONFIG="$ROOT/wp-config.php"
MARKER="wp-config-multisite.php"

if ! grep -q "$MARKER" "$WP_CONFIG"; then
  echo "==> Adding multisite require to wp-config.php..."
  sed -i "/Add any custom values/a\\
require_once __DIR__ . '/wp-config-multisite.php';\\
" "$WP_CONFIG"
fi
