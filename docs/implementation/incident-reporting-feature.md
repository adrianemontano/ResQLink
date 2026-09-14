# TASK2 — Incident Reporting and Severity Feature

**Assigned owner:** Adriane

## Scope

Task 2 provides a backend operation for an authenticated, active volunteer to
submit an incident report. Mobile screens and dispatcher status controls remain
outside this feature.

## Endpoint

`POST /api/incidents`

## Volunteer web flow

Verified active volunteers can also sign in through the web application. After
login they are redirected to `/volunteer/dashboard` and can submit the same
incident structure through `/volunteer/incidents/create`. The web form posts to
`/volunteer/incidents` and uses the shared `StoreIncidentRequest` validation and
`IncidentSubmissionService`, so API and web submissions produce the same stored
incident data, severity, timestamp, and `Reported` status.

Development testing requires an active volunteer user with a related
`volunteer_profiles.verification_status` value of `verified`.

The request must be authenticated as an active user with the `volunteer` role
and a related `volunteer_profiles` record whose `verification_status` is
`verified`. Inactive, unverified, or non-volunteer users are rejected.
The endpoint accepts JSON or form data with:

| Field | Required | Rules |
| --- | --- | --- |
| `category` | Yes | `Flood`, `Earthquake`, `Landslide`, or `Fire` |
| `barangay` | Yes | Text, maximum 100 characters |
| `nearest_landmark` | Yes | Text, maximum 255 characters |
| `affected_population` | Yes | Integer from 0 to 1,000,000 |
| `latitude` | Yes | Numeric value from -90 to 90 |
| `longitude` | Yes | Numeric value from -180 to 180 |
| `impact_radius` | Yes | Positive numeric value, maximum 100,000 metres |
| `notes` | No | Text, maximum 5,000 characters |

The authenticated volunteer is assigned automatically; clients cannot choose a
different reporter.

## Stored workflow values

Accepted reports receive `Reported` status and an automatic `reported_at`
timestamp. Reference IDs for the selected category, severity, and `Reported`
status are also stored when the reference rows are seeded. The response
includes the incident identifier, submitted location, impact radius, and
preliminary severity.

Severity is calculated by `IncidentSeverityService` using both affected
population and impact radius. The current scoring thresholds are:

- Population: 0–9 = 0, 10–49 = 2, 50–99 = 3, 100+ = 4.
- Radius in metres: below 100 = 0, 100–499 = 2, 500–999 = 3, 1,000+ = 4.
- Total score: 0–1 Low, 2–3 Moderate, 4–5 High, 6+ Critical.

This classification is preliminary decision support only. It does not replace
dispatcher judgment or an official emergency assessment.

## Map contract

The incident record stores latitude, longitude, and impact radius independently
of map rendering. The approved fallback for this backend task is a local
coordinate grid served by the application; it requires no external tiles or
network access. A packaged local Leaflet/basemap may be added by Task 3 under a
documented `public/maps/` path. Remote tile URLs, CDNs, geocoding services, and
external map APIs are not permitted.

## Requirement mapping

- `TASK2-001`–`TASK2-005`: `StoreIncidentRequest` and API incident controller.
- `TASK2-006`–`TASK2-007`: `IncidentSeverityService` and this document.
- `TASK2-008`: API controller assigns `Reported` and `reported_at`.
