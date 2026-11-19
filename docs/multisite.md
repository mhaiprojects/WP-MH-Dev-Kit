# WordPress multisite (DDEV)

Subdomain network on `*.cursor-wpdev2.ddev.site`. Multisite constants live in `wp-config-multisite.php` (included from `wp-config.php`). After `ddev restart`, the post-start hook runs `scripts/ensure-multisite-wp-config.sh`.

## Site registry

| Blog | Slug | URL | Purpose |
|------|------|-----|---------|
| 1 | *(main)* | https://cursor-wpdev2.ddev.site | Plugin dev, WooCommerce, mh-dev-kit module demos — **keep clean** |
| 2 | `adamat` | https://adamat.cursor-wpdev2.ddev.site | adamat client showcase (Divi) |
| 3 | `divi-demo` | https://divi-demo.cursor-wpdev2.ddev.site | Divi builder + WooCommerce grocery demo (20 products) |
| 4 | `cafe-takeaway` | https://cafe-takeaway.cursor-wpdev2.ddev.site | Riverbank Cafe & Takeaway (Shepparton-style) |
| 5 | `factory-sales` | https://factory-sales.cursor-wpdev2.ddev.site | Goulburn Factory Sales B2C outlet + TempApp package pricing |
| 6 | `hospitality-supplies` | https://hospitality-supplies.cursor-wpdev2.ddev.site | GV Hospitality Supplies B2B catalogue |

## Rules

1. **Main site (blog 1)** — mh-dev-kit demos, WooCommerce test data, plugin admin. Do **not** seed theme showcases or client sites here.
2. **Existing subsites** — reuse `adamat` or `divi-demo` when the task matches their purpose.
3. **New work** — create a new subsite when a demo/client site needs isolation:

   ```bash
   ./scripts/create-subsite.sh my-slug "Human-readable title"
   ```

4. **Always pass `--url`** on WP-CLI when targeting a subsite:

   ```bash
   ddev wp eval-file scripts/seed-divi-demo-site.php --url=https://divi-demo.cursor-wpdev2.ddev.site
   ```

5. **Seed scripts must guard host/blog** — refuse to run on the wrong site (see `seed-divi-demo-site.php`).

## Setup commands

| Task | Command |
|------|---------|
| Verify network | `ddev wp site list` |
| Ensure wp-config | `bash scripts/ensure-multisite-wp-config.sh` |
| Divi demo subsite | `./scripts/setup-divi-demo-multisite.sh` |
| adamat subsite | `./scripts/setup-adamat-multisite.sh` |
| Industry demos (3 sites) | `./scripts/setup-industry-demo-sites.sh` |
| Clean demo from main | `ddev wp eval-file scripts/cleanup-main-site-divi-demo.php --url=https://cursor-wpdev2.ddev.site` |
| Network admin | https://cursor-wpdev2.ddev.site/wp-admin/network/ |

## Troubleshooting

- **“Multisite not enabled”** — run `bash scripts/ensure-multisite-wp-config.sh` and confirm `require_once wp-config-multisite.php` is in `wp-config.php`.
- **Wrong site gets seeded** — check `--url`; run cleanup on main if needed.
- **Subsite 404** — confirm `.ddev/config.yaml` has `additional_hostnames: ["*.cursor-wpdev2"]` and `ddev restart`.
