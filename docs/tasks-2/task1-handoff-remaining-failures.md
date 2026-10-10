# Task 1 Handoff — Remaining Failures for Tasks 2 and 3

**From:** Jassy (Task 1 — User Access and Volunteer Management)
**Status:** Task 1 is complete, verified, committed (`2990346`), and pushed to `origin/jassy`.

This note lists the full-suite test failures that are **outside Task 1 scope** so the
owners can pick them up. Task 1 tests are all green:
`AdminUserAccessTest` (7/7) and `WebAuthenticationTest` (7/7).

## How to reproduce

The tests are configured for SQLite `:memory:` (`phpunit.xml`), but many machines only
have the `pdo_mysql` driver. Run against an isolated MySQL test database instead:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS resqlink_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
cd backend
npm run build                      # required: rebuilds the Vite manifest for map/volunteer assets
DB_CONNECTION=mysql DB_DATABASE=resqlink_test php artisan test
```

> `npm run build` must be run first. Without it, several dispatcher/volunteer pages fail
> with `Unable to locate file in Vite manifest: resources/js/dispatcher-map.js` (and the
> `volunteer-report` entrypoints). That is a stale `public/build/manifest.json`, not a
> code bug — rebuilding fixes it.

## For Angela — Task 3 (Dispatcher Incident Coordination)

File: `backend/tests/Feature/DispatcherIncidentCoordinationTest.php`

1. **`test_dispatcher_can_view_and_filter_the_incident_queue`** (assertion at line 30)
   The barangay filter is expected to be an **exact match**. The test filters by
   `barangay = 'Lahug (Pob.)'` and also by the partial `'Lahu'`. Current
   `Dispatcher\IncidentController::index()` uses `where('barangay', $barangay)`, which is
   already exact — so verify the partial `'Lahu'` case returns **no** rows (it should,
   because `'Lahu' !== 'Lahug (Pob.)'`). If the failure is actually on the category
   filter (line 30 asserts filtering by `category=Fire` hides the `Lahug (Pob.)` flood),
   confirm the queue rows render only matching incidents and that unrelated incidents do
   not appear elsewhere on the page (e.g. a summary count or sidebar).

2. **`test_dispatcher_map_feed_returns_database_incidents_as_geojson`** (assertion at line 68)
   `impact_radius` is asserted as the float `300.0`, but the JSON returns integer `300`.
   The map feed casts with `(float) ($incident->impact_radius ?? 0)`, yet the value round-
   trips through JSON as an int because the stored column is an integer. Decide the
   contract: either make the test assert `300` (int) or guarantee a float in the JSON
   response. Align the assertion with the intended GeoJSON schema.

## For Adriane — Task 2 (Incident Reporting and Severity)

File: `backend/tests/Feature/VolunteerIncidentSubmissionTest.php`

1. **`test_verified_volunteer_sees_confirmation_after_submitting_incident`** (assertion at line 50)
   After submitting, the confirmation view is expected to show the incident reference
   `INC-0001`. The dispatcher map feed already builds references as
   `'INC-'.str_pad((string) $incident->id, 4, '0', STR_PAD_LEFT)`. The volunteer
   confirmation page (`resources/views/volunteer/incidents/create.blade.php`) needs to
   render the same `INC-####` reference for the just-submitted incident (it is flashed to
   the session as `incident`). Surface that reference in the success message.

## Notes

- These are the only failures observed after Task 1 was fixed and the frontend was
  rebuilt. Task 1 does not depend on any of the above.
- If any of these touch shared fields/contracts (e.g. `impact_radius` type, incident
  `reference`), please flag it back so the PM reconciliation stays accurate.
