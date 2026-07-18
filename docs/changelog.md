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

### Order Menu feature (v0.4.0)

- WooCommerce bulk-order table shortcode `[mhdk_order_menu]` with category, ids, or slugs filters
- Nonce-validated cart submission; PoC page at `/bulk-order-menu/`
- Extended Shepparton seed catalog to 20 products for demo

### Order Menu quantity controls

- Per-item decrease, increase, and clear buttons; Quantity in Cart display under controls
- Stay on order menu page after bulk add (redirect to referer, not cart)

### Order Menu v0.5.0 (plan items #1–#20)

- AJAX cart, line subtotals, unit column, stock badges, search, category headers, sort attrs
- Clear all, loading states, print CSS, reorder last order, optional thumbnails
- MHDK Settings → Order Menu, shortcode generator, saved menus CPT, Gutenberg block
- Product meta: menu description, seasonal availability; bulk order source on checkout

### Module layer refactor (v0.6.0)

- Plugin → Module → Feature architecture; `config/modules.php` replaces `config/features.php`
- **Core** module hosts MHDK-POC reference features; **Commerce** module hosts Order Menu
- Single top-level **MHDK Settings** menu; modules add subpages only (removed MHDK Content top-level)
- **MHDK Settings → Modules** page to enable/disable modules at runtime
- Features moved to `src/Modules/{Module}/Features/`; namespaces updated
- `ModuleAdminContributorInterface` replaces `FeatureAdminContributorInterface`

### WP Admin UI alignment (v0.6.1)

- Shared Settings API shell partial (`Core/Views/admin/partials/settings-page.php`)
- MHDK-POC settings: fixed `settings_fields()` group, field descriptions, removed custom admin CSS
- Order Menu settings: migrated to Settings API field callbacks; shortcode generator JS enqueued
- Modules page: `WP_List_Table` subclass, `wp-heading-inline`, `settings_errors()`
- Content hubs and settings dashboard: core list/header markup; removed `mhdk-admin` wrapper
- CPT meta boxes: `form-table` layout matching post editor
- Added `Core/Admin/SettingsFieldRenderer` helper; updated module `_Template` scaffold

### Platform features batch (v0.7.0)

Nine new features across Site Tools, User Tools, and Content Tools modules (plugin v0.7.0). Core module retains only the MHDK-POC reference.

**Site Tools module** (new)
- Backend Restrict Origin — Referer/Origin admin guard with settings toggle
- Map Distance — `[mhdk_map_distance]` shortcode, haversine geolocation, demo page

**Content Tools module** (new)
- Children Pages — Gutenberg block + `[mhdk_children_pages]` shortcode
- Markdown Editor — dynamic Gutenberg block (marked.js CDN)
- My Local Ad Page — CPT, `/local/{country}/{state}/{suburb}-{postcode}/` URLs, REST CRUD + upload, Shepparton VIC seed

**User Tools module** (new)
- QR Code Generator — user-scoped CPT, `[mhdk_qr_code]` shortcode
- vCard Generator — user CPT, `/vcard/{id}/` download, `[mhdk_vcard]` shortcode
- Shortlinks Manager — Linktree-style pages at `/l/{slug}/`
- Family Tree Builder — Vue.js app, `[mhdk_family_tree]`, REST save endpoint

**Recategorization:** v0.7.0 features initially landed in Core; moved to Site Tools, User Tools, and Content Tools so Core contains only `MhdkPocFeature`.

Shared helpers: `BlockCategoryHelper`, `UserScopedCptHelper`, settings partial. Per-feature README under each feature folder. Run `./scripts/composer-mh-dev-kit.sh dump-autoload -o` after deploy; flush permalinks for new rewrite rules.
