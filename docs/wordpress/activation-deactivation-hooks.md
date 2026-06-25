# Activation / Deactivation Hooks

> Source: https://developer.wordpress.org/plugins/activation-deactivation-hooks/ (saved 2025-06-25)

## Activation

Run setup when the plugin is activated (rewrite rules, default options, custom tables):

```php
register_activation_hook( __FILE__, 'pluginprefix_activate' );
```

## Deactivation

Cleanup that should run when deactivated (not full uninstall):

```php
register_deactivation_hook( __FILE__, 'pluginprefix_deactivate' );
```

## Uninstall

Permanent deletion belongs in `uninstall.php` (or uninstall hook), not deactivation.

## Example pattern

```php
function pluginprefix_activate() {
    // one-time setup
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'pluginprefix_activate' );

function pluginprefix_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'pluginprefix_deactivate' );
```

Hooks must be registered from the **main plugin file** (the file with the plugin header), or the first parameter to `register_*_hook` must point to that file.
