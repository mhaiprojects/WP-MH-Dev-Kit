# Adamat Divi code — built-in feature audit

Audit of custom adamat seed/MU-plugin code against Divi and WordPress built-ins.  
Principle: **use Divi/WP features first; custom PHP/CSS only when no native option exists.**

## Refactored (removed custom code)

| Was | Divi / WP built-in | Action |
|-----|-------------------|--------|
| `wp-content/mu-plugins/adamat-divi-hero.php` — regex sync of `background_image` on featured-image change | **Dynamic Content → Featured Image** on section Background → Image | **Removed** MU plugin |
| `sync_hero_background_from_featured_image()` + `register_hero_sync_hooks()` | Same | **Removed** from `AdamatDiviLayouts.php` |
| Static hero URL in `hero_photo_bg( $media_key )` | `@ET-DC@…post_featured_image…@` token + page Featured Image | **Replaced** with `hero_photo_bg_dynamic()` |
| Card CSS via `wp_update_custom_css_post()` (WP Customizer Additional CSS) | **Divi → Theme Options → General → Custom CSS** (`divi_custom_css`) | **Moved** to Divi Theme Options field |

Hero sections now use Divi’s native dynamic featured image. The seed script still calls `set_page_featured_image()` so each page gets the correct initial image from the media catalog.

## Already using Divi built-ins (keep)

| Code | Divi feature | Notes |
|------|--------------|-------|
| `hero_overlay()` | Background → Gradient, **Place Gradient Above Background Image** | `background_color_gradient_overlays_image="on"`, 70% black |
| `card_row_attrs()` | Row → Equalize Column Heights | `make_equal="on"` |
| `fade_in()`, `slide_in()`, `zoom_in()` | Design → Animation | Standard animation shortcode attrs |
| Scroll attrs on home hero | Design → Scroll Effects | `scroll_vertical_motion_*` |
| `global_section_ref()` / `seed_global_layouts()` | Global Modules + Divi Library | `global_module="ID"` in shortcodes; `et_pb_create_layout()` for seeding |
| `apply_builder_meta()` | Divi page meta | `_et_pb_use_builder`, full-width layout |
| `AdamatMediaAssets` + `set_post_thumbnail()` | WP Media Library + Featured Image | Feeds dynamic hero backgrounds |

## Custom code still justified (no Divi equivalent)

| Code | Why custom |
|------|------------|
| `apply_card_styles()` / `.adamat-card` CSS | Divi Blurb has no 16:9 image crop + flex equal-height card body in one setting; row `make_equal` alone does not fix image aspect ratio | CSS lives in **Divi → Theme Options → General → Custom CSS** (`divi_custom_css`) |
| `AdamatDiviLayouts` shortcode builders | Needed to programmatically seed layouts via WP-CLI; Divi has no “import JSON layout” in CLI without equivalent shortcodes |
| `AdamatMediaAssets` sideload | One-time seed of royalty-free images into Media Library |
| Inline brand colours in Text module HTML | Could move to Divi Theme Options / Global Colors over time; low priority for seed script |
| Service page retirement logic in seed | WP `post_status` / menu cleanup — not Divi |

## Optional future refactors (not done)

| Idea | Trade-off |
|------|-----------|
| **Theme Builder** header/footer templates | Better for production; seed script would need Theme Builder API or manual export |
| **Divi Global Colors** for `#0f2744`, `#0ea5e9` | Cleaner than inline styles; requires Theme Options or Divi 5 variables |
| **Post Title module** with `featured_placement="background"` | Only applies to post title module, not free-form hero copy — not a drop-in for adamat heroes |
| **Single Global Layout** for entire services block | Already partially done; could collapse three global sections into one library item |

## Parallax + gradient note

Divi may reduce gradient-over-image behaviour when parallax is enabled on the same section. The adamat hero uses both (`parallax="on"` + gradient overlay). Verify visually after changing featured images; if overlay weakens, set `parallax="off"` (as on the `hero-test` page) or use scroll effects instead.

## Verify after re-seed

```bash
ddev wp eval-file scripts/seed-adamat-site.php --url=https://adamat.cursor-wpdev2.ddev.site
```

1. Home hero shows featured image with dark overlay.
2. Change Featured Image on a page in wp-admin — hero updates **without** saving Divi layout (no MU plugin).
3. Service cards still equal height on Home, Services, About.
