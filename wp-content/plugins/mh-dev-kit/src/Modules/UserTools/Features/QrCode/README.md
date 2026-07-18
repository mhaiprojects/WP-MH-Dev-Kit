# QR Code Generator

User-scoped QR codes via CPT `mhdk_qr_code`.

## Admin

**MHDK Settings → User Tools → QR Codes** (or `edit.php?post_type=mhdk_qr_code`)

Create a QR Code post, set target URL/text and size. Only the author (or admin) can edit.

## Shortcode

```
[mhdk_qr_code id="123"]
```

Renders QR on frontend using qrcodejs (jsDelivr CDN). Visible to post author and admins when published.

## Auto-approved decisions

- User-scoped CPT with author capability mapping.
- Client-side QR render (no server image storage).
- Size range 128–512 px.

## Remaining decisions

- Public QR pages without login requirement.
- PNG download / print button.
- REST CRUD endpoints for mobile apps.
