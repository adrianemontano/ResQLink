# TASK3 — Angela: Remaining Map Integration Work

## Seeder data
`DatabaseSeeder` creates `admin`, `dispatcher`, and `volunteer` roles. Development accounts: Admin `admin` / `admin@resqlink.local` / `Admin@12345`; Dispatcher `Angela` / `a@gmail.com` / `admin123`; Volunteer `volunteer` / `volunteer@resqlink.local` / `Volunteer@12345`. These are development-only.

## Current implementation

The volunteer and dispatcher maps use bundled MapLibre assets, a local
TileServer style, and application-owned Cebu City GeoJSON. Map search uses local
barangay and landmark features; no CARTO tile service, CDN, Nominatim request,
paid map API, or API key is used.

## What the new map dataset will do

The dataset contains 80 Cebu City barangays. Each barangay has boundary shapes
and names/codes. It is already stored locally in the Laravel app and does not
need a separate API to display the barangay boundaries. The app can load it
directly using:

```text
/maps/cebu-city-barangays.geojson
```

The integrated dataset now:

- Show Cebu City barangay boundaries on the MapLibre map.
- Display a barangay name when hovered or clicked.
- Help identify which barangay contains a selected incident location.
- Support offline barangay selection.

The GeoJSON itself needs no API. However, the existing map background and map
search still use external online map services and should be documented as
known limitations until local replacements are available.

## Remaining assignment

- Verify every local layer and the authenticated incident feed in a browser.
- Verify the visible fallback/error state when the local TileServer is stopped.
- Confirm barangay search, hover/click identification, incident markers, impact
  radii, popups, filters, and detail links.
- Record automated and manual acceptance evidence and reconcile any remaining
  incident-field or history defects with Tasks 2 and 4.

## Definition of done
The integrated local map has recorded automated and manual verification and is
documented for PM reconciliation.

## Additional prepared local layers

The following BBBike/OpenStreetMap-derived GeoJSON assets are integrated:

- `/maps/cebu-city-osm-roads.geojson` — 14,067 road and path features.
- `/maps/cebu-city-osm-landmarks.geojson` — 3,516 named points of interest.
- `/maps/cebu-city-osm-emergency-facilities.geojson` — 48 emergency-related points.

They were clipped to the Cebu City barangay boundary area and are loaded by the
volunteer and dispatcher maps. Emergency-facility coverage is dependent on OpenStreetMap
mapping and requires review before operational use.
