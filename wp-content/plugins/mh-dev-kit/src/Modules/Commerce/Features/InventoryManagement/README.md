# Inventory Management

Track WooCommerce product stock per Store Locator location.

## Features

- Stock levels keyed by product + store ID
- Admin page: **Commerce → Inventory Management**
- Product meta box: stock per store
- Frontend list on product pages (stores with stock)
- REST: `GET /wp-json/mhdk/v1/inventory/{product_id}` and `/stores`

## Dependencies

Requires **Store Locator** stores (`StoreLocatorModel`) for valid location IDs.
