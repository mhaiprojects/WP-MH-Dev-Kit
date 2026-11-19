# My Local Ad Page

Suburb-specific landing pages with auto-generated URLs and REST API for AI agents.

## Public URL pattern

Configurable under **MHDK Settings → Content Tools → — Local Ad Pages**.

| Pattern | Example |
|---------|---------|
| **Dash-separated (default)** | `/local-au-vic-shepparton-3630/` |
| **Slash-separated** | `/local/au/vic/shepparton-3630/` |

Example (seed): `/local-au-vic-shepparton-3630/`

## Admin

**MHDK Settings → Content Tools** — module hub and feature links.

Set country, state, suburb, postcode, headline, and body. Featured image via post thumbnail.

## REST API

Base: `/wp-json/mhdk/v1/local-ads`

Requires authenticated user with `edit_posts` capability. Pass `X-WP-Nonce` header from `wp_create_nonce('wp_rest')`.

| Method | Route | Description |
|--------|-------|-------------|
| GET | `/local-ads` | List ads |
| POST | `/local-ads` | Create ad |
| GET | `/local-ads/{id}` | Get ad |
| PUT/PATCH | `/local-ads/{id}` | Update ad |
| DELETE | `/local-ads/{id}` | Trash ad |
| POST | `/local-ads/{id}/upload` | Upload featured image (`file` multipart) |

### Create payload example

```json
{
  "title": "Shepparton Local Produce",
  "content": "Main page content…",
  "headline": "Fresh from the Goulburn Valley",
  "body": "Ad body HTML…",
  "status": "publish",
  "location": {
    "country": "au",
    "state": "vic",
    "suburb": "shepparton",
    "postcode": "3630"
  }
}
```

## Seed data

On first load, creates published Shepparton VIC 3630 example if none exists.

## Auto-approved decisions

- Custom rewrite (not WP native permalink) for predictable AI-friendly URLs.
- REST scoped to `edit_posts` (not public read).
- Standalone public template (minimal, not theme-dependent).

## Remaining decisions

- Public REST read for published ads (currently auth required for list).
- Application passwords vs OAuth for external AI agents.
- Duplicate location guard (multiple ads same suburb).
- Sitemap / SEO meta integration.
