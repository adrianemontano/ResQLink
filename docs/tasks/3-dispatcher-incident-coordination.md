# Task 3 — Dispatcher Incident Coordination

## Seeder Credentials — Read First

The current `DatabaseSeeder` creates the roles `admin`, `dispatcher`, and
`volunteer`, but only the default admin user is seeded:

| Role | Username | Email | Password |
| --- | --- | --- | --- |
| Admin | `admin` | `admin@resqlink.local` | `Admin@12345` |

Use the admin account to create a dispatcher account before testing dispatcher
features. These are development credentials only.

**Assigned owner:** Angela
**Primary system user:** Dispatcher  
**Platform:** Laravel Blade, Bootstrap 5, and Leaflet.js web dashboard  
**Priority:** Must

## Local map requirement

The Dispatcher `Map View` and incident detail mini-map must work without
internet access. Use Leaflet with locally bundled assets and a locally served
basemap or packaged map data. Do not reference remote tile URLs, CDNs,
geocoding services, or external map APIs. Incident markers and impact-radius
circles must be rendered from the locally stored latitude, longitude, and
radius values.

Before implementation, document the selected local tile/data source and storage
path. The application must show a clear local fallback (such as a coordinate
grid) when detailed basemap tiles are unavailable, rather than failing because
the network is disconnected.

## Required pages and visibility

The page structure follows the Dispatcher Control Center in
`html/resqgroup.html`:

| Page or UI location | Required content |
| --- | --- |
| Dispatcher sidebar | `Dashboard`, `Incidents`, and `Map View` navigation items; visible only to Dispatcher |
| Dispatcher — Dashboard tab | Summary/stat cards and an Active Incidents table with reporter, category, persons, barangay, time, status, and actions |
| Dispatcher — Incidents tab | Incident Management page with category, status, and barangay filters; complete incident table; detail/open action |
| Dispatcher — Map View tab | Local Leaflet map, incident markers, impact-radius circles, and map legend for active/historical incidents |
| Incident Details side panel | Incident information, reporter, location, mini-map, radius, severity, notes, current status, history, and status action buttons |
| Shared dashboard footer | Logged-in dispatcher identity and Logout action |

The HTML reference uses a right-side details panel opened from the dashboard
table or map marker. The Laravel implementation may use a dedicated details
page, but it must preserve the same information and actions.

## System requirements covered

- Dispatcher Map View.
- Incident Summary View.
- Digital Incident Logging and Status Management.
- Incident Queue Management.
- Dispatcher Dashboard requirements in the project lifecycle and technology
  stack.

## Scope

The owner will implement the dispatcher’s daily workflow: viewing incoming
incidents, prioritizing them by preliminary severity, inspecting details on a
map, and recording status changes through completion.

## Functional requirements

- `TASK3-001` A dispatcher can view incoming incidents in an incident queue.
- `TASK3-002` The queue organizes incidents according to preliminary severity
  and displays the current workflow status.
- `TASK3-003` A dispatcher can open an incident summary containing category,
  volunteer, reported date/time, affected persons, location, barangay,
  landmark, impact radius, notes, severity, and status.
- `TASK3-004` The dashboard displays active and historical incidents on a
  Leaflet map using markers and impact-radius visualization.
- `TASK3-005` A dispatcher can update status only through Reported, Received,
  Dispatched, and Completed.
- `TASK3-006` Every status change records the dispatcher and timestamp in the
  complete incident history.
- `TASK3-007` The dispatcher can distinguish active incidents from completed or
  archived incidents.
- `TASK3-008` Unauthorized users cannot access dispatcher operations.

## Expected implementation outputs

- Dispatcher routes using the `dispatcher.*` naming prefix.
- Dispatcher controllers, policies, requests/services, and Blade views.
- Views under `resources/views/dispatcher/dashboard/`,
  `resources/views/dispatcher/incidents/`, and map-related partials.
- `dispatcher-map.js` or equivalent Leaflet asset using the project naming
  conventions.
- Incident status history presentation and update behavior.

## Dependencies and handoff

Depends on Tasks 1 and 2 for authenticated dispatchers and incident records.
Coordinate the incident history schema with Task 2 and the admin records/report
interfaces with Task 4. Do not change severity rules without agreement from the
incident-domain owner.

## Acceptance criteria

The feature is complete when:

- A dispatcher can see incoming incidents in a severity-aware queue.
- A dispatcher can open every required incident detail.
- Active and historical incidents render correctly on the Leaflet map with
  location markers and impact-radius indicators.
- A dispatcher can make valid status changes only in the approved lifecycle.
- Each change is persisted with actor and timestamp history.
- Completed incidents remain available for historical review.
- Unauthorized users are denied dispatcher pages and actions.
- The dispatcher workflow is documented and follows the route/view naming rules.

## Task folder label

This document belongs to **Task 3 — Dispatcher Incident Coordination**.

## Frontend/backend split

The split below defines the concrete interface and server responsibilities for
each requirement; no requirements are added.

- **`TASK3-001`** Dispatchers can view incoming incidents in an incident queue.
  - Frontend: Build the dispatcher queue with required summary fields.
  - Backend: Provide dispatcher-authorized access to incoming incidents.
- **`TASK3-002`** The queue is ordered by preliminary severity and shows workflow status.
  - Frontend: Display severity ordering and current status.
  - Backend: Order incidents and return workflow status.
- **`TASK3-003`** Dispatchers can view complete incident summaries and details.
  - Frontend: Build the incident details view.
  - Backend: Return all required summary fields.
- **`TASK3-004`** Active and historical incidents are shown on the local Leaflet map.
  - Frontend: Render local markers and impact-radius circles.
  - Backend: Supply authorized coordinates and radius values.
- **`TASK3-005`** Workflow status is limited to Reported, Received, Dispatched, and Completed.
  - Frontend: Provide only the four allowed status controls.
  - Backend: Validate and reject other status values.
- **`TASK3-006`** The system records the dispatcher and timestamp for every status change.
  - Frontend: Display dispatcher, timestamp, and history entries.
  - Backend: Persist the acting dispatcher and change timestamp.
- **`TASK3-007`** Active, completed, and archived incidents are visibly distinguished.
  - Frontend: Use clear labels or visual states.
  - Backend: Identify and filter each incident group consistently.
- **`TASK3-008`** Users without dispatcher access cannot perform dispatcher operations.
  - Frontend: Show unauthorized state and hide restricted controls.
  - Backend: Enforce authorization for all dispatcher operations.
