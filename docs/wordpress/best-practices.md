# Plugin Best Practices

> Source: https://developer.wordpress.org/plugins/plugin-basics/best-practices/ (saved 2025-06-25)

## Avoid naming collisions

- Prefix functions, classes, options, and constants (≥4 chars, unique; avoid `wp_`, `__`, `_`).
- This project uses prefix **`mhdk_`** / namespace-style **`MH_Dev_Kit`**.

```php
if ( ! function_exists( 'mhdk_bootstrap' ) ) {
    function mhkdk_bootstrap() { /* ... */ }
}
```

Prefer OOP with `if ( ! class_exists( 'MH_Dev_Kit' ) )`.

## File organization

```
/plugin-name
    plugin-name.php
    uninstall.php          # optional
    /languages
    /includes
    /admin
    /public
```

## Security — block direct access

At top of main plugin file and any direct-access PHP:

```php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```

## Conditional loading

```php
if ( is_admin() ) {
    require_once __DIR__ . '/admin/class-mh-dev-kit-admin.php';
}
```

Always check capabilities in admin code; `is_admin()` alone is not enough.

## Architecture

For larger plugins: main file + classes in `/includes`, separate admin/public assets.
