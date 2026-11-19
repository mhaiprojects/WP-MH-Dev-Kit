# Project documentation

Documentation for the **Cursor WP Dev 2** WordPress development environment.

## Contents

| Path | Description |
|------|-------------|
| [setup.md](setup.md) | Local environment URLs, credentials, and commands |
| [multisite.md](multisite.md) | Subdomain multisite registry and workflow |
| [industry-demo-sites.md](industry-demo-sites.md) | Cafe / factory sales / hospitality demo sites + follow-up issues |
| [changelog.md](changelog.md) | Dated log of requests and changes |
| [ddev-docs/](ddev-docs/) | Saved DDEV official documentation |
| [wordpress/](wordpress/) | Saved WordPress plugin developer handbook excerpts |
| [wordpress/royalty-free-assets.md](wordpress/royalty-free-assets.md) | Royalty-free image & icon sources for site content |
| [requests/](requests/) | Per-request notes |

## Quick start

```bash
# Requires Docker access (user in docker group)
./scripts/setup-wordpress.sh
ddev launch
```

See [setup.md](setup.md) for details.
