# Testing

PHPUnit tests for MH Dev Kit run inside DDEV against the project WordPress install using **wp-phpunit** (`WP_UnitTestCase`).

## Quick start

From the repository root:

```bash
./scripts/test-mh-dev-kit.sh
```

This will:

1. Install Composer dev dependencies in the plugin
2. Download the WordPress PHPUnit library (if missing)
3. Create the `wordpress_test` database
4. Run all tests and write reports to `test-results/`

## Manual run (inside DDEV)

```bash
ddev exec "bash wp-content/plugins/mh-dev-kit/tests/bin/install-wp-tests.sh db db db db latest skip"
ddev exec "cd wp-content/plugins/mh-dev-kit && composer install && vendor/bin/phpunit"
```

## Layout

| Path | Purpose |
|------|---------|
| `tests/Unit/` | Model and helper tests (fast, isolated) |
| `tests/Integration/` | CPT registration, REST permissions, seed logic |
| `tests/bootstrap.php` | Loads wp-phpunit and boots the plugin |
| `phpunit.xml.dist` | PHPUnit configuration and module test suites |

## Module test suites

Run a single module:

```bash
ddev exec "cd wp-content/plugins/mh-dev-kit && vendor/bin/phpunit --testsuite Core"
ddev exec "cd wp-content/plugins/mh-dev-kit && vendor/bin/phpunit --testsuite Commerce"
ddev exec "cd wp-content/plugins/mh-dev-kit && vendor/bin/phpunit --testsuite UserTools"
ddev exec "cd wp-content/plugins/mh-dev-kit && vendor/bin/phpunit --testsuite ContentTools"
ddev exec "cd wp-content/plugins/mh-dev-kit && vendor/bin/phpunit --testsuite SiteTools"
```

## Reports

After `./scripts/test-mh-dev-kit.sh`:

- `test-results/SUMMARY.md` — overall pass/fail counts per module
- `test-results/{module}/report.md` — per-module case list
- `test-results/mh-dev-kit/junit.xml` — machine-readable results
- `test-results/mh-dev-kit/output.log` — full PHPUnit output

## Requirements

- DDEV project `cursor-wpdev2` running
- WooCommerce active for Commerce integration tests (unit token tests work without it)
