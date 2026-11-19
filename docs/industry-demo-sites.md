# Industry demo sites

Three Divi + WooCommerce subsites for local industry demos. Multisite remains subdomain-based (`*.cursor-wpdev2.ddev.site`).

## Sites

| Site | URL | Seed |
|------|-----|------|
| Riverbank Cafe & Takeaway | https://cafe-takeaway.cursor-wpdev2.ddev.site | `scripts/seed-cafe-takeaway-site.php` |
| Goulburn Factory Sales | https://factory-sales.cursor-wpdev2.ddev.site | `scripts/seed-factory-sales-site.php` |
| GV Hospitality Supplies | https://hospitality-supplies.cursor-wpdev2.ddev.site | `scripts/seed-hospitality-supplies-site.php` |

```bash
./scripts/setup-industry-demo-sites.sh
ddev wp eval-file scripts/verify-industry-demo-pricing.php --url=https://factory-sales.cursor-wpdev2.ddev.site
```

PoC walkthrough: https://factory-sales.cursor-wpdev2.ddev.site/demo-guide/

## Pricing (verified)

TempApp (`wp-content/plugins/temp-app/`):

| Feature | How to demo | Expected |
|---------|-------------|----------|
| Qty packages | Tomato Sauce Value Pack `FS-SAUCE-VP` qty 1/2/3/5 | $10 / $15 / $25 / $32 |
| Mix Any 3 | Apple + Orange + Pasta | cart total **$7** (fee) |
| Mix Any 4 | Spaghetti + Tomatoes + Corn + Chickpeas | cart total **$5** |
| No double stack | Beans qty packages have **no** mix tag; tagged SKUs have **no** qty breaks | |

```bash
ddev wp eval-file scripts/verify-industry-demo-pricing.php --url=https://factory-sales.cursor-wpdev2.ddev.site
ddev wp eval-file scripts/verify-qty-packages-matrix.php --url=https://factory-sales.cursor-wpdev2.ddev.site
```

Qty matrix: **231 checks** (all package products × qty range + set_quantity + merge + edge break shapes).  
Browser: product qty preview + classic cart update 2→$15, 3→$25 confirmed.

### If the store looks “broken” in the browser

WooCommerce 9+ **Coming soon** mode (`woocommerce_coming_soon=yes`) was enabled on factory-sales and hid the real product UI behind “Great things are on the horizon”. Seeds now force it **off**. If it comes back:

```bash
ddev wp option update woocommerce_coming_soon no --url=https://factory-sales.cursor-wpdev2.ddev.site
```

## PoC readiness checklist

- [x] Multisite intact (blogs 4–6)
- [x] Divi pages render (not raw shortcodes)
- [x] Shop + add to cart on all three sites
- [x] Cafe menu lists products
- [x] Hospitality catalogue by category
- [x] Guest checkout enabled; shipping disabled (local pickup / pay-in-store style)
- [x] COD enabled
- [x] Package packing + mix & match verified
- [x] Demo guide page on factory-sales

## Remaining (non-blocking) follow-ups

1. Replace picsum placeholders with real photos.
2. Polish Divi layouts in Visual Builder (dark-section text contrast).
3. Add product featured images.
4. Replace TempApp with a maintained dynamic-pricing plugin before client go-live.
5. Hospitality “Quote” is still a contact form — not a full quote-cart system.
6. AU GST tax classes / shipping zones if you need production-like checkout.
7. External brand names are illustrative only — fictionalise further before sharing publicly.
