# TASK3 — Dispatcher Incident Coordination

**Assigned owner:** Angela

## Implemented workflow

Dispatcher routes are protected by authentication and the `dispatcher` role.
The incident queue is ordered by preliminary severity, then workflow sequence
and reported time, with category, status, and barangay filters. Dispatchers can
open a full incident summary and update status only through `Reported`,
`Received`, `Dispatched`, and `Completed`.

Each accepted status change stores the acting dispatcher, timestamp, status
reference, and optional note in `incident_histories`. Completed incidents remain
available in the queue and map for historical review.

The map is intentionally read-only. Dispatchers inspect incident locations and
open details from markers; incident creation remains in the volunteer reporting
workflow, so clicking an empty map area does not open a save modal.

The compatibility migration
`2026_08_25_000000_add_severity_to_incidents_table.php` ensures the denormalized
`incidents.severity` value used for queue ordering is present alongside
`severity_id`.

## Map source

The map uses Leaflet bundled locally through Vite and the local Cebu City
development dataset at `public/maps/resqlink-map.geojson`. The dataset contains
an approximate service-area boundary, major-road lines, and landmark points. No
remote tile URLs, paid map APIs, API keys, geocoding services, or external map
APIs are used. Markers use stored latitude and longitude values, and each
incident's stored impact radius is rendered as a local circle. Marker selection
opens the dispatcher incident detail page. The coordinate-grid fallback remains
available when local GeoJSON data is missing.

## Phase 2 interface consistency

Shared theme and widget styles now provide distinct Reported and Completed status
badges, consistent queue and detail-page action links, clearer filter spacing, and
responsive behavior for incident tables and detail layouts on small screens.

## Verification setup

Run `php artisan migrate --seed` in `backend/` before testing. Create a
dispatcher account with the seeded admin account, then sign in as that
dispatcher to verify the protected routes and status workflow. For a direct
SQLite import of the two demo incidents, run the statements in
`backend/database/sample-data/incidents.sql` after migrations and reference
data seeding. The Laravel `SampleIncidentSeeder` remains the recommended
cross-database option.