# Children Pages

Lists child pages of the current page or a specified parent.

## Shortcode

```
[mhdk_children_pages]
[mhdk_children_pages parent_id="42" orderby="title" order="ASC" limit="20"]
```

| Attribute | Default | Description |
|-----------|---------|-------------|
| `parent_id` | current page | Parent page ID |
| `orderby` | `menu_order` | `menu_order`, `title`, `date`, `ID` |
| `order` | `ASC` | `ASC` or `DESC` |
| `limit` | `50` | Max items (1–100) |

## Block

Insert **MH Children Pages** from the MHDK block category. Configure parent ID and sort in the sidebar.

## Auto-approved decisions

- Uses core `page` post type children only.
- Server-side dynamic block render (no saved HTML).

## Remaining decisions

- Whether to support custom post type children (currently pages only).
- Styling: currently minimal list CSS; theme integration left to site owner.
