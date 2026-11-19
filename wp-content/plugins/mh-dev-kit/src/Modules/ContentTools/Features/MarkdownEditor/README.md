# Markdown Editor Block

Gutenberg block for writing page content in Markdown.

## Block

Insert **MH Markdown** from the MHDK category. Edit Markdown in the block or sidebar; preview renders via [marked.js](https://marked.js.org/) (jsDelivr CDN).

The block is allowed in all block editor contexts (pages, posts, CPTs, widgets, site editor) via `allowed_block_types_all`.

## Demo page

On first load, a published demo page is created at `/markdown-block-demo/` containing sample Markdown block content.

## Auto-approved decisions

- Client-side render on frontend (marked.js) to avoid PHP Markdown dependency.
- Markdown stored in block attribute `content` (dynamic block, server outputs data container).

## Remaining decisions

- Server-side Markdown render for SEO/no-JS fallback.
- Sanitize HTML output (marked allows raw HTML by default).
- Self-host marked.js instead of CDN.
