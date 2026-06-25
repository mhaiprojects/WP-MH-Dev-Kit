#!/usr/bin/env bash
# Run Composer inside mh-dev-kit via DDev.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CMD="cd wp-content/plugins/mh-dev-kit && composer $*"

run() {
  ddev exec "$CMD"
}

if docker info >/dev/null 2>&1; then
  run
elif getent group docker | grep -qE "[[:space:]]${USER}(,|$)"; then
  exec sg docker -c "cd '$ROOT' && ddev exec \"$CMD\""
else
  echo "ERROR: Docker not available. See docs/setup.md"
  exit 1
fi
