# Order Menu

Bulk-order WooCommerce products from a restaurant-style menu table.

## Shortcode

```
[mhdk_order_menu category="shepparton-local-produce"]
[mhdk_order_menu menu="saturday-market"]
[mhdk_order_menu ids="19,20,21"]
[mhdk_order_menu slugs="goulburn-valley-pink-lady-apples-1kg" orderby="price" order="ASC" show_image="yes"]
```

Attributes (first match wins for source): `menu`, `ids`, `slugs`, `category`. Also: `orderby`, `order`, `show_image`.

## Features (plan items #1–#20)

- AJAX add to cart with live **Quantity in Cart** (#1)
- Line subtotals and order total (#2)
- Unit column parsed from product title (#3)
- Stock badges (#4)
- Clear all (#5)
- Loading state on Add Item (#6)
- Search bar (#7)
- Category section headers (#8)
- Sort via `orderby` / `order` (#9)
- Preserve qty after add (setting) (#10)
- Optional thumbnails (#11)
- Print stylesheet (#12)
- Reorder last order button (#13)
- MHDK Settings → Order Menu (#14)
- Shortcode generator (#15)
- Menu description override on products (#16)
- Saved menus CPT (#17)
- Gutenberg block `mh-dev-kit/order-menu` (#18)
- Seasonal availability dates on products (#19)
- Bulk order source meta on checkout (#20)

## Admin (via Commerce module)

**MHDK Settings → Order Menu** — global options and shortcode generator.

**MHDK Settings → Commerce Content → Saved Menus** — ordered product ID lists for `menu="slug"` shortcodes.

Product edit screen: **Order menu description**, **Available from/until**.

## PoC page

`/bulk-order-menu/` — auto-created with Shepparton category shortcode.
