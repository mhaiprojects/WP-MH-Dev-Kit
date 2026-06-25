# DDEV Configuration (`config.yaml`)

> Source: https://docs.ddev.com/en/stable/users/configuration/config/ (saved 2025-06-25)

Configuration lives in `.ddev/config.yaml`. Change via `ddev config` flags or direct YAML edit, then `ddev restart`.

## Key settings for this WordPress project

| Setting | This project | Notes |
|---------|--------------|-------|
| `name` | `cursor-wpdev2` | URL: `https://cursor-wpdev2.ddev.site` |
| `type` | `wordpress` | Enables `ddev wp`, WP nginx config, settings management |
| `docroot` | `""` | WordPress at project root |
| `php_version` | `8.3` | Set via `ddev config --php-version=8.3` |
| `web_environment` | `WP_ENVIRONMENT_TYPE=local` | Optional, via `--web-environment-add` |

## Common config commands

```bash
ddev config --show-config-location
ddev config --project-type=wordpress --project-name=cursor-wpdev2
ddev config --php-version=8.3
ddev config --database=mariadb:10.11
ddev config --webserver-type=nginx-fpm
ddev config --upload-dirs=wp-content/uploads
ddev config --disable-settings-management
```

## Settings management

When `disable_settings_management: false` (default for CMS types), DDEV on `ddev start`:

- Creates/updates `wp-config-ddev.php`
- Manages database credentials for local dev

Files with `#ddev-generated` are overwritten by DDEV until you remove that marker.

## Hooks (`.ddev/config.yaml`)

```yaml
hooks:
  post-start:
    - exec: "wp plugin activate mh-dev-kit"
  post-import-db:
    - exec: "wp search-replace https://production.example https://cursor-wpdev2.ddev.site"
```

## What to commit to git

- **Do commit:** `.ddev/config.yaml`, custom add-ons/providers you intend to share
- **Do not commit:** `.ddev/.gitignore`, `.ddev/db_snapshots/`, generated local state

See full option list at https://docs.ddev.com/en/stable/users/configuration/config/
