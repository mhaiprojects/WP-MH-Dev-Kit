# Determining Plugin and Content Directories

> Source: https://developer.wordpress.org/plugins/plugin-basics/determining-plugin-and-content-directories/ (saved 2025-06-25)

Never hardcode `wp-content/plugins/...` paths. Use WordPress API functions.

## Plugin paths and URLs

```php
plugins_url( 'assets/script.js', __FILE__ );
plugin_dir_url( __FILE__ );
plugin_dir_path( __FILE__ );
plugin_basename( __FILE__ );
```

## Enqueue assets

Use `wp_enqueue_script()` / `wp_enqueue_style()` with URLs from `plugins_url()`.

## Constants (reference only — prefer functions)

- `WP_PLUGIN_DIR`, `WP_PLUGIN_URL`
- `WP_CONTENT_DIR`, `WP_CONTENT_URL`

## Multisite / custom content dir

Sites may relocate or rename `wp-content`. Always use discovery functions above.

## mh-dev-kit convention

Define in main plugin file:

```php
define( 'MH_DEV_KIT_VERSION', '0.1.0' );
define( 'MH_DEV_KIT_FILE', __FILE__ );
define( 'MH_DEV_KIT_PATH', plugin_dir_path( __FILE__ ) );
define( 'MH_DEV_KIT_URL', plugin_dir_url( __FILE__ ) );
```
