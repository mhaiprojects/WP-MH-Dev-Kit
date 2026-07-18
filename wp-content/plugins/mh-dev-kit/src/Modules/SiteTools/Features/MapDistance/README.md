# Map Distance Calculator

Calculates straight-line (haversine) distance from a configured start point to the visitor's browser geolocation.

## Shortcode

```
[mhdk_map_distance]
```

## Settings

**MHDK Settings → Map Distance** — configure start label, latitude, longitude.

Default start: Shepparton CBD (-36.3809, 145.3985).

## Demo page

Auto-created at `/map-distance-demo/` with the shortcode embedded.

## Auto-approved decisions

- Haversine formula (great-circle distance), not driving distance.
- Geolocation requires user consent via browser prompt.
- Distance shown in km and metres.

## Remaining decisions

- Integrate map visualization (Leaflet/Google Maps).
- Server-side distance API for logged-in users without geolocation.
- Custom start point per shortcode instance.
