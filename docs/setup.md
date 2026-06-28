# Local setup — Cursor WP Dev 2

## Prerequisites

- [DDEV](https://docs.ddev.com/) installed (`ddev` on PATH)
- Docker running and **your user in the `docker` group**

```bash
sudo usermod -aG docker $USER
# Log out and back in (or reboot), then:
docker info          # Docker daemon reachable by your user
ddev --version       # DDEV binary installed (no Docker required)
ddev version         # DDEV + Docker stack OK (requires Docker access)
```

If you are already in the `docker` group but `docker info` fails in the current terminal, either log out/in or run commands via `sg docker -c "..."`. The setup script handles this automatically.

## First-time bootstrap

From the project root:

```bash
chmod +x scripts/setup-wordpress.sh
./scripts/setup-wordpress.sh
```

This runs the official DDEV WordPress flow (see [ddev-docs/wordpress-quickstart.md](ddev-docs/wordpress-quickstart.md)):

1. `ddev config` — project `cursor-wpdev2`, type `wordpress`
2. `ddev start`
3. `ddev wp core download`
4. `ddev wp core install` with `$DDEV_PRIMARY_URL`
5. `ddev wp plugin activate mh-dev-kit`
6. `./scripts/setup-woocommerce.sh` — WooCommerce, Storefront theme, COD-only checkout

## E-commerce setup

WooCommerce and Storefront are **not in git** (see git tracking policy). Install or refresh the shop with:

```bash
chmod +x scripts/setup-woocommerce.sh
./scripts/setup-woocommerce.sh
```

This script:

- Installs and activates **WooCommerce** and the **Storefront** theme
- Creates shop, cart, checkout, and my-account pages
- Enables **Cash on delivery** as the only payment method (BACS and Cheque disabled)
- Sets baseline store options (USD, US address defaults, catalog live but empty)
- Keeps **mh-dev-kit** active

### Store URLs

| Page | URL |
|------|-----|
| Shop | https://cursor-wpdev2.ddev.site/shop/ |
| Cart | https://cursor-wpdev2.ddev.site/cart/ |
| Checkout | https://cursor-wpdev2.ddev.site/checkout/ |
| My account | https://cursor-wpdev2.ddev.site/my-account/ |

### Post-setup checklist

In **WooCommerce → Settings**:

1. **General** — confirm store address and currency
2. **Products** — review catalog/display options
3. **Shipping** — add zones/rates if needed (COD works without online payment)
4. **Payments** — confirm only **Cash on delivery** is enabled

Add products under **Products → Add new** when ready.

### Shepparton local produce catalog

Seed 10 in-demand Goulburn Valley products (with vendor details in descriptions):

```bash
chmod +x scripts/seed-shepparton-products.sh
./scripts/seed-shepparton-products.sh
```

Sets store region to Shepparton VIC / AUD. Safe to re-run (skips if products already seeded).

### Future payment gateways

Install later under **Plugins → Add new** (do not enable until configured):

- [WooCommerce Stripe](https://wordpress.org/plugins/woocommerce-gateway-stripe/)
- [WooCommerce PayPal Payments](https://wordpress.org/plugins/woocommerce-paypal-payments/)

Enable new gateways under **WooCommerce → Settings → Payments**. Keep **Cash on delivery** enabled as a fallback if desired.

## URLs

| Service | URL |
|---------|-----|
| Site | https://cursor-wpdev2.ddev.site |
| Admin | https://cursor-wpdev2.ddev.site/wp-admin/ |
| Mailpit | https://cursor-wpdev2.ddev.site:8026 |

## Default admin (local only)

- **User:** `admin`
- **Password:** `admin`
- **Email:** `admin@example.com`

## Daily commands

```bash
ddev start
ddev stop
ddev describe          # URLs, DB port, status
ddev launch            # open site
ddev launch wp-admin/
ddev wp plugin list
ddev wp plugin activate mh-dev-kit
ddev mysql
ddev logs -f
```

## Git tracking policy

Only custom code and docs are committed. WordPress core, uploads, and third-party plugins stay local. See root `.gitignore`.

## Custom plugin

- **Slug:** `mh-dev-kit`
- **Path:** `wp-content/plugins/mh-dev-kit/`
- **Composer:** `./scripts/composer-mh-dev-kit.sh install` (after clone or pull)
- **Architecture:** [wordpress/mh-dev-kit-architecture.md](wordpress/mh-dev-kit-architecture.md)

## Reference docs (local copies)

- DDEV: [ddev-docs/README.md](ddev-docs/README.md)
- WordPress plugins: [wordpress/README.md](wordpress/README.md)
