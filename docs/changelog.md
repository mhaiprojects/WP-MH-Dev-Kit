# Changelog

All notable requests and project changes.

## 2025-06-25

### Initial setup (user request)

- Bootstrap WordPress with DDEV (`cursor-wpdev2`)
- Create `docs/` for requests and changes
- Initialize git: ignore WP core, track `docs/` and custom plugin `mh-dev-kit`
- Save local copies of DDEV and WordPress plugin docs under `docs/ddev-docs/` and `docs/wordpress/`
- Scaffold `mh-dev-kit` plugin (activation hooks, admin notice)
- Add `scripts/setup-wordpress.sh` for reproducible bootstrap

### WordPress bootstrap completed

- Ran `./scripts/setup-wordpress.sh` via `sg docker` (group membership active, shell not refreshed)
- Site live at https://cursor-wpdev2.ddev.site (HTTP 200)
- `mh-dev-kit` plugin active
- Setup script updated to auto-reexec via `sg docker` when needed
