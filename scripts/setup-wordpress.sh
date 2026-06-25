#!/usr/bin/env bash
# Bootstrap WordPress via DDEV — commands from official docs (docs/ddev-docs/wordpress-quickstart.md)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

if ! docker info >/dev/null 2>&1; then
  echo "ERROR: Cannot access Docker. Add your user to the docker group and re-login:"
  echo "  sudo usermod -aG docker \$USER"
  echo "Then log out/in (or reboot) and re-run this script."
  exit 1
fi

ddev version

ddev config \
  --project-name=cursor-wpdev2 \
  --project-type=wordpress \
  --docroot="" \
  --php-version=8.3

ddev config --web-environment-add="WP_ENVIRONMENT_TYPE=local"

ddev start -y

if [ ! -f wp-settings.php ]; then
  ddev wp core download
fi

if ! ddev wp core is-installed 2>/dev/null; then
  ddev wp core install \
    --url='$DDEV_PRIMARY_URL' \
    --title='Cursor WP Dev 2' \
    --admin_user=admin \
    --admin_password=admin \
    --admin_email=admin@example.com \
    --skip-email
fi

ddev wp plugin activate mh-dev-kit

echo ""
ddev describe
echo ""
echo "Site:  https://cursor-wpdev2.ddev.site"
echo "Admin: https://cursor-wpdev2.ddev.site/wp-admin/ (admin / admin)"
