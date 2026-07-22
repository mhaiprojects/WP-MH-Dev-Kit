# API Middleware

Authenticate and call third-party HTTP APIs; store responses in WordPress options or transients.

## Features

- Admin UI: **Integration → API Middleware**
- Auth types: none, bearer, basic, API key header
- Schedules: manual, hourly (WP-Cron), daily
- Manual **Run now** + REST trigger `POST /wp-json/mhdk/v1/api-connections/{id}/run`
- Demo connection: JSONPlaceholder `/posts/1` → option `mhdk_api_demo_posts`

## POC

Use **Run now** on the demo connection to verify HTTP pull without waiting for cron.
