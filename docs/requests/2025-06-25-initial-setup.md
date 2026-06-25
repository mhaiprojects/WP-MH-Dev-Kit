# Request: Initial WordPress + DDev setup

**Date:** 2025-06-25

## User request

1. Use DDev to set up a working WordPress installation on this PC
2. Create a `docs` folder to document requests, changes, etc.
3. Initiate a repo ignoring default WP files; commit only custom code and `docs/`

## Decisions

| Item | Choice |
|------|--------|
| DDev project name | `cursor-wpdev2` |
| Custom code | Plugin `mh-dev-kit` |
| WP install method | DDEV WP-CLI quickstart (not Bedrock) |
| Local WP environment | `WP_ENVIRONMENT_TYPE=local` via DDev `web_environment` |

## Implementation notes

- Official DDEV commands used verbatim from docs (no assumed flags)
- Docker must be running; user needs `docker` group membership for `ddev` without sudo
- `.gitignore` uses deny-all / whitelist pattern for `mh-dev-kit` only

## Follow-up

- Run `./scripts/setup-wordpress.sh` after Docker access is confirmed
- Extend `.gitignore` whitelist when adding more custom themes/plugins
