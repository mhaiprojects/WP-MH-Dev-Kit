# Changelog

All notable requests and project changes.

## 2025-06-25

### Initial setup (user request)

- Bootstrap WordPress with DDEV (`cursor-wpdev2`)
- Create `docs/` for requests and changes
- Initialize git: ignore WP core, track `docs/` and custom plugin `mh-dev-kit`
- Save local copies of DDEV and WordPress plugin docs under `docs/ddev-docs/` and `docs/wordpress/`
- Scaffold `mh-dev-kit` plugin (activation hooks, admin notice)
- Add `scripts/setup-wordpress.sh` for reproducible bootstrap

### Foundation PoC — Composer + MVP refactor (v0.2.0)

- PSR-4 autoload via Composer (`src/` namespace `MH\DevKit\`)
- **Foundation** feature: Settings API, 2 CPTs (`mhdk_hosp_menu`, `mhdk_web_link`), 3 dynamic Gutenberg blocks
- MVP folders per feature: Models, Presenters, Views, Assets
- Admin menus: **MH Dev Kit** + **MH Dev Kit Content**
- No npm/webpack; block editor JS uses bundled `wp.*` globals
- `./scripts/composer-mh-dev-kit.sh` helper added

### Core admin + MHDK-POC rename (v0.3.0)

- Renamed **Foundation** feature → **MHDK-POC** (`src/Features/MhdkPoc/`)
- Core admin menus: **MHDK Settings** (`mhdk-settings`) and **MHDK Content** (`mhdk-content`)
- `AdminMenuRegistrar` + `FeatureAdminContributorInterface` for feature settings subpages
- Block category renamed to **MHDK**; editor preview via `blocks-editor.js` + inspector controls
- Settings option migrated from `mhdk_foundation_settings` → `mhdk_poc_settings`

### WooCommerce store — COD checkout

- Added `scripts/setup-woocommerce.sh`: WooCommerce + Storefront, COD-only payments, store pages
- Wired into `scripts/setup-wordpress.sh` for one-command bootstrap
- Documented shop URLs, post-setup checklist, and future Stripe/PayPal path in `docs/setup.md`

### Shepparton produce catalog

- Added `scripts/seed-shepparton-products.sh`: 10 Goulburn Valley products with vendor details in descriptions
- Store region set to Shepparton VIC, currency AUD
