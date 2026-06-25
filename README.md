# Cursor WP Dev 2

Local WordPress development environment powered by [DDEV](https://docs.ddev.com/).

## Quick start

```bash
# One-time: ensure Docker works (see docs/setup.md)
./scripts/setup-wordpress.sh
ddev launch
```

- **Site:** https://cursor-wpdev2.ddev.site
- **Admin:** https://cursor-wpdev2.ddev.site/wp-admin/ (`admin` / `admin`)

## What's in git

- `docs/` — project documentation and saved reference docs
- `.ddev/config.yaml` — DDEV project configuration
- `wp-content/plugins/mh-dev-kit/` — custom plugin

WordPress core and other `wp-content` assets are **not** tracked. After clone, run `./scripts/setup-wordpress.sh` to download core and install.

## Documentation

See [docs/README.md](docs/README.md).
