# Using the `ddev` Command

> Source: https://docs.ddev.com/en/stable/users/usage/cli/ (saved 2025-06-25)

## Help

```bash
ddev help
ddev help <command>
ddev <command> -h
```

Running `ddev` with no args opens the interactive TUI dashboard. Disable with `no_tui: true` in `~/.ddev/global_config.yaml` or `DDEV_NO_TUI=true`.

## Core workflow commands

| Command | Purpose |
|---------|---------|
| `ddev config` | Configure project type, docroot, PHP version, etc. |
| `ddev start` | Start project containers |
| `ddev stop` | Stop containers (data preserved) |
| `ddev restart` | Restart project |
| `ddev describe` / `ddev status` | Project URLs, DB credentials, services |
| `ddev launch [path]` | Open site in browser |
| `ddev list` | All projects and state |
| `ddev delete` | Remove DDEV project knowledge + DB (not your code) |
| `ddev poweroff` | Stop all DDEV resources |

## WordPress-specific

```bash
ddev wp <wp-cli-args>    # WP-CLI inside web container (wordpress / wp-bedrock types only)
```

Upload directory for `ddev import-files`: `wp-content/uploads` (standard WordPress).

## Database

```bash
ddev mysql                 # MySQL client
ddev import-db             # Import SQL / .sql.gz
ddev export-db             # Export to stdout or --file=
ddev snapshot              # Fast DB snapshot
ddev snapshot restore <name>
```

Examples:

```bash
ddev export-db --file=/tmp/db.sql.gz
ddev export-db --gzip=false --file=/tmp/db.sql
```

## Files

```bash
ddev import-files --source=/path/to/files.tgz
ddev import-files --target=../private --source=/tmp/files.tgz
```

## Container access

```bash
ddev exec <command>        # Run command in web container
ddev exec --service db mysql
ddev ssh                   # Interactive shell in web container
ddev logs                  # Web server logs
ddev logs -s db            # Database logs
ddev logs -f               # Follow logs
```

## Other bundled tools

```bash
ddev composer install
ddev xdebug on|off|status|toggle
ddev mailpit               # Open Mailpit (captured email)
ddev auth ssh              # Load host SSH keys into agent container
```

## Post-import DB hook example (WordPress URL replace)

In `.ddev/config.yaml`:

```yaml
hooks:
  post-import-db:
    - exec: wp search-replace https://www.myproductionsite.com http://mydevsite.ddev.site
```
