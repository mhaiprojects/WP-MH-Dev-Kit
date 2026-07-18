# Backend Restrict Origin

Blocks wp-admin requests when neither `Referer` nor `Origin` matches this site (or configured allowed hosts).

## Settings

**MHDK Settings → Restrict Origin** (`mhdk-settings-restrict-origin`)

| Setting | Default | Description |
|---------|---------|-------------|
| Enable | off | Master toggle |
| Allow same site host | on | Match site home URL host |
| Additional allowed hosts | empty | One per line, e.g. staging.example.com |
| Bypass admin-ajax | on | Skip check for AJAX |
| Bypass wp-cron | on | Skip check for cron |
| Bypass REST | off | Skip REST in admin context |

## Auto-approved decisions

- Runs on `admin_init` priority 1 after login bootstrap.
- Uses 403 `wp_die` rather than silent redirect.
- Empty Referer **and** empty Origin → blocked when enabled.

## Remaining decisions

- Whether to allow first-load bookmarked admin URLs (currently blocked).
- Log blocked attempts to a custom post type or file.
- WP-CLI / cron external triggers may need host allowlisting.

## Edge cases

- Browsers with strict Referer privacy may block legitimate admin navigation.
- OAuth / SSO return URLs may need additional allowed hosts.
- Test with your admin workflow before enabling in production.
