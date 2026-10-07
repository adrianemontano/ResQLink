# TASK3 — Angela: Remaining Map Integration Work

## Seeder data
`DatabaseSeeder` creates `admin`, `dispatcher`, and `volunteer` roles. Development accounts: Admin `admin` / `admin@resqlink.local` / `Admin@12345`; Dispatcher `Angela` / `a@gmail.com` / `admin123`; Volunteer `volunteer` / `volunteer@resqlink.local` / `Volunteer@12345`. These are development-only.

## Current issue with the app

The current map still uses external online services for its background tiles
from CARTO/OpenStreetMap and place search through the Nominatim API. This means
the map background and search features may not work when the application has no
internet connection. The locally prepared barangay GeoJSON does not have this
problem, but it has not yet been fully applied to the dispatcher map workflow.

## What the new map dataset will do

The dataset contains 80 Cebu City barangays. Each barangay has boundary shapes
and names/codes. It is already stored locally in the Laravel app and does not
need a separate API to display the barangay boundaries. The app can load it
directly using:

```text
/maps/cebu-city-barangays.geojson
```

When applied to the app, it can:

- Show Cebu City barangay boundaries on the Leaflet map.
- Display a barangay name when hovered or clicked.
- Help identify which barangay contains a selected incident location.
- Support offline barangay selection.

The GeoJSON itself needs no API. However, the existing map background and map
search still use external online map services and should be documented as
known limitations until local replacements are available.

## Assignment

- Integrate the local GeoJSON into the dispatcher Leaflet map.
- Display barangay boundaries, names/codes, incident coordinates, and impact radius.
- Add hover/click barangay identification and incident-to-barangay lookup.
- Keep barangay selection usable offline.
- Coordinate incident fields with Adriane and status/history behavior with Monique.
- Document the dataset path, integration behavior, external-service limitations, and offline behavior.

## Definition of done
The local map is integrated into dispatcher workflows and documented for PM reconciliation.

## Additional prepared local layers

The following BBBike/OpenStreetMap-derived GeoJSON assets are also available for
future integration:

- `/maps/cebu-city-osm-roads.geojson` — 14,067 road and path features.
- `/maps/cebu-city-osm-landmarks.geojson` — 3,516 named points of interest.
- `/maps/cebu-city-osm-emergency-facilities.geojson` — 48 emergency-related points.

They were clipped to the Cebu City barangay boundary area and are not yet loaded
by the application. Emergency-facility coverage is dependent on OpenStreetMap
mapping and requires review before operational use.
