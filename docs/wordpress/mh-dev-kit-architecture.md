# MH Dev Kit architecture

Monolithic WordPress plugin with **Composer PSR-4 autoloading** and **MVP feature modules**.

## Plugin layout

```
wp-content/plugins/mh-dev-kit/
  composer.json          # PHP >=8.1, PSR-4 autoload only
  config/features.php    # Registered feature classes
  mh-dev-kit.php         # Bootstrap + activation hooks
  src/
    Core/                # Plugin, FeatureLoader, ViewRenderer, Admin
    Shared/              # AssetRegistrar
    Features/
      MhdkPoc/           # MHDK-POC feature
      _Template/         # Copy scaffold for new features
  vendor/                # Not in git — run composer install
```

## MVP per feature

| Folder | Contains |
|--------|----------|
| **Models/** | Options, post meta, queries, validation — no HTML, no hooks |
| **Presenters/** | `add_action` / `add_filter`, enqueue assets, call views |
| **Views/** | PHP partials and frontend templates |
| **Assets/** | CSS, JS, Gutenberg `block.json` folders |
| **`*Feature.php`** | Implements `FeatureInterface`; wires presenters |

## Core admin

| Menu | Slug | Purpose |
|------|------|---------|
| **MHDK Settings** | `mhdk-settings` | Plugin settings dashboard |
| **MHDK Content** | `mhdk-content` | Hub for feature CPTs |

Features register settings subpages via `FeatureAdminContributorInterface::register_admin()`.
CPTs attach with `'show_in_menu' => AdminMenu::CONTENT_SLUG`.

## MHDK-POC

### Admin

- **MHDK Settings** → **MHDK-POC** settings (`Settings API`)
- **MHDK Content** → Hospitality Menus, Web Links

### Custom post types

| Slug | Label | Notes |
|------|-------|-------|
| `mhdk_hosp_menu` | Hospitality Menu | Max 20 chars (WP limit); title, editor, thumbnail |
| `mhdk_web_link` | Web Link | URL + description meta |

### Gutenberg blocks (no build tools)

Dynamic blocks via `block.json` + `render.php` (category **MHDK**):

- `mh-dev-kit/hospitality-menu`
- `mh-dev-kit/web-links`
- `mh-dev-kit/callout`

Editor: `blocks-editor.js` (ServerSideRender preview) + `block-inspector.js` (sidebar controls).

### Composer

```bash
./scripts/composer-mh-dev-kit.sh install
./scripts/composer-mh-dev-kit.sh dump-autoload -o
```

## Adding a feature

1. Copy `src/Features/_Template/` → `src/Features/YourFeature/`
2. Implement Model / Presenter / Views / Assets
3. Add class to `config/features.php`
4. Optionally implement `FeatureAdminContributorInterface` for MHDK Settings subpages
5. Run `composer dump-autoload -o`
6. Document in feature `README.md` and `docs/changelog.md`
