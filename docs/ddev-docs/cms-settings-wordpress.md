# DDEV CMS Settings — WordPress Specifics

> Source: https://docs.ddev.com/en/stable/users/usage/cms-settings/#wordpress-specifics (saved 2025-06-25)

## WP-CLI and docroot

DDEV v1.24.5+ automatically adds `--path=$DDEV_DOCROOT` to `ddev wp` when needed.

If docroot is **not** the project root (e.g. `public`), create `wp-cli.yml` in the project root:

```yaml
path: public
```

This project uses empty docroot (`""`), so no `wp-cli.yml` is required.

## WordPress environment type

By default `wp_get_environment_type()` returns `production`. Override for local dev:

**Option A — environment variable (recommended, no file edits):**

```bash
ddev config --web-environment-add="WP_ENVIRONMENT_TYPE=local"
ddev restart
```

**Option B — constant in `wp-config-ddev.php`:**

Remove the `#ddev-generated` line from `wp-config-ddev.php`, then add:

```php
defined( 'WP_ENVIRONMENT_TYPE' ) || define( 'WP_ENVIRONMENT_TYPE', 'local' );
```

## Controlling DDEV settings management

- Remove `#ddev-generated` from a file to take manual control (DDEV stops overwriting it).
- `ddev config --disable-settings-management` — no auto CMS settings files.
- `ddev config --project-type=php` — generic PHP, no WP-specific nginx/settings.
- Unset `$IS_DDEV_PROJECT` to fence off DDEV-only code paths in production.

## `.ddev/.gitignore`

Created on `ddev start` when settings management is enabled. Do **not** commit it — it ignores DDEV-managed local artifacts. Commit only `.ddev/config.yaml` (and custom add-ons/providers you intentionally share).
