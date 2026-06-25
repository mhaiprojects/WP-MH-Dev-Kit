# Plugin Header Requirements

> Source: https://developer.wordpress.org/plugins/plugin-basics/header-requirements/ (saved 2025-06-25)

## Minimum required field

```php
/*
 * Plugin Name: YOUR PLUGIN NAME
 */
```

## Common header fields

| Field | Purpose |
|-------|---------|
| Plugin Name | **Required** — shown in Plugins admin |
| Plugin URI | Plugin homepage (unique URL, not wordpress.org) |
| Description | Short description (<140 chars) |
| Version | Semver, compared with `version_compare()` |
| Requires at least | Minimum WP version |
| Requires PHP | Minimum PHP version |
| Author / Author URI | Author info |
| License / License URI | e.g. GPL v2 or later |
| Text Domain / Domain Path | i18n |
| Requires Plugins | Comma-separated wp.org plugin slugs (dependencies) |
| Update URI | Prevents accidental overwrite from wp.org |

## Example (used as basis for mh-dev-kit)

```php
/**
 * Plugin Name:       MH Dev Kit
 * Description:       Development utilities and scaffolding for MH WordPress projects.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            MH
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mh-dev-kit
 */
```

## Version numbering

WordPress uses PHP `version_compare()`. Example: `1.02` > `1.1`.
