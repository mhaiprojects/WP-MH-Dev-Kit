# MHDK-POC (Core module)

Reference feature inside the **Core** module — demonstrates patterns for all current and future core/platform features.

## Admin (via Core module)

- **MHDK Settings → MHDK-POC** — Settings API defaults
- **MHDK Settings → Content** — hub for Hospitality Menus and Web Links CPTs (`show_in_menu` → `mhdk-core-content`)

## Custom post types

| Slug | Label |
|------|-------|
| `mhdk_hosp_menu` | Hospitality Menu |
| `mhdk_web_link` | Web Link |

## Gutenberg blocks

Dynamic blocks (category **MHDK**): `mh-dev-kit/hospitality-menu`, `mh-dev-kit/web-links`, `mh-dev-kit/callout`

## Adding another core feature

1. Copy this feature folder as a starting point under `src/Modules/Core/Features/YourFeature/`
2. Implement `YourFeatureFeature` implementing `FeatureInterface`
3. Add the class to `CoreModule::features()`
4. Register any new admin subpages on `CoreModule` (not on the feature)

Features must not register admin menus directly.
