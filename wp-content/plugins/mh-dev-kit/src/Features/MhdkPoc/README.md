# MHDK-POC

Proof-of-concept feature for MH Dev Kit.

## Admin

- **MHDK Settings** → **MHDK-POC** settings subpage (registered via Core `AdminMenuRegistrar`)
- **MHDK Content** → Hospitality Menus and Web Links CPTs (`show_in_menu` → `mhdk-content`)

## Blocks (category: MHDK)

- `mh-dev-kit/hospitality-menu`
- `mh-dev-kit/web-links`
- `mh-dev-kit/callout`

## Registering a new feature

Implement `FeatureAdminContributorInterface` and call `AdminMenuRegistrar::register_contributor()` from `FeatureLoader`, or implement `register_admin()` on your feature class:

```php
public function register_admin( AdminMenuRegistrar $registrar ): void {
    $registrar->add_settings_submenu(
        'my-feature-settings',
        __( 'My Feature', 'mh-dev-kit' ),
        __( 'My Feature Settings', 'mh-dev-kit' ),
        fn() => /* render settings page */,
    );
}
```

CPTs attach to **MHDK Content** with `'show_in_menu' => AdminMenu::CONTENT_SLUG`.
