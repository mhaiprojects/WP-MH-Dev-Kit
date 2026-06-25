# DDEV Built-in Developer Tools

> Source: https://docs.ddev.com/en/stable/users/usage/developer-tools/ (saved 2025-06-25)

Run `ddev describe` to see project services and access URLs.

## Tools inside the web container

Accessible via `ddev exec` or `ddev ssh`:

- `mysql`, `psql` — database clients
- `wp` / `wp-cli` — WordPress CLI
- `composer`, `git`, `npm`, `yarn`, `node`
- `drush` (Drupal), and CMS-specific CLIs per project type

## Composer via DDEV

Always prefer host-side `ddev composer` over host-installed Composer (correct PHP/extensions):

```bash
ddev composer install
ddev composer require vendor/package
ddev composer create-project vendor/package [version]
```

`ddev composer create-project` requires an essentially empty directory.

Set alternate composer root:

```bash
ddev config --composer-root <relative-path>
```

Pin Composer version in `.ddev/config.yaml`:

```yaml
composer_version: "2.9.3"
```

Then `ddev restart --no-cache` or `ddev utility rebuild`.

**Note:** Global composer packages and `composer self-update` inside the container do **not** persist across restarts.

## Mailpit (email capture)

- Web UI: `https://<project>.ddev.site:8026` or `ddev mailpit`
- SMTP for apps: host `localhost`, port `1025`

## Database from host machine

```bash
ddev describe    # shows Host: localhost:<port>
ddev mysql       # easiest option
```

Pin host DB port:

```bash
ddev config --host-db-port=49156
```

## SSH keys in container

```bash
ddev auth ssh
```

Loads `~/.ssh` keys for private Composer repos, remote Drush/WP-CLI, etc.
