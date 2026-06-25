# Foundation PoC

First MH Dev Kit feature module. Delivers:

- **MH Dev Kit** admin menu + **Foundation** settings subpage
- **MH Dev Kit Content** menu with CPTs: `mhdk_hosp_menu`, `mhdk_web_link`
- **3 Gutenberg blocks:** `mh-dev-kit/hospitality-menu`, `mh-dev-kit/web-links`, `mh-dev-kit/callout`

## MVP layout

| Folder | Role |
|--------|------|
| `Models/` | Settings, CPT queries, block data |
| `Presenters/` | WordPress hooks, asset enqueue |
| `Views/` | Admin pages, templates, block partials |
| `Assets/` | CSS, JS, `blocks/*/block.json` |

## Composer

From project root:

```bash
ddev exec "cd wp-content/plugins/mh-dev-kit && composer install"
```

Or: `../../scripts/composer-mh-dev-kit.sh install`
