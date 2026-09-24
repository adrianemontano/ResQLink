# TASK3 — Dispatcher Incident Coordination

**Assigned owner:** Angela

## Implemented workflow

Dispatcher routes are protected by authentication and the `dispatcher` role.
The incident queue is ordered by preliminary severity, then workflow sequence
and reported time, with category, status, and a searchable barangay selector.
The selector uses the barangay names from the local Cebu City map dataset, so
the incident list can be filtered by an existing mapped barangay. Dispatchers
can open a full incident summary and update status only through `Reported`,
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
 
The dispatcher and volunteer maps share MapLibre, the configured local
TileServer GL style, and reusable local-map helpers bundled through Vite. The
dispatcher overlays `cebu-city-barangays.geojson`,
`cebu-city-osm-roads.geojson`, `cebu-city-osm-landmarks.geojson`, and
`cebu-city-osm-emergency-facilities.geojson` from `public/maps/`.

Dispatcher incidents are no longer read from the demonstration
`public/maps/incidents.geojson` file. The authenticated
`GET /dispatcher/map/incidents` route creates GeoJSON from current database
records that have coordinates. Status-colored markers, impact-radius polygons,
popup summaries, and incident-detail links use that feed. The incident-details
mini-map filters the same feed to the selected incident.

No remote tile URL, paid map API, API key, or geocoding service is required.
The configured local TileServer process must be running for the detailed
background style; application-owned GeoJSON overlays are served directly by
Laravel's public assets.

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

Automated coverage is provided in
`tests/Feature/DispatcherIncidentCoordinationTest.php` and covers queue
filtering, details, map rendering, valid and invalid status transitions,
history persistence, completed visibility, and authorization. The focused
suite passes with 5 tests and 24 assertions; the full suite passes with 19
tests and 69 assertions.

## Recommended next enhancements

Manual browser acceptance was completed on 2026-09-09. The verified flow
included Dispatcher login, dashboard counters and queue, category filtering,
Clear, incident details, a Reported-to-Received status update with history,
local map layers, custom popup close behavior, and map status filters.

Recommended next enhancements:

1. Add dispatcher assignment and presence indicators so the response owner is
	visible separately from the dispatcher who changed status.
2. Add polling or event-based refresh for active incidents when live
	integration is approved.
3. Add conflict handling for concurrent status updates.
4. Add admin archive/report integration so archived records have an explicit
	lifecycle state instead of relying only on Completed.
5. Add audit logging for failed authorization and invalid transition attempts.
