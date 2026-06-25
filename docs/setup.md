# Local setup — Cursor WP Dev 2

## Prerequisites

- [DDEV](https://docs.ddev.com/) installed (`ddev` on PATH)
- Docker running and **your user in the `docker` group**

```bash
sudo usermod -aG docker $USER
# Log out and back in (or reboot), then:
docker info          # Docker daemon reachable by your user
ddev --version       # DDEV binary installed (no Docker required)
ddev version         # DDEV + Docker stack OK (requires Docker access)
```

If you are already in the `docker` group but `docker info` fails in the current terminal, either log out/in or run commands via `sg docker -c "..."`. The setup script handles this automatically.

## First-time bootstrap

From the project root:

```bash
chmod +x scripts/setup-wordpress.sh
./scripts/setup-wordpress.sh
```

This runs the official DDEV WordPress flow (see [ddev-docs/wordpress-quickstart.md](ddev-docs/wordpress-quickstart.md)):

1. `ddev config` — project `cursor-wpdev2`, type `wordpress`
2. `ddev start`
3. `ddev wp core download`
4. `ddev wp core install` with `$DDEV_PRIMARY_URL`
5. `ddev wp plugin activate mh-dev-kit`

## URLs

| Service | URL |
|---------|-----|
| Site | https://cursor-wpdev2.ddev.site |
| Admin | https://cursor-wpdev2.ddev.site/wp-admin/ |
| Mailpit | https://cursor-wpdev2.ddev.site:8026 |

## Default admin (local only)

- **User:** `admin`
- **Password:** `admin`
- **Email:** `admin@example.com`

## Daily commands

```bash
ddev start
ddev stop
ddev describe          # URLs, DB port, status
ddev launch            # open site
ddev launch wp-admin/
ddev wp plugin list
ddev wp plugin activate mh-dev-kit
ddev mysql
ddev logs -f
```

## Git tracking policy

Only custom code and docs are committed. WordPress core, uploads, and third-party plugins stay local. See root `.gitignore`.

## Custom plugin

- **Slug:** `mh-dev-kit`
- **Path:** `wp-content/plugins/mh-dev-kit/`
- **Composer:** `./scripts/composer-mh-dev-kit.sh install` (after clone or pull)
- **Architecture:** [wordpress/mh-dev-kit-architecture.md](wordpress/mh-dev-kit-architecture.md)

## Reference docs (local copies)

- DDEV: [ddev-docs/README.md](ddev-docs/README.md)
- WordPress plugins: [wordpress/README.md](wordpress/README.md)
