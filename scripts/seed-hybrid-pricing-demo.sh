#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"
ddev wp eval-file scripts/seed-hybrid-pricing-demo.php --url=https://cursor-wpdev2.ddev.site
