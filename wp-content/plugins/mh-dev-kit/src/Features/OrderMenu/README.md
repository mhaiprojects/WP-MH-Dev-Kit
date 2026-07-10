# Order Menu

Bulk-order WooCommerce products from a restaurant-style menu table.

## Shortcode

```
[mhdk_order_menu category="shepparton-local-produce"]
[mhdk_order_menu ids="19,20,21"]
[mhdk_order_menu slugs="goulburn-valley-pink-lady-apples-1kg,cherry-tomatoes-500g"]
```

Attributes (first match wins): `ids`, then `slugs`, then `category`. Omit all to list up to 100 purchasable products.

## Security

- WordPress nonce on form submit
- Quantities sanitized (1–99 per line)
- Only products matching the shortcode filter can be added to cart
- Redirect via `wp_safe_redirect`

## PoC page

Auto-created at `/bulk-order-menu/` on first load with the Shepparton category shortcode.

Each row includes **− / + / Clear** quantity controls, an **Add Item** button (single-line add), and a **Quantity in Cart** readout. After adding items, the user stays on the order menu page (WooCommerce success notice shown).
