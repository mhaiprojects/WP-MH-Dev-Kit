# MH Dev Kit architecture

Monolithic WordPress plugin with **Composer PSR-4 autoloading**, **modules** (sub-plugins), and **MVP features**.

## Plugin layout

```
wp-content/plugins/mh-dev-kit/
  composer.json          # PHP >=8.1, PSR-4 autoload only
  config/modules.php     # Registered module classes
  mh-dev-kit.php         # Bootstrap + activation hooks
  src/
    Core/                # Plugin bootstrap, ModuleLoader, ViewRenderer, global admin shell
    Modules/
      Core/              # Core module — MHDK-POC reference + future platform features
      Commerce/          # Commerce module — WooCommerce features
      _Template/         # Copy scaffold for new modules
    Shared/              # AssetRegistrar and cross-module utilities
  vendor/                # Not in git — run composer install
```

## Layer model

| Layer | Path | Responsibility |
|-------|------|----------------|
| **Plugin** | `src/Core/` | Single top-level **MHDK Settings** menu, Modules enable/disable page, `ModuleLoader` |
| **Module** | `src/Modules/{Name}/` | Requirements, admin subpages, feature list, module boot |
| **Feature** | `src/Modules/{Name}/Features/{Feature}/` | Models, Presenters, Views, Assets — domain logic only |

**Naming:** `src/Core/` is the plugin core. `src/Modules/Core/` is the Core module — distinct namespaces.

## MVP per feature

| Folder | Contains |
|--------|----------|
| **Models/** | Options, post meta, queries, validation — no HTML, no hooks |
| **Presenters/** | `add_action` / `add_filter`, enqueue assets, call views |
| **Views/** | PHP partials and frontend templates |
| **Assets/** | CSS, JS, Gutenberg `block.json` folders |
| **`*Feature.php`** | Implements `FeatureInterface`; wires presenters (no admin menu registration) |

## Admin menus

| Owner | Item | Slug |
|-------|------|------|
| Plugin | MHDK Settings (top-level) | `mhdk-settings` |
| Plugin | Modules (enable/disable) | `mhdk-settings-modules` |
| Core module | MHDK-POC settings | `mhdk-settings-poc` |
| Core module | Content hub + CPTs | `mhdk-core-content` |
| Commerce module | Order Menu settings | `mhdk-settings-order-menu` |
| Commerce module | Commerce content + Saved Menus CPT | `mhdk-commerce-content` |

Modules register subpages via `ModuleAdminContributorInterface::register_admin()`. Features must not call `add_menu_page()` or `add_submenu_page()`.

## Core module (MHDK-POC)

Living reference for all current and future **core** features:

- Settings API subpage pattern
- CPT registration (`mhdk_hosp_menu`, `mhdk_web_link`)
- Gutenberg dynamic blocks (category **MHDK**)
- Post meta boxes, frontend templates, asset enqueue

## Registered modules

| Module ID | Features | Requirements |
|-----------|----------|--------------|
| `core` | `MhdkPocFeature` | WordPress |
| `commerce` | `OrderMenuFeature` | WooCommerce |

Enable/disable modules under **MHDK Settings → Modules** (`mhdk_module_settings` option).

## Composer

```bash
./scripts/composer-mh-dev-kit.sh install
./scripts/composer-mh-dev-kit.sh dump-autoload -o
```

## Adding a module

1. Copy `src/Modules/_Template/` → `src/Modules/YourModule/`
2. Implement `YourModule.php` (`ModuleInterface`, optionally `ModuleAdminContributorInterface`)
3. Add features under `Features/YourFeature/`
4. Register module class in `config/modules.php`
5. Run `composer dump-autoload -o`
6. Document in module README and `docs/changelog.md`

## Adding a feature to an existing module

1. Copy `src/Modules/_Template/Features/_Template/` into the target module
2. Implement Model / Presenter / Views / Assets
3. Add feature class to `$module::features()`
4. Register any admin subpages on the **module**, not the feature
