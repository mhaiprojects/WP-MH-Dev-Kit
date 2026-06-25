# DDEV Commands Reference (project-relevant subset)

> Source: https://docs.ddev.com/en/stable/users/usage/commands/ (saved 2025-06-25)

Full reference: `ddev help` or the URL above.

## Global flags (all commands)

- `-h`, `--help` — command help
- `-j`, `--json-output` — JSON output

## `ddev config`

Create/modify `.ddev/config.yaml`. Existing values are preserved unless overridden by flags.

```bash
ddev config                                          # interactive
ddev config --auto                                   # accept defaults
ddev config --update                                 # autodetect from existing files
ddev config --project-type=wordpress
ddev config --project-name=cursor-wpdev2
ddev config --docroot=""                             # WP at project root
ddev config --php-version=8.3
ddev config --web-environment-add="WP_ENVIRONMENT_TYPE=local"
ddev config --disable-settings-management
ddev config --host-db-port=49156
ddev config global --instrumentation-opt-in=false
```

Key flags: `--project-type`, `--project-name`, `--docroot`, `--php-version`, `--database`, `--webserver-type`, `--web-environment`, `--web-environment-add`, `--upload-dirs`, `--composer-root`, `--disable-settings-management`.

## `ddev start`

```bash
ddev start
ddev start -y              # skip confirmation
ddev start --no-cache       # rebuild image layers
ddev start --all            # all projects
```

## `ddev stop`

Non-destructive by default (containers removed, DB preserved).

```bash
ddev stop
ddev stop --remove-data     # destroys imported DB
ddev stop --snapshot        # snapshot before stop
ddev stop --all
```

## `ddev restart`

Restarts project; use after config changes.

```bash
ddev restart
ddev restart --no-cache
```

## `ddev describe`

Aliases: `status`, `st`, `desc`.

```bash
ddev describe
ddev describe cursor-wpdev2
```

## `ddev launch`

```bash
ddev launch
ddev launch wp-admin/
```

## `ddev wp`

Available only for `project-type: wordpress` or `wp-bedrock`.

```bash
ddev wp core download
ddev wp core install --url='$DDEV_PRIMARY_URL' --title='Title' \
  --admin_user=admin --admin_password=admin --admin_email=admin@example.com
ddev wp plugin list
ddev wp plugin activate mh-dev-kit
ddev wp cache flush
ddev wp search-replace 'https://old.example' 'https://new.ddev.site'
```

Use single quotes around `'$DDEV_PRIMARY_URL'` so expansion happens **inside** the container.

## `ddev import-db` / `ddev export-db`

```bash
ddev import-db --file=db.sql.gz
ddev export-db --file=db.sql.gz
ddev export-db --gzip=false --file=db.sql
```

## `ddev import-files`

WordPress default upload dir: `wp-content/uploads`.

```bash
ddev import-files --source=/path/to/files.tgz
```

## `ddev exec` / `ddev ssh`

```bash
ddev exec ls
ddev exec --service db mysql
ddev exec --dir /usr/bin ls
ddev ssh
ddev ssh --service db
```

## `ddev version`

```bash
ddev version
ddev --version    # DDEV version only
```

Requires Docker daemon running.
