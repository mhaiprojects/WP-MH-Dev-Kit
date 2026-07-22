# Store Locator

Find pickup stores across Australia using the shared haversine distance calculator (same math as Map Distance).

## Features

- Four seeded AU stores (Sydney, Melbourne, Brisbane, Perth)
- Shortcode `[mhdk_store_locator]` with geolocation + nearest-store sorting
- REST: `GET /wp-json/mhdk/v1/stores`, `/stores/nearby?lat=&lng=`, `/stores/{id}/distance?lat=&lng=`
- WooCommerce checkout pickup store field with live distance
- Demo page: `/store-locator/`

## Admin

**Commerce → Store Locator Settings** (`mhdk-settings-store-locator`)
