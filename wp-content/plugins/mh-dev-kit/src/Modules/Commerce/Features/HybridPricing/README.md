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
2. Assign custom price lists via user meta `mhdk_price_list_ids` (array of list IDs)

Default list ID: `wholesale-default`
