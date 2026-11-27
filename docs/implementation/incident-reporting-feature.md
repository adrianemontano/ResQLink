# TASK2 — Incident Reporting and Severity Feature

**Assigned owner:** Adriane

## Scope

Task 2 provides a backend operation for an authenticated, active volunteer to
submit an incident report. Mobile screens and dispatcher status controls remain
outside this feature.

## Endpoint

`POST /api/incidents`

The API endpoint requires a Sanctum bearer token with the
`incidents:create` ability. The authenticated account must be an active,
verified volunteer and must have replaced any temporary password. Web incident
submission continues to use the existing Laravel session flow.

## Volunteer web flow

Verified active volunteers can also sign in through the web application. After
login they are redirected to `/volunteer/dashboard` and can submit the same
incident structure through `/volunteer/incidents/create`. The web form posts to
`/volunteer/incidents` and uses the shared `StoreIncidentRequest` validation and
`IncidentSubmissionService`, so API and web submissions produce the same stored
incident data, severity, timestamp, and `Reported` status.

After a successful web submission the volunteer is redirected back to the
create form with a `success` flash (`Incident INC-0001 was submitted successfully.`)
that renders as a single auto-dismissing `x-toast`. This toast is the only
submission confirmation; the inline banner was removed. Validation
errors from `StoreIncidentRequest` also render as error toasts.

### Incident photo (camera capture)

The web form (`multipart/form-data`) has an optional photo field
(`volunteer/incidents/partials/photo-field.blade.php`):

- **Take photo** opens the device's rear camera directly on phones and tablets
  (`<input type="file" capture="environment">`).
- On laptops and desktops, whose browsers ignore `capture`, **Take photo**
  opens an in-page webcam window with **Capture** and **Cancel**
  (`resources/js/volunteer-incident-webcam.js`, using `getUserMedia`). The
  webcam is switched off when the window closes. If no camera exists or
  access is denied, the form falls back to the file picker and says why.
  Browsers allow webcam access only on HTTPS, or on `http://localhost` /
  `http://127.0.0.1` during development, so production needs HTTPS.
- **Or choose from gallery** picks an existing image; it is copied into the
  submitted `photo` input, so one file is always sent.
- A preview with **Retake** and **Remove** is shown before submitting.
- `resources/js/volunteer-incident-photo.js` resizes large photos in the browser
  (longest side 1,920 px, JPEG, target about 1.5 MB). Phone camera photos are
  often 3–10 MB, and PHP's default `upload_max_filesize` is 2 MB.
- The photo is stored on the private `local` disk by `IncidentSubmissionService`
  and appears on the Dispatcher incident page ("Incident Photo & Attachment")
  and through the protected Dispatcher/Admin photo routes.
- Browsers do not keep a selected file after a validation error, so the
  volunteer must take the photo again if the form is returned with errors.
- Coverage: `tests/Feature/VolunteerIncidentPhotoTest.php` (written, not yet
  executed).

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
| `photo` | No | JPEG or PNG image, maximum 5 MB (multipart upload) |

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
MapLibre basemap served by the local TileServer process, with application-owned
GeoJSON overlays under the documented `public/maps/` path. Remote tile URLs,
CDNs, geocoding services, and
external map APIs are not permitted.

## Requirement mapping

- `TASK2-001`–`TASK2-005`: `StoreIncidentRequest` and API incident controller.
- `TASK2-006`–`TASK2-007`: `IncidentSeverityService` and this document.
- `TASK2-008`: API controller assigns `Reported` and `reported_at`.

## TASK2 volunteer map behavior

The volunteer map uses the bundled Cebu City barangay GeoJSON for local
barangay search and selection. Selecting a barangay navigates to its boundary,
briefly highlights it, and synchronizes the form field. Dropping or dragging an
incident pin resolves the containing barangay into that field. The impact
radius is hidden while the pin is being dragged and is rendered again after the
pin is released.

The nearest-landmark field is also populated from the local landmark dataset.
After pin placement, its searchable options are limited to landmarks contained
by the selected barangay polygon, with the closest available landmark selected.
The selected landmark is emphasized on the map with a red highlight.

## TASK2-009 volunteer dashboard analytics

The volunteer dashboard summarizes only incidents linked to the authenticated
volunteer through `volunteer_id` or `reported_by`. It displays total, open, and
completed report counts, a six-month trend, category distribution, and the five
most recent reports. Records without `reported_at` use their creation timestamp
for the trend and recent-report date. The dashboard uses the existing local
ResQLink palette and responsive layout; it does not add schema or API changes.

## TASK2-010 — Map-independent volunteer submission

**Assigned owner:** Adriane

The volunteer incident form remains submittable when the map or its JavaScript
is unavailable. Volunteers can enter latitude, longitude, and impact radius
manually; placing a map pin and moving the radius slider continue to fill those
fields automatically. The server validates the same required location fields
for either entry method.
