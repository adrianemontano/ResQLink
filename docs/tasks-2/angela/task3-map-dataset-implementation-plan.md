# TASK3 — Angela: Map Dataset Implementation Plan

## Seeder data

The current `DatabaseSeeder` creates `admin`, `dispatcher`, and `volunteer`
roles. Development accounts are Admin `admin` / `admin@resqlink.local` /
`Admin@12345`, Dispatcher `Angela` / `a@gmail.com` / `admin123`, and Volunteer
`volunteer` / `volunteer@resqlink.local` / `Volunteer@12345`. These credentials
are for development only.

## Objective

Maintain and verify the locally integrated Cebu City GeoJSON layers in the
dispatcher MapLibre map so barangay boundaries and names remain usable during
incident coordination without an internet dependency.

## Dataset and loading contract

- Dataset: `backend/public/maps/cebu-city-barangays.geojson`
- Public application path: `/maps/cebu-city-barangays.geojson`
- Coverage: 80 Cebu City barangays.
- Contents: barangay boundary shapes and barangay names/codes.
- Delivery: load the file directly from the Laravel public path; no separate API
  is required for displaying the boundaries.

Additional prepared reference layers are `backend/public/maps/cebu-city-osm-roads.geojson`
(14,067 features), `backend/public/maps/cebu-city-osm-landmarks.geojson`
(3,516 features), and
`backend/public/maps/cebu-city-osm-emergency-facilities.geojson` (48 features).
These clipped OpenStreetMap-derived GeoJSON assets are loaded by the volunteer
and dispatcher map modules.

## Implementation steps

1. Inspect the GeoJSON structure and identify the exact properties used for the
   barangay name and code.
2. Load the local GeoJSON with bundled MapLibre assets and render it
   as a boundary layer on the dispatcher map.
3. Apply clear boundary styling and add hover highlighting.
4. Add a popup or tooltip showing the barangay name and code when a boundary is
   hovered or clicked.
5. Use the incident latitude and longitude to identify and display the selected
   incident's barangay when possible.
6. Connect the layer to the existing incident map and details workflow without
   changing Task 2 severity or Task 3 status rules.
7. Test loading the dataset, boundary interaction, incident location display,
   and barangay selection with internet access disabled.
8. Document the completed integration and any unsupported or unmatched
   coordinates.

## Local-service dependency

The GeoJSON, JavaScript, CSS, and search data are local and require no external
API. The detailed basemap style is served by the locally operated TileServer at
the configured `LOCAL_MAP_STYLE_URL`. The application must display a useful
message or reference layers when that local process is unavailable.

## Acceptance criteria

- All 80 barangay features can be loaded from the documented local path.
- Boundaries appear on the dispatcher MapLibre map with no GeoJSON API endpoint.
- Hovering or clicking a boundary identifies its barangay name/code.
- Incident locations can be associated with a barangay when they fall within a
  boundary.
- Barangay boundary display and selection continue to work without internet.
- The local TileServer dependency and fallback behavior are documented.
