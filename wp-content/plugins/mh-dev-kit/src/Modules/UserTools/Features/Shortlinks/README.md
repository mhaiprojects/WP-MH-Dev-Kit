# Shortlinks Manager

Linktree-style link pages per user at `/l/{slug}/`.

## Admin

**User Tools → Link Pages** — set public slug, bio, and link rows.

Public URL pattern: `{site}/l/your-slug/`

## Auto-approved decisions

- Slug stored in post meta (`mhdk_link_slug`), unique lookup on publish.
- Standalone minimal HTML template (not theme single.php).
- Links open in new tab with `noopener`.

## Remaining decisions

- Enforce unique slugs across all users (currently first match wins).
- Theme integration vs standalone template toggle.
- Click analytics per link.
- Avatar / profile image support.
