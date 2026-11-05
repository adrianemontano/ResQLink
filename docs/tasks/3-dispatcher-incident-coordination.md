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
**Platform:** Laravel Blade, Bootstrap 5, and MapLibre GL web dashboard
**Priority:** Must

## Local map requirement

The Dispatcher `Map View` and incident detail mini-map use the same locally
served MapLibre basemap as the volunteer reporting workflow. MapLibre assets
are bundled locally, and Cebu City barangays, roads, landmarks, and emergency
facilities are served from `public/maps/`. Do not use remote tile URLs, paid
map APIs, API keys, geocoding services, or external map APIs. Incident markers
and impact-radius polygons are rendered from authenticated database incident
data containing stored latitude, longitude, and radius values.
The map attribution remains available through a compact, expandable control.

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
| Dispatcher — Incidents tab | Incident Management page with category and status filters, a searchable mapped-barangay selector, complete incident table, and detail/open action |
| Dispatcher — Map View tab | Local MapLibre map, incident markers, impact-radius polygons, and map legend for active/historical incidents |
| Incident Details side panel | Incident information, reporter, location, mini-map, radius, severity, notes, current status, history, and status action buttons |
| Shared dashboard footer | Logged-in dispatcher username and Logout action |

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

- `TASK3-001` A dispatcher can view incidents in a queue and filter by All,
  Reported, Received, Dispatched, or Completed, with each filter showing its
  incident count.
- `TASK3-002` The queue organizes incidents according to preliminary severity
  within the selected status filter and displays the current workflow status.
- `TASK3-003` A dispatcher can open an incident summary containing category,
  volunteer, reported date/time, affected persons, location, barangay,
  landmark, impact radius, notes, severity, and status.
- `TASK3-004` The dashboard displays active and historical incidents on a
  MapLibre map using markers and impact-radius visualization.
- `TASK3-005` A dispatcher can update status only through the forward-only
  Reported, Received, Dispatched, and Completed workflow. Each update requires
  confirmation and a note of at most 300 characters; completion is irreversible.
- `TASK3-006` Every status change records the dispatcher, timestamp, status, and
  required note in `incident_histories`.
- `TASK3-007` The dispatcher can distinguish active incidents from completed or
  archived incidents.
- `TASK3-008` Unauthorized users cannot access dispatcher operations.
- `TASK3-009` Dispatchers can review recent incident notifications from the top
  bar, see the unread count, open an incident while marking its notification
  read, and mark all notifications as read.
- `TASK3-010` Dispatcher select controls use one inline dropdown component with
  consistent styling, viewport-aware menus, and keyboard navigation.
- `TASK3-011` The dispatcher account forms use the available content width, and
  the dashboard footer identifies the signed-in dispatcher by username.
- `TASK3-012` Local map attribution is compact by default and remains expandable.
- `TASK3-013` Dispatchers can manage their own profile and password from My Account;
  Dispatcher-specific reports are not part of the module.
- `TASK3-014` Dispatcher action buttons share status-filter hover feedback, and
  incident rows use View buttons with refined severity/status badges.
- `TASK3-015` Incident Category and Status dropdowns share Barangay hover behavior;
  Filter, Clear, and My Account actions match the incident View button style.

## Implementation Checklist

- [x] `TASK3-001` The dispatcher queue provides counted status filters that
  preserve the selected filter in the URL.
- [x] `TASK3-002` Queue ordering prioritizes severity within each status filter,
  followed by workflow status and report time.
- [x] `TASK3-003` Incident details include the required report, location, severity,
  status, and history information; the focused map shows only that incident.
- [x] `TASK3-004` Local MapLibre map renders database incident markers and
  impact-radius polygons from stored coordinates.
- [x] `TASK3-005` Status updates show only the next forward transition, require
  confirmation and a note (maximum 300 characters), and warn that completion
  cannot be undone.
- [x] `TASK3-006` `incident_histories` stores the dispatcher, timestamp, status,
  and required note for every status change.
- [x] `TASK3-007` Status badges and filters distinguish active and completed
  incidents; archived-record behavior remains coordinated with the admin module.
- [x] `TASK3-008` Dispatcher routes and operations are protected by authentication
  and role middleware.
- [x] `TASK3-009` The notification bell shows an unread count capped at `9+`,
  lists recent incident category, barangay, severity, and relative time, and
  supports opening incidents, marking notifications read, and reduced-motion-
  aware arrival animation.
- [x] `TASK3-010` Incident filters use the shared keyboard-accessible
  dropdown component; the incident barangay filter supports searching options.
- [x] `TASK3-011` Dispatcher account forms use the available content width, and
  the dashboard footer displays the signed-in dispatcher's username.
- [x] `TASK3-012` Map attribution uses a compact expandable control.
- [x] `TASK3-013` Dispatchers use the shared My Account module; Dispatcher Reports
  has been removed while Admin reports remain available.
- [x] `TASK3-014` Dispatcher buttons share status-filter hover feedback; incident
  rows use View buttons and refined severity/status badges.
- [x] `TASK3-015` Incident dropdown hover is consistent across Category, Status,
  and Barangay; Filter, Clear, and My Account buttons use the View style.
- [x] Automated Dispatcher feature tests pass: 5 tests and 24 assertions.
- [x] Manual browser acceptance completed for login, dashboard, queue filters,
  Clear, incident details, status history, local map layers, popup close
  behavior, and map status filters.

## Expected implementation outputs

- Dispatcher routes using the `dispatcher.*` naming prefix.
- Dispatcher controllers, policies, requests/services, and Blade views.
- Views under `resources/views/dispatcher/dashboard/`,
  `resources/views/dispatcher/incidents/`, and map-related partials.
- `dispatcher-map.js` MapLibre asset using the project naming
  conventions.
- Dispatcher notification dropdown assets using the project naming conventions.
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
- Active and historical incidents render correctly on the MapLibre map with
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
  - Frontend: Show counted status filter chips, incident summaries, and filter-preserving pagination. Match dashboard chip hover states to the map filters; show the map's All filter without a dot.
  - Backend: Provide dispatcher-authorized, server-side queue filters and counts.
- **`TASK3-002`** The queue is ordered by preliminary severity and shows workflow status.
  - Frontend: Display severity ordering and current status in each filtered queue.
  - Backend: Preserve severity ordering within each status query.
- **`TASK3-003`** Dispatchers can view complete incident summaries and details.
  - Frontend: Build the incident details view and show only the selected incident on its focused map.
  - Backend: Return all required summary fields.
- **`TASK3-004`** Active and historical incidents are shown on the local MapLibre map.
  - Frontend: Render status-colored markers and impact-radius polygons.
  - Backend: Supply authenticated incident GeoJSON with coordinates and radius values.
- **`TASK3-005`** Workflow status is limited to Reported, Received, Dispatched, and Completed.
  - Frontend: Provide only the four allowed status controls; use the shared
    Dispatcher palette, with blue reserved for the Completed action button.
    Dashboard and map status filters use the same non-blue active and hover styles.
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
- **`TASK3-009`** Dispatchers can review recent incident notifications and manage read state.
  - Frontend: Provide a bell, unread badge, accessible inline dropdown, and reduced-motion-aware new-notification cue.
  - Backend: Return recent notifications and unread count; allow authorized dispatchers to mark one or all as read.
- **`TASK3-010`** Dispatcher filter dropdowns share one inline, keyboard-accessible control.
  - Frontend: Use consistent sizing, Tangelo keyboard focus, neutral open/selected styling, searchable barangay options, and viewport-bounded menus.
  - Backend: Preserve existing filter field names and values.
