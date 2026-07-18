# Family Tree Builder

Interactive family tree with Vue.js (CDN). Data stored as JSON on user-scoped CPT `mhdk_family_tree`.

## Shortcode

```
[mhdk_family_tree id="123"]
[mhdk_family_tree id="123" editable="1"]
```

`editable="1"` enables add-child and save for the post author (requires login).

## REST API

```
POST /wp-json/mhdk/v1/family-tree/{id}
Body: { "tree": { "root": { ... } } }
Header: X-WP-Nonce: {wp_rest nonce}
```

Implemented in FamilyTreeRestPresenter (registered via feature).

## Auto-approved decisions

- Vue 3 from jsDelivr CDN (pre-approved Vue.js).
- Tree stored as JSON string in post meta.
- Simple nested list UI (Three.js deferred — see remaining decisions).

## Remaining decisions

- Three.js radial/tree visualization vs current list view.
- Drag-and-drop reordering.
- REST GET endpoint for read-only clients.
- Gutenberg block wrapper.
