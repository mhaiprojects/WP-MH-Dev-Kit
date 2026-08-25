# Divi block documentation (local mirror)

Offline copies of [Elegant Themes Divi documentation](https://www.elegantthemes.com/documentation/divi/) for modules and features used in site seeds.

## Live demo subsite

**URL:** [https://divi-demo.cursor-wpdev2.ddev.site/](https://divi-demo.cursor-wpdev2.ddev.site/)

Subdomain multisite showcase with every standard Divi 4 module, row/column grid layout, and advanced builder settings (animation, scroll, gradients, borders).

| Setup | Command |
|-------|---------|
| Create subsite + seed pages | `./scripts/setup-divi-demo-multisite.sh` |
| Reseed pages only | `ddev wp eval-file scripts/seed-divi-demo-site.php --url=https://divi-demo.cursor-wpdev2.ddev.site` |
| Regenerate module catalog | `ddev exec php scripts/extract-divi-module-reference.php` |

**Agent skill:** `.cursor/skills/divi-builder/SKILL.md` (layouts, grids, module slugs, advanced settings).

**Module catalog (JSON):** [module-catalog.json](./module-catalog.json) — 55 standard + 25 WooCommerce modules extracted from theme PHP.

## Built-in first workflow

Before adding PHP, MU plugins, or custom CSS in `AdamatDiviLayouts.php`:

1. **Check this folder** — Divi often has a native option (Dynamic Content, Global Modules, row equalisation, gradient overlays, scroll effects, animations).
2. **Prefer WordPress features** — Featured images, menus, media library, page templates, Theme Builder.
3. **Use the Visual Builder** to confirm a setting exists, then mirror its shortcode attributes in the seed helpers.
4. **Custom code only when Divi has no equivalent** — e.g. shared 16:9 blurb card CSS.

## Index

| File | Source | Relevant to adamat |
|------|--------|-------------------|
| [dynamic-content-options.html](./dynamic-content-options.html) | [ET docs](https://www.elegantthemes.com/documentation/divi/divi-dynamic-content-options/) | Hero backgrounds via Featured Image |
| [sections.html](./sections.html) | [ET docs](https://www.elegantthemes.com/documentation/divi/sections/) | Hero sections, padding, parallax |
| [background-options.html](./background-options.html) | [ET docs](https://www.elegantthemes.com/documentation/divi/background-options/) | Gradient overlay on background image |
| [rows.html](./rows.html) | [ET docs](https://www.elegantthemes.com/documentation/divi/rows/) | `make_equal="on"` for equal-height columns |
| [blurb.html](./blurb.html) | [ET docs](https://www.elegantthemes.com/documentation/divi/blurb/) | Service cards |
| [global-modules.html](./global-modules.html) | [ET docs](https://www.elegantthemes.com/documentation/divi/global-modules/) | Shared service card rows |
| [scroll-effects.html](./scroll-effects.html) | [ET docs](https://www.elegantthemes.com/documentation/divi/scroll-effects/) | Hero parallax / vertical motion |
| [animation.html](./animation.html) | [ET docs](https://www.elegantthemes.com/documentation/divi/animation/) | Fade / slide on text and cards |
| [theme-options.html](./theme-options.html) | [ET docs](https://www.elegantthemes.com/documentation/divi/theme-options/) | Custom CSS field, brand colours |

## Dynamic Content shortcode token

Divi stores dynamic values as base64 JSON wrapped in `@ET-DC@…@`. For **Featured Image** on a section background:

```
background_image="@ET-DC@eyJkeW5hbWljIjp0cnVlLCJjb250ZW50IjoicG9zdF9mZWF0dXJlZF9pbWFnZSIsInNldHRpbmdzIjp7ImVuYWJsZV9odG1sIjoib2ZmIn19@"
```

Decoded payload: `{"dynamic":true,"content":"post_featured_image","settings":{"enable_html":"off"}}`

In PHP (when Divi is loaded):

```php
et_builder_serialize_dynamic_content( true, 'post_featured_image', array( 'enable_html' => 'off' ) );
```

Set the page **Featured Image** in WordPress; Divi resolves the hero background at render time — no sync hooks required.

## Custom CSS location

Service card CSS (`.adamat-card`) is written to **Divi → Theme Options → General → Custom CSS** via the `divi_custom_css` option when you run the adamat seed script. Edit it there in wp-admin.

## Refreshing these files

```bash
cd docs/divi
for slug in divi-dynamic-content-options sections rows background-options global-modules scroll-effects animation blurb theme-options; do
  curl -sL "https://www.elegantthemes.com/documentation/divi/${slug}/" -o "${slug}.html"
done
```
