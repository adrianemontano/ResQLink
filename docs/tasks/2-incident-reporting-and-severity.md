# Task 2 — Incident Reporting and Severity Assessment

## Seeder Credentials — Read First

The current `DatabaseSeeder` creates the roles `admin`, `dispatcher`, and
`volunteer`, but only the default admin user is seeded:

| Role | Username | Email | Password |
| --- | --- | --- | --- |
| Admin | `admin` | `admin@resqlink.local` | `Admin@12345` |

Use the admin account to create a volunteer account before testing volunteer
incident reporting. These are development credentials only.

**Suggested owner:** One groupmate responsible for incident domain logic and
database/API operations  
**Primary system user:** Volunteer submits; system validates and classifies  
**Platform:** Laravel backend, MySQL, and the currently supported web interface  
**Priority:** Must

## Local map requirement

Incident location selection and visualization must be designed for a local,
offline-capable map. Use Leaflet with locally stored JavaScript, CSS, and map
tile/data assets. Do not use OpenStreetMap or any other remote tile URL, CDN,
geocoding service, or external map API. The incident record must still store
latitude, longitude, and impact radius when the application has no internet
connection.

The implementation plan must identify the local tile/data source and its
storage path before development. If a full local basemap is not available,
provide a local coordinate grid or locally packaged basemap fallback rather
than silently requiring internet access.

## Required pages and visibility

Mobile screens are excluded from this four-task plan. The desktop prototype does
not show a volunteer web submission page, so this task should deliver the
server-side incident submission capability and define the page contract for a
future approved client without implementing Flutter screens.

| Page or UI location | Required content |
| --- | --- |
| Incident submission page/API contract | Category, barangay, nearest landmark, estimated affected persons, local map coordinates, impact radius, optional notes, and Submit Incident action |
| Submission validation area | Field-level errors, invalid-location/radius errors, and inactive-account message |
| Submission success state | Incident identifier, reported timestamp, initial `Reported` status, and preliminary severity |
| Dispatcher Incident Management page | Newly submitted incident appears in the incident table used by Task 3 |
| Dispatcher Incident Details panel | Submitted fields, map location, impact radius, severity, and status are available to the details view |

The `resqgroup.html` reference places the resulting incident data in the
Dispatcher `Incidents` tab, `Incident Map` tab, and right-side `Incident Details`
panel. Do not add the prototype's mobile view to this plan.

## System requirements covered

- Incident Alert Summary.
- GPS location capture.
- Preliminary Incident Severity Assessment.
- Incident data storage and validation.
- Laravel business logic, REST API, MySQL, and Leaflet-compatible map data.

## Scope

The owner will implement the central incident record and the server-side
operation that accepts a complete incident report. Mobile application screens
and mobile integration are explicitly outside this plan. The implementation
must still expose clean backend operations for an approved client or existing
web workflow to submit incident data.

## Functional requirements

- `TASK2-001` A verified volunteer can submit an incident report containing
  category, barangay, nearest landmark, estimated affected persons, latitude,
  longitude, impact radius, and optional notes.
- `TASK2-002` Category is restricted to Flood, Earthquake, Landslide, or Fire.
- `TASK2-003` Latitude, longitude, impact radius, and reported timestamp are
  stored with the incident.
- `TASK2-004` The report is associated with the authenticated volunteer.
- `TASK2-005` Required fields are validated and invalid submissions are rejected
  with useful errors.
- `TASK2-006` The system calculates Low, Moderate, High, or Critical severity
  from estimated affected persons and impact radius.
- `TASK2-007` Severity is preliminary decision support and does not replace
  dispatcher judgment or official emergency assessment.
- `TASK2-008` A newly accepted incident receives the `Reported` workflow status.

## Expected implementation outputs

- `Incident` model, migration, relationships, factory, and seed data as needed.
- `StoreIncidentRequest` or equivalent validation request.
- Incident domain service/action for severity classification.
- Incident controller/API endpoints and authorization policy.
- Consistent JSON or web responses for success, validation, unauthorized, and
  not-found cases.

## Dependencies and handoff

Depends on Task 1 for authenticated volunteer identity and roles. Coordinate
incident fields and severity thresholds with the database and dispatcher task
owners. Do not implement dispatcher queue screens or mobile screens here.

## Acceptance criteria

The feature is complete when:

- An authenticated, active volunteer can submit a complete incident report.
- The system rejects missing fields, invalid categories, invalid coordinates,
  and invalid radius values.
- The stored record contains the required incident data and reporting volunteer.
- A reported timestamp and `Reported` status are assigned automatically.
- Severity is consistently calculated as Low, Moderate, High, or Critical.
- The incident can be retrieved in a format usable by the dispatcher map and
  details view.
- Unauthorized users cannot submit incidents.
- The requirement-to-code mapping and severity rules are documented.
