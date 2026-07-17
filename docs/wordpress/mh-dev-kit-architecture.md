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

## Admin UI

MHDK admin pages follow WordPress core markup — no custom admin CSS framework.

### Settings pages (General Settings pattern)

Use the shared partial `Core/Views/admin/partials/settings-page.php`:

- `div.wrap` → `h1` (`get_admin_page_title()`) → `settings_errors()` → form `action="options.php"`
- `settings_fields( $option_group )` must match `register_setting()` group
- `do_settings_sections( $page_slug )` renders `form-table` rows from field callbacks
- `submit_button()` at the bottom

Field callbacks should use `Core\Admin\SettingsFieldRenderer` (`regular-text`, `large-text`, `small-text`, `description` paragraphs). Register fields in the presenter via `add_settings_section()` / `add_settings_field()`.

### List pages (Posts list pattern)

- `h1.wp-heading-inline`, optional `a.page-title-action`, `hr.wp-header-end`
- `settings_errors()` after save on option pages
- `table.wp-list-table.widefat.fixed.striped` or `WP_List_Table` subclass (see `Core/Admin/ModulesListTable.php`)

### Meta boxes (post editor pattern)

Use `table.form-table` with `th scope="row"` / `td`, core input classes (`regular-text`, `widefat`, `large-text`).

### Rules

- Use `div.wrap` only — avoid custom wrapper classes unless a JS hook requires an ID
- Enqueue admin JS via `admin_enqueue_scripts`; no inline scripts in views
- Avoid custom admin CSS unless core classes cannot express the layout

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
