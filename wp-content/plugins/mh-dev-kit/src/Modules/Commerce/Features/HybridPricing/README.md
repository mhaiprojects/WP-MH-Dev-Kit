# Hybrid B2B/B2C Pricing

WooCommerce hybrid catalog and wholesale pricing powered by [Roots Acorn](https://roots.io/acorn/) service container.

## Features

- Hide prices from guests and/or B2C retail customers
- B2B role (`mhdk_b2b_customer`) with wholesale price list access
- Price list rules per **product** (fixed price) or **category** (discount %)
- When multiple rules match, the **lowest price** (highest effective discount) applies
- Mark products **B2B-only** to hide from B2C shop/search
- Admin: **Commerce → Hybrid B2B/B2C Pricing**

## Acorn

Services registered in `src/Providers/PluginServiceProvider.php`:

- `PriceResolver` — effective price calculation
- `CatalogVisibilityService` — B2B-only product filtering

Bootstrap: `src/Core/AcornBootstrap.php` (loaded on `plugins_loaded` priority 1).

## Assign B2B access

1. Users → edit user → Role: **B2B Customer**, or
2. Users → edit user → **MH Dev Kit — B2B pricing** → assign price lists
3. Custom price lists via user meta `mhdk_price_list_ids` (array of list IDs)

Default list ID: `wholesale-default`

## Demo seed (main site)

```bash
./scripts/seed-hybrid-pricing-demo.sh
```

Creates category/product price rules, a B2B demo user (`mhdk_b2b_demo` / `demo-b2b-pass`), and sample product flags.

## Production behaviour

- **Lowest price wins** when product and category rules overlap
- **Cart/checkout** applies B2B resolved prices via `woocommerce_before_calculate_totals`
- **Purchasing blocked** for guests/B2C when prices are hidden or product is B2B-only
- **REST API** strips or adjusts prices for non-B2B callers
