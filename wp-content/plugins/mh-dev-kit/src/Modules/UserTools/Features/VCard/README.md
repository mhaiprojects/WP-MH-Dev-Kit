# vCard Generator

User-scoped contact cards with downloadable `.vcf` files.

## Admin

Create vCards under **User Tools → vCards** (`edit.php?post_type=mhdk_vcard`).

## Shortcode

```
[mhdk_vcard id="123"]
```

## Download URL

```
/vcard/{post_id}/
```

Returns `text/vcard` attachment (flush permalinks after activation).

## Auto-approved decisions

- vCard 3.0 format generated in PHP.
- Public download for published vCards (no auth on download URL).

## Remaining decisions

- vCard 4.0 / photo embedding.
- Restrict download to logged-in users.
- QR code linking to vCard download.
