# Royalty-free images & icons for website content

Agents and developers should prefer **downloaded, self-hosted** media (WordPress Media Library) over hotlinking. Record source and license for each asset.

## Approved photo sources

| Source | URL | License | Best for |
|--------|-----|---------|----------|
| Unsplash | https://unsplash.com/ | [Unsplash License](https://unsplash.com/license) — free commercial use | Hero, team, tech, lifestyle |
| Pexels | https://www.pexels.com/ | Pexels License — free commercial use | General web imagery |
| Pixabay | https://pixabay.com/ | Pixabay License | Photos, vectors, video |
| Burst (Shopify) | https://www.shopify.com/stock-photos | Free commercial use | eCommerce, products |
| Openverse | https://openverse.org/ | Filter CC0 / CC BY | Aggregated search |

## Approved icon sources

| Source | URL | License | Best for |
|--------|-----|---------|----------|
| Material Symbols | https://fonts.google.com/icons | Apache 2.0 | UI icons |
| Phosphor Icons | https://phosphoricons.com/ | MIT | Modern UI sets |
| Heroicons | https://heroicons.com/ | MIT | Tailwind-style UI |
| Tabler Icons | https://tabler.io/icons | MIT | Large consistent sets |
| Lucide | https://lucide.dev/ | ISC | Feather-style icons |
| Divi icon font | Theme bundled | Divi license | Blurbs, buttons in Divi Builder |
| SVG Repo (CC0 filter) | https://www.svgrepo.com/ | Varies — filter CC0/MIT | SVG search |

## Agent workflow

When adding or refreshing site visuals:

1. Pick assets from the tables above (never random Google Images).
2. Prefer **CC0, MIT, Apache 2.0, or Unsplash/Pexels** licenses for client work.
3. Download into the subsite Media Library via `AdamatMediaAssets::seed()` (see `scripts/lib/AdamatMediaAssets.php`).
4. Store attribution in attachment description (`post_content`) and alt text in `_wp_attachment_image_alt`.
5. Use in Divi via `et_pb_image`, blurb `image` (with `use_icon="off"`), or section `background_image`.
6. Prefer WebP/JPEG for photos; SVG for icons when not using Divi’s icon font.

## adamat site asset map

Defined in `scripts/lib/AdamatMediaAssets.php`:

| Key | Use | Source |
|-----|-----|--------|
| `hero` | Home hero background / visual | Unsplash |
| `about-team` | About page | Unsplash |
| `contact` | Contact page | Unsplash |
| `service-*` | Services blurbs (6 keys) | Unsplash |
| `project-*` | Projects showcase cards (6 keys) | Unsplash |

Re-seed after changing the catalog:

```bash
ddev wp eval-file scripts/seed-adamat-site.php --url=https://adamat.cursor-wpdev2.ddev.site
```

## Recommended image sizes (Divi)

| Placement | Target width | Notes |
|-----------|--------------|-------|
| Hero background | 1920px | Dark brand overlay for text contrast |
| Half-column image | 1200px | About / contact side images |
| Service / project card | 800px | Blurb top image |
| Open Graph (future) | 1200×630 | Social preview |

## What to avoid

- Hotlinking production URLs from stock sites (download and host locally).
- Icons from Flaticon/Icons8 without required attribution (unless licensed).
- Images with visible trademarks or unrelated brands in client mockups.
- Committing large binaries to git — uploads stay in `wp-content/uploads/` (ignored).
