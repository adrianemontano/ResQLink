# ResQLink Task 4 - Admin Records and Reports Mock Defense Reviewer

**Owner:** Monique  
**Scope:** Admin incident records and reports  
**Basis:** Current Laravel source, Task 4 documents, migrations, and feature tests

This guide describes the implementation present in `backend/`. It separates
working features from ideas that are not implemented. It does not include
development passwords or claim that a live production database was checked.

## Part 0 - Complete ResQLink Project Overview

This overview combines the project scope described in docs/project-overview.md,
docs/system-requirements.md, docs/feature-requirements.md, docs/tech-stack.md,
docs/project-lifecycle.md, and docs/README.md with the current README and
Laravel source. Requirements and architecture documents also describe planned
work, so the implementation status below is based on the checked-in application.
Where the documents and code differ, the difference is called out.

### A. Project Introduction

1. **What is ResQLink?** ResQLink is a volunteer-based emergency alert and
   dispatch coordination support system for the Cebu City Disaster Risk
   Reduction and Management Office (CCDRRMO). Its full project title is
   “ResQLink: A Volunteer-Based Emergency Alert and Dispatch Coordination
   Support System.”
2. **Main purpose:** Support the Notify-Receive-Dispatch workflow with
   standardized incident reports, a dispatcher workspace, and administrative
   records and summaries.
3. **Problem addressed:** Emergency details may arrive through hotlines, radio,
   social media, messaging, and direct reports. The project documents describe
   reports that can be incomplete, inconsistent, duplicated, or difficult to
   verify, with important details such as location, barangay, landmark, and
   affected population sometimes missing. That can make review and coordination
   slower.
4. **Why it was developed:** The proposal seeks a centralized workflow for
   verified barangay volunteers to submit structured information and for
   dispatchers to review and organize it. The stated aim is to improve
   situational awareness and support coordination. The project is an academic
   prototype; it is not connected to live CCDRRMO systems or other emergency
   agencies.
5. **Target users confirmed by the project:** Admin, Dispatcher, and verified
   Volunteer. The code has these three roles. A general Citizen or anonymous
   public reporter role is not defined; the system does not describe an
   unrestricted public reporting flow.
6. **Flood response support:** A verified volunteer can submit a Flood report
   with a barangay, landmark, affected-person estimate, notes, map-selected
   coordinates, and impact radius. A dispatcher can review the record in a
   queue and on a map, then update its workflow status. Admin can later find
   the stored record and include it in period summaries. ResQLink organizes
   information; it does not automatically dispatch rescuers or guarantee a
   response.
7. **Difference from traditional reporting:** The intended improvement is to
   collect consistent fields in a shared system, display incidents with
   location and status, and preserve status history. This provides an
   organized digital record alongside existing emergency channels; the project
   does not claim to replace official hotlines, radios, or agency procedures.

### B. Project Objectives

The general objective in project-overview.md is to develop a volunteer-based
emergency alert and dispatch coordination support system that improves incident
reporting and supports the Notify-Receive-Dispatch workflow. The specific
objectives and their relationship to the current application are:

| Documented objective | Supporting feature | Current status in this repository |
| --- | --- | --- |
| Develop a mobile application for verified barangay volunteers. | Volunteer login and incident submission are the intended mobile workflow. | Planned. No Flutter application is present; volunteers currently use the Laravel web workflow. |
| Collect category, barangay, landmark, affected-person estimate, location, impact radius, and optional notes. | Validated volunteer incident form and shared incident submission service. | Implemented in the web application. The map pin is selected by the user; this is not device GPS capture. |
| Develop a dispatcher dashboard with map-based incident visualization. | Dispatcher dashboard, queue, and incident map. | Implemented in the Laravel web application using MapLibre GL and local map data. |
| Implement digital incident logging and status management. | Persisted incidents, dispatcher status updates, and incident history. | Implemented. Status transitions are recorded with dispatcher, time, and optional notes. |
| Assess preliminary incident severity to help organize incoming reports. | Severity service uses affected population and impact radius; dispatcher queue sorts by severity. | Implemented as decision support, not an official assessment or an automated dispatch decision. |
| Provide admin tools for volunteer and dispatcher accounts and incident records. | Admin user management, volunteer profile/document handling, incident records, and reports. | Implemented in the web application. |
| Improve situational awareness and dispatch coordination. | Shared incident records, map/queue views, status updates, and admin summaries. | This is the project goal. The software supports the workflow but does not establish or measure real-world response-time improvement. |

### C. Overall System Functionality

#### Confirmed users and responsibilities

| Role | Current responsibilities |
| --- | --- |
| Volunteer | A verified, active Volunteer signs in to the web application and submits structured incident information. A mobile application is planned but is not in the repository. |
| Dispatcher | Reviews incident details, uses the severity-ordered queue and map, and moves incidents through the supported status workflow. |
| Admin | Creates and manages user accounts, reviews volunteer verification/documents, and accesses incident records and reports. Task 4 record pages are read-only. |
| Citizen or general registered user | Not a confirmed role. User accounts have Admin, Dispatcher, or Volunteer roles; public anonymous submission is not implemented. |

#### End-to-end workflow in the current implementation

1. An Admin creates a Volunteer account. Volunteer profile verification and
   active-account state are used to control access. Only an active, verified
   Volunteer is authorized to submit an incident.
2. The Volunteer signs in to the web application and opens the incident form.
   The form collects category, barangay, nearest landmark, affected
   population, coordinates, impact radius, and optional notes. The user clicks
   the map to place or move a pin; the application fills latitude and longitude
   from that selection.
3. Laravel validates the submission. The shared submission service stores the
   incident and associates it with the Volunteer. It sets the initial status
   to Reported, records the report time, and calculates a preliminary severity.
4. The Dispatcher sees active records in the queue, ordered by severity and
   workflow status, and can filter by category, status, or barangay. The
   Dispatcher can also use the map to view records that have coordinates.
5. The Dispatcher reviews a record and may progress it through Reported,
   Received, Dispatched, and Completed. Each accepted status change updates the
   incident and adds a history row with the dispatcher, timestamp, status,
   and optional note. The application records coordination; it does not send a
   rescue vehicle or assign a field responder automatically.
6. Admin can search and filter persisted incidents, open read-only details,
   view any saved history, and generate daily, weekly, or monthly summaries.
   Completed and older incidents remain available as existing records.

The browser form posts through a protected web route. A JSON incident-submission
route also exists, but the Flutter application and its API authentication
integration are not implemented. The workflow above therefore describes the
current web application, not a completed mobile-to-server integration.

### D. Main System Features

| Feature | Purpose and how it works | Roles and connection to other features | Status |
| --- | --- | --- | --- |
| Login, roles, and protected pages | Session-based login accepts username or email and password. Role middleware protects Admin, Dispatcher, and Volunteer routes and checks active status; Volunteer access also requires verification. | All three roles; opens the role-specific dashboard and controls access to the workflows below. | Implemented for the web application. Mobile/API token authentication is outstanding. |
| Admin user and volunteer management | Admin can create and edit accounts, reset passwords, activate/deactivate accounts, verify Volunteers, and upload/view volunteer documents. | Admin manages the accounts used by Volunteers and Dispatchers. | Implemented in the Admin web module. |
| Volunteer incident reporting | A validated web form collects standardized incident fields. The map pin provides coordinates; Laravel stores the incident and reporter relationship. A JSON POST endpoint uses the same submission service. | Verified Volunteer submits; the stored record becomes available to Dispatcher and Admin views. | Implemented in web form/API code; Flutter client and end-to-end mobile integration are planned. |
| Preliminary severity | A service combines population and impact-radius scores to produce Low, Moderate, High, or Critical. The Dispatcher queue sorts by this label. | Calculated during submission and used by Dispatcher review; it does not make the final response decision. | Implemented as preliminary decision support. |
| Dispatcher queue and incident handling | Dispatcher reviews a filtered queue and details, then updates status along the allowed lifecycle. Each transition writes an incident-history row. | Dispatcher works on records created by Volunteers; Admin later reads the records and history. | Implemented in the web module. |
| Incident map | MapLibre GL displays coordinate-bearing incidents as markers and impact-radius shapes, with status filters and local barangay/road/landmark/facility datasets. | Dispatcher uses it to inspect location; Volunteer form uses a map to select a report location. | Implemented, subject to local map style/data availability. |
| Admin incident records and reports | Admin searches and filters stored incidents, reads details/history, and generates period summaries from database rows. Completed records remain available; no separate archive marker is required. | Admin reads the same incident data created by Volunteers and updated by Dispatchers. | Implemented in Task 4; see Parts 1-9 for details and limitations. |

**Planned or unfinished project features:** Flutter volunteer application;
mobile/API authentication and Flutter integration; notification system;
full end-to-end integration verification; formal user acceptance testing and
test reports; complete technical/user documentation and final diagrams; and
final presentation materials. These are listed as remaining work in the
repository README or as later project lifecycle phases. They are not current
features merely because they appear in requirements or the technology plan.

Duplicate detection or grouping is not implemented in the reviewed source.
The project overview identifies duplicate/inconsistent reports as a problem,
but no deduplication, merge, or incident-group workflow was found.

### E. System Architecture and Technology

#### Simple architecture

The current application is one Laravel project. Its web routes serve
server-rendered pages, its controllers validate and coordinate requests, its
services handle incident submission and severity/report calculations, and
Eloquent models read or write MySQL records. Browser JavaScript fetches
GeoJSON for the dispatcher map.

Current flow in plain language:

Volunteer web form -> Laravel validation and submission service -> MySQL
incident record -> Dispatcher queue/details and map -> Dispatcher status
updates and history -> Admin records and reports.

#### Technologies actually present

- **Backend:** PHP 8.3 or newer as required by composer.json, with Laravel.
  The repository README identifies Laravel 13.17.0 from the lock file.
- **Web frontend:** Laravel Blade templates, Vite asset bundling, JavaScript
  modules, and project CSS. Pages are rendered by the Laravel server.
- **Database:** MySQL is the documented project database. Laravel migrations
  define users, roles, volunteer profiles/documents, incidents, status history,
  and reference data.
- **API communication:** There is a protected JSON POST route for incident
  submission and a JSON dispatch-points feed for Admin/Dispatcher. The
  Dispatcher web map also fetches a GeoJSON collection from its web route.
  These routes do not amount to a completed Flutter client integration.
- **Authentication and authorization:** Web login creates a Laravel session.
  Passwords are hashed. The custom role middleware checks role and active
  account state, and requires Volunteers to be verified. The API routes use
  authentication middleware, but a mobile token-authentication flow is listed
  as remaining work.
- **Maps and location:** Current map JavaScript imports MapLibre GL. The
  volunteer chooses a location by clicking or dragging a map marker; the
  chosen coordinates and impact radius are submitted. Dispatcher markers,
  status colors, local GeoJSON layers, and radius shapes are rendered in the
  browser. There is no code for device geolocation or a displayed GPS accuracy
  estimate.
- **Local map dependency:** The map style defaults to a local style URL on
  localhost:8080 and the app loads local GeoJSON assets. A local map service
  and its data must be available for the background and overlays to work.

#### Documentation and source differences

- docs/tech-stack.md describes a Flutter mobile app and Bootstrap 5. The
  repository has no Flutter application and no Bootstrap package; current web
  pages use Blade and custom project styles. The README identifies the mobile
  app as planned.
- docs/tech-stack.md identifies MapLibre GL, which agrees with the current
  map JavaScript. An older README feature bullet says Leaflet, while the
  current dispatcher and volunteer map modules import MapLibre GL. Both
  Leaflet and MapLibre packages are listed in package.json, but the reviewed
  map implementation uses MapLibre.
- The README calls the Admin and Dispatcher dashboard metrics placeholders.
  Current dashboard controllers query incident and user records for the
  displayed totals and active incident list; this summary describes the
  behavior visible in source, not the contents of a live database.
- Requirements describe GPS capture. Current form code derives coordinates
  from a user-selected map pin; it does not call a device GPS/geolocation API.
- Some planning requirements describe generated reports as stored data.
  Task 4 currently calculates report summaries from incident rows when an
  Admin requests them; no generated-report snapshot table is used.

### F. How Task 4 Fits the Project

Task 4 is the Admin-side review and reporting stage after incident information
has entered the shared system. A Volunteer submission creates the incident
record; a Dispatcher may update its status and add history; Task 4 reads those
same persisted records for administrative review.

The Admin uses the records workspace to find an incident by approved saved
fields, narrow results by category or status, open its details, and review
history when history rows exist. The reports summarize incident totals,
category, barangay, and status distributions, and frequency over a selected
period. Completed and historical incidents are useful because they preserve
what was reported and how its status changed. For this scope, they remain
ordinary stored incident rows; there is no separate archive column or archive
workflow.

Task 4 connects to incident submission through the incidents table and
reporter relationships, and to Dispatcher coordination through current status
and incident-history relationships. It is read-only for incident status:
status transitions remain in the Dispatcher module. Reports can support
administrative monitoring and documentation, but they do not choose a response,
dispatch a volunteer, or replace official decision-making.

### G. Project Overview Presentation Script

Maayong adlaw, panel. Our project is ResQLink, a Volunteer-Based Emergency
Alert and Dispatch Coordination Support System. Ang tumong sa project mao ang
pagtabang nga mas organized ang pag-report ug pag-review sa emergency
incidents, especially during events like floods.

Sa kasamtangan, emergency information can come from calls, radio, social
media, and direct reports. Usahay kulang ang important details sama sa exact
location, barangay, landmark, ug pila ka tawo ang apektado. ResQLink proposes a
shared workflow where verified barangay Volunteers submit a structured report,
then Dispatchers review the incident details, location, and status in one
system.

The confirmed users are Admin, Dispatcher, and verified Volunteer. Ang
Volunteer mo-submit sa incident information. The Dispatcher checks the queue
and map, reviews the preliminary severity, and updates the status as the
incident progresses. The Admin manages accounts and can later search incident
records, view saved history, and generate daily, weekly, or monthly summaries.

Our current implementation is a Laravel web application connected to a
database. Volunteers currently use the web reporting form; ang Flutter mobile
app ug mobile integration kay planned pa, dili pa part sa current repository.
The map pin records the location selected by the user, so we do not claim
automatic phone GPS accuracy. ResQLink also does not automatically dispatch
rescuers or connect to live emergency agencies.

The main features already in the web application include role-protected login,
Admin account management, volunteer incident reporting, preliminary severity,
Dispatcher queue and map, status history, and Admin records and reports. My
assigned part is Task 4. Kini nga module maoy Admin workspace para makapangita
sa stored incidents, makatan-aw sa completed ug historical records, ug
makasusi sa summaries. With that project context, I will now explain Task 4 in
more detail.

### H. Possible Project-Level Panel Questions

#### 1. What is the purpose of ResQLink?

It supports structured incident reporting and the Notify-Receive-Dispatch
workflow. Gusto namo nga mas sayon i-review ang incident details ug status.

#### 2. What problem is the project addressing?

Reports can arrive through separate channels and may not consistently include
location, barangay, landmark, or affected-person information. ResQLink provides
a standard report and shared record for review.

#### 3. Who are the intended users?

The confirmed roles are Admin, Dispatcher, and verified Volunteer. Wala mi
mag-claim ug public Citizen account or anonymous reporting role.

#### 4. Why require verified Volunteers?

The design focuses on reports from known barangay Volunteers. In the current
code, an active Volunteer must also have verified profile status to access the
volunteer workflow.

#### 5. Can you explain the overall response workflow?

A verified Volunteer submits an incident; Laravel stores it; the Dispatcher
reviews it in the queue or map and updates its status; Admin later reviews
records and reports. Mao ni ang software workflow; dili automatic ang actual
rescue dispatch.

#### 6. What does each role do?

Volunteer submits reports, Dispatcher reviews and updates incident status, and
Admin manages accounts and reviews records/reports. Role middleware protects
their pages.

#### 7. Is a Citizen allowed to submit an incident?

There is no Citizen role or open public form in the current source. The
reporting workflow is restricted to active, verified Volunteers.

#### 8. What architecture does ResQLink use?

It is currently a Laravel application: Blade pages call Laravel routes and
controllers, services handle shared calculations, Eloquent accesses the
database, and browser JavaScript loads map data. Simple ra ang flow:
browser-to-Laravel-to-database.

#### 9. What technologies are in the running web application?

PHP and Laravel on the backend, Blade and JavaScript with Vite on the web
frontend, MySQL as the documented database, and MapLibre GL in the current map
code. The mobile Flutter app is planned.

#### 10. Is the mobile application complete?

No. The repository README identifies it as remaining work. Karon, Volunteer
reporting is available in the web application; Flutter client and integration
are not implemented.

#### 11. Does ResQLink use GPS?

The report stores latitude and longitude from the map selection. Sa current
code, ang user mopili sa location pinaagi sa pag-click o pag-drag sa pin; we
did not find device GPS capture or a GPS accuracy display.

#### 12. How accurate is the incident location?

It reflects the point selected on the map and the available local map data.
We cannot claim device-level GPS accuracy or a measured error radius. The
reporter should verify the pin and location details.

#### 13. What map technology is actually used?

The current JavaScript modules import MapLibre GL and use local map style/data.
An older README bullet mentions Leaflet and the package file contains both
dependencies, but the reviewed map implementation uses MapLibre.

#### 14. Does the system detect or group duplicate alerts?

No duplicate-detection or merge feature appears in the current implementation.
The project documents duplicates as a possible reporting problem, but the app
does not automatically identify or group them.

#### 15. How is severity calculated?

The submission service uses the affected-population and impact-radius values
to calculate a preliminary Low, Moderate, High, or Critical label. This helps
order Dispatcher review; dili kini official assessment ug dili kini automatic
dispatch decision.

#### 16. Does ResQLink dispatch rescue resources automatically?

No. Dispatchers update incident status and coordinate the workflow. There is
no automatic vehicle assignment, route optimization, or responder tracking.

#### 17. How does Task 4 help the whole system?

Task 4 gives Admin a read-only way to find shared incident records, inspect
saved status history, and review period summaries. It helps documentation and
monitoring after reports enter the system.

#### 18. Are completed incidents deleted or separately archived?

No. Completed and historical incidents remain stored and are available to
Admin. For this implementation, archived means those existing records; there
is no separate archive table, marker, or workflow.

#### 19. Is ResQLink connected to live CCDRRMO or other agencies?

No. The project overview describes a prototype and explicitly excludes live
agency integration. Dili mi moingon nga connected na kini sa real emergency
dispatch systems.

#### 20. What are the main limitations today?

The Flutter app and mobile integration, notifications, duplicate detection,
live agency integration, automated dispatch, formal UAT, and complete final
documentation are not finished. Location is user-selected on the map, and
reports depend on data already saved in the database.

#### 21. Why do the documents mention Bootstrap and mobile when they are absent?

The technology and requirements documents describe the planned target
architecture. The current repository README and source show the implementation
status: custom-styled Blade pages and web-based Volunteer reporting, with the
mobile client still planned.

## Part 1 - Task 4 Overview

Task 4 gives an Admin a read-only place to review incident records already
stored in ResQLink and view summaries of incidents. The Admin can search the
records, filter by category or status, open details and status history, and
view daily, weekly, or monthly reports.

ResQLink supports structured reporting by verified volunteers and coordination
by dispatchers. During emergencies, information can arrive with different
levels of detail. A searchable list helps the Admin locate stored information;
reports summarize counts by category, barangay, status, and date.

For a flood response, for example, the Admin can search/filter Flood incidents,
review their barangays and report times, and see how many were recorded during
a selected period. This supports record keeping and review. Task 4 does not
dispatch responders or decide how resources should be deployed. Dispatcher
status changes remain in the Dispatcher workflow.

For this implementation, **archived** means a completed or older incident
already stored in the database. The records list includes all persisted
incidents. There is no separate archive marker, archive table, or archive
workflow.

## Part 2 - Implemented Admin Features

### 2.1 Admin Dashboard summary

1. **Feature:** Admin Dashboard summary cards.
2. **Purpose:** Show counts of volunteers, dispatchers, and incidents.
3. **How it works:** `DashboardController::__invoke()` counts users by role and
   incident rows, then passes the values to a Blade view.
4. **Workflow:** Admin signs in; ResQLink opens the Admin Dashboard; the Admin
   sees the three totals and chooses Incident Records or Reports in the sidebar.
5. **Files/functions:** `backend/routes/web.php` (`admin.dashboard`),
   `backend/app/Http/Controllers/Admin/DashboardController.php`
   (`__invoke()`), `backend/resources/views/admin/dashboard.blade.php`.
6. **Database:** `users`, `roles`, `incidents`.
7. **Permission:** Active Admin only.
8. **Output:** Total Volunteers, Total Dispatchers, Total Incidents, and a
   Create User link.
9. **Status:** Implemented as a data-backed overview. It is not the detailed
   reporting page; account creation belongs to user management.

### 2.2 Incident records, search, filters, and pagination

1. **Feature:** Admin Incident Records list.
2. **Purpose:** Find and review incidents already persisted in the database.
3. **How it works:** `IncidentController::index()` validates search/filter
   inputs, queries incidents and reporter relationships, orders by report time
   and ID, and paginates 15 rows. Search covers numeric or `INC-####` IDs,
   reporter/volunteer name, email, username, category, barangay, landmark,
   affected population, coordinates, radius, reported date, notes, status, and
   severity. Category/status filters use the stored incident values. Page
   links preserve filters.
4. **Workflow:** Open Incident Records; enter a search term and/or select a
   category/status; apply filters; review results; select View; use Clear
   filters or pagination as needed.
5. **Files/functions:** `backend/routes/web.php`
   (`admin.incidents.index`),
   `backend/app/Http/Controllers/Admin/IncidentController.php`
   (`index()`, `applySearch()`, `incidentIdentifier()`),
   `backend/resources/views/admin/incidents/index.blade.php`.
6. **Database:** `incidents`, `users`; the `reporter` and `volunteer`
   relationships provide the reporter identity.
7. **Permission:** Active Admin only. Other roles are denied by middleware.
8. **Output:** Identifier, reporter, category, affected persons, barangay,
   landmark, report time, status, severity, and a View link. An empty result
   displays a no-matches message.
9. **Status:** Implemented. Completed and older incidents are in the same
   all-records list; there is no separate Archive page.

### 2.3 Read-only incident details and status history

1. **Feature:** Admin Incident Record details.
2. **Purpose:** Show a stored report and its available Dispatcher history
   without allowing the Admin to change status.
3. **How it works:** `IncidentController::show()` loads reporter, volunteer,
   history, history status, and history dispatcher relationships. The view
   displays incident fields and each history row. Reporter display falls back
   from `reported_by` to `volunteer_id`. An incident with no history rows shows
   a no-history message.
4. **Workflow:** Select View from a row; review the incident; read available
   history; choose Back to records. No status update control is shown.
5. **Files/functions:** `backend/routes/web.php`
   (`admin.incidents.show`),
   `backend/app/Http/Controllers/Admin/IncidentController.php` (`show()`),
   `backend/app/Models/Incident.php` (`reporter()`, `volunteer()`, `history()`),
   `backend/app/Models/IncidentHistory.php` (`dispatcher()`, `status()`),
   `backend/resources/views/admin/incidents/show.blade.php`.
6. **Database:** `incidents`, `users`, `incident_histories`,
   `incident_statuses`.
7. **Permission:** Active Admin only; view is read-only.
8. **Output:** Reporter, category, barangay, landmark, affected persons,
   coordinates, impact radius, preliminary severity, status, reported time,
   notes, and history actor/status/time/optional note where recorded.
9. **Status:** Implemented. Completed and older incidents are accessible from
   the same list; history appears when rows exist.

### 2.4 Daily, weekly, and monthly reports

1. **Feature:** Admin incident reports.
2. **Purpose:** Summarize persisted incidents for a selected day, week, or
   month.
3. **How it works:** `ReportController::index()` validates `period` and `date`,
   then calls `AdminIncidentReportService::generate()`. The service scopes
   database queries and calculates a total, distributions by category,
   barangay, and status, and frequency by calendar date. Missing frequency
   dates receive zero. Legacy rows without `reported_at` use `created_at`.
4. **Workflow:** Open Reports; choose Daily, Weekly, or Monthly; choose a date;
   select Generate report; review totals and distributions. Empty periods show
   a zero total and an explicit no-data message.
5. **Files/functions:** `backend/routes/web.php` (`admin.reports.index`),
   `backend/app/Http/Controllers/Admin/ReportController.php` (`index()`),
   `backend/app/Services/AdminIncidentReportService.php` (`generate()`,
   `periodBounds()`, `incidentScope()`, `distribution()`, `frequency()`),
   `backend/resources/views/admin/reports/index.blade.php`.
6. **Database:** `incidents`; the report reads persisted category, barangay,
   status, and timestamp fields. Generated summaries are not saved in a
   separate report table.
7. **Permission:** Active Admin only.
8. **Output:** Total; category/status bar rows with counts and percentages;
   barangay counts; frequency counts per date.
9. **Status:** Implemented. No status is excluded, so Completed incidents are
   counted when their report time is inside the period. PDF/export is not
   implemented.

### 2.5 Admin navigation and access protection

1. **Feature:** Admin-only Task 4 navigation and route authorization.
2. **Purpose:** Restrict records and reports to the Admin role.
3. **How it works:** The Admin sidebar displays Incident Records and Reports.
   Routes are grouped under `auth` and `role:admin`. The role alias points to
   `EnsureUserHasRole`, which checks active state and role.
4. **Workflow:** Admin signs in; the login controller redirects to
   `admin.dashboard`; the Admin selects a Task 4 sidebar link. Logout is in the
   shared dashboard footer.
5. **Files/functions:** `backend/routes/web.php`, `backend/bootstrap/app.php`
   (role alias), `backend/app/Http/Middleware/EnsureUserHasRole.php`
   (`handle()`), `backend/app/Http/Controllers/Auth/LoginController.php`
   (`store()`, `redirectPathFor()`),
   `backend/resources/views/layouts/partials/sidebar-nav.blade.php`,
   `backend/resources/views/layouts/dashboard.blade.php`.
6. **Database:** `users`, `roles`.
7. **Permission:** Active Admin only. Tests check Dispatcher and Volunteer
   denial for the list, detail, and report routes.
8. **Output:** Admin can open Task 4 pages; non-admin users receive a forbidden
   response.
9. **Status:** Implemented and feature-tested.

## Part 3 - Technical Implementation

### Frontend components

The Admin interface is rendered by Laravel Blade. The shared HTML frame and
static CSS links are in `backend/resources/views/layouts/app.blade.php`.
Dashboard shell/sidebar/footer are in
`backend/resources/views/layouts/dashboard.blade.php` and
`backend/resources/views/layouts/partials/sidebar-nav.blade.php`.

- `admin/incidents/index.blade.php`: search/filter form, table, empty state,
  result count, and pagination.
- `admin/incidents/show.blade.php`: incident detail and read-only history.
- `admin/reports/index.blade.php`: period/date controls, total, category/status
  bar rows, barangay counts, date frequency, empty state.

Task 4 forms use ordinary GET requests. They do not use a dedicated JavaScript
application or a Task 4 API.

### Backend and routes

The web router calls the Admin controllers. Incident pages query Eloquent
models and return Blade views. The report controller validates the period/date
and delegates calculations to `AdminIncidentReportService`. The forms only
read and display; they do not mutate incidents.

| Method | Path | Route name | Purpose |
| --- | --- | --- | --- |
| GET | `/admin/dashboard` | `admin.dashboard` | Admin count summary |
| GET | `/admin/incidents` | `admin.incidents.index` | Search/filter/list |
| GET | `/admin/incidents/{incident}` | `admin.incidents.show` | Read-only detail/history |
| GET | `/admin/reports` | `admin.reports.index` | Period report |

Every route is inside the `auth` and `role:admin` group in
`backend/routes/web.php`.

### API endpoints and boundaries

There is no Task 4-specific API endpoint. The Admin records/reports UI uses the
web routes above. Other APIs are separate workflows:

- `POST /api/incidents` - incident submission, implemented by
  `App\Http\Controllers\Api\IncidentController::store()`.
- `GET /api/dispatch-points` - map-point feed for Admin/Dispatcher access,
  implemented by `App\Http\Controllers\Api\DispatchPointController::index()`.
- `GET /dispatcher/map/incidents` - Dispatcher map feed, implemented by
  `Dispatcher\IncidentController::mapIncidents()`.

Task 4 does not call or change these endpoints.

### Database tables and relationships

| Table/model | Task 4 use |
| --- | --- |
| `users` / `User` | Admin access, reporter/volunteer identity, history actor |
| `roles` / `Role` | Identifies the Admin role |
| `incidents` / `Incident` | Primary records and persisted report fields |
| `incident_histories` / `IncidentHistory` | Stored status transitions, actor, time, notes |
| `incident_statuses` / `IncidentStatus` | Status labels for history entries |
| `incident_categories` | Category reference data; incident text is also stored on `incidents` |
| `barangays` | Barangay reference data; reports read the incident's stored barangay |
| `severity_levels` | Severity reference data; severity text is also stored on `incidents` |

`Incident::reporter()` uses `reported_by`; `Incident::volunteer()` uses
`volunteer_id`; `Incident::history()` returns history ordered by `changed_at`,
newest first. `IncidentHistory` belongs to an incident, status, and user via
`changed_by`. `User::role()` links a user to `roles`.

There is no archive table/column and no table storing generated reports.
Reports are calculated from incident rows when requested.

### Report rules

- Inputs: `period=daily|weekly|monthly`, `date=YYYY-MM-DD`.
- Default date: today in `config('app.timezone')`; checked-in config is UTC.
- Daily: midnight to next midnight.
- Weekly: Monday midnight to following Monday midnight.
- Monthly: first day at midnight to first day of next month.
- Windows include `from` and exclude `until` (`[from, until)`).
- Frequency is per calendar date, with missing dates shown as zero.
- If `reported_at` is null, `created_at` is used for period inclusion and date
  grouping.
- Reports do not filter out statuses. Completed/historical incidents are
  counted when their date is in the selected period.

### Access control

`auth` requires a signed-in user. `role:admin` then checks active account state
and Admin role before a controller runs. Sidebar visibility helps navigation;
server-side middleware is the actual route protection. Task 4 records routes
are GET-only, so there is no Admin status mutation action.

## Part 4 - Admin Workflow

1. Admin opens `/login` and submits username/email and password.
2. `LoginController::store()` checks credentials, active state, and web access.
   A valid Admin session redirects to `/admin/dashboard`.
3. Dashboard shows totals for volunteers, dispatchers, and all incidents. It is
   a quick overview, not detailed report analytics.
4. Admin selects **Incident Records**. The page loads persisted incidents,
   ordered by report time and ID, with 15 rows per page.
5. Admin searches, selects category/status filters, applies or clears them,
   and uses pagination as needed. Completed and older incidents are in this
   same list; there is no separate Archive page.
6. Admin selects **View** on a row. The detail page shows the stored report and
   available history. It does not show status controls.
7. Admin selects **Reports**, chooses a period/date, and selects **Generate
   report**. The page queries persisted data; completed incidents are included
   if they fall within the report window.
8. Admin uses **Logout** to end the session.

Dispatcher status changes use Dispatcher routes and are outside this Admin
workflow. Task 4 reads the stored status/history only.

## Part 5 - Mock Defense Presentation Script

### 1. Introduction to Task 4

Maayong adlaw, panel. I will present Task 4, the Admin Records and Reports
module of ResQLink. This module helps the Admin review incidents already stored
in the system and see summaries by period.

### 2. Purpose of Admin Records and Reports

During an emergency, daghan ug impormasyon ang kinahanglan balikan: unsa nga
incident, asa nga barangay, kanus-a gi-report, ug unsa na ang status. Task 4
puts those stored details in one searchable page. The reports help the Admin
review counts by category, barangay, status, and date. For example, makatabang
ni sa pag-review sa Flood incidents in a selected week. The report supports
review; it does not dispatch responders.

### 3. Explanation of implemented features

The Incident Records page shows saved incidents in a table. Admin can search
by ID, reporter, category, barangay, landmark, and other saved fields. Pwede
usab i-filter by category ug status. There are 15 records per page.

When I open an incident, makita ang saved report details and any status
history. Read-only ni nga Admin view; walay button dinhi para ilisan ang
status. Completed and older incidents remain in the same list. Mao na ang
archived records for our approved Task 4 scope; no separate archive workflow
was added.

On the Reports page, Admin chooses Daily, Weekly, or Monthly and a date. The
page shows the total, category/status bars, barangay counts, and frequency by
date. Kung walay incidents sa selected period, zero ang total and naa'y
no-data message.

### 4. Step-by-step Admin system demonstration

I will sign in with an authorized Admin account. Human sa login, makita ang
Admin Dashboard with counts for volunteers, dispatchers, and incidents. From
the sidebar, I open Incident Records. I can search or filter, then open a row
to show its details and available history.

Next, I open Reports, choose a period and date, and generate the summary. Ang
values gikan sa persisted database records. If time permits, I will also show
an empty period so the panel can see the zero total and empty-state message.

### 5. Technical explanation

Laravel routes the page requests to Admin controllers. The controllers use
Eloquent to read the `incidents` table and related users/history. The report
controller delegates date-window calculations to
`AdminIncidentReportService`. Blade renders the result as HTML. Ang access
protection naa sa server-side `auth` ug `role:admin` middleware, so dili igo
nga taguon lang ang sidebar link.

The current application timezone is UTC. Weekly reports start Monday. Report
windows include their start and exclude their end. Wala'y separate report
table; the summary is calculated when the Admin opens Reports.

### 6. Importance of Task 4 to ResQLink

Task 4 makes stored incident details easier to retrieve and summarize. It
supports the overall Notify-Receive-Dispatch workflow by giving the Admin an
organized record view and period summaries. Actual response decisions remain
with the Dispatcher.

### 7. Closing statement

To summarize, Task 4 provides a protected, read-only Admin workspace for
incident records and database-based reports. Completed and historical
incidents are included using existing data and statuses. Wala mi nagdugang ug
archive table or workflow. Salamat, panel. I am ready for your questions.

## Part 6 - Live System Demonstration Script

Use an authorized Admin account and a database with incident rows. Avoid
displaying account passwords during the presentation.

| Step | Click/open | What should appear | What to say | Background behavior |
| --- | --- | --- | --- | --- |
| 1 | Open `/login`; sign in as Admin | Admin Dashboard | "Mag-login ko as Admin. Admin ra ang maka-access sa records ug reports." | Login checks credentials, active state, and web access, then redirects by role. |
| 2 | Review dashboard cards | Volunteer, dispatcher, incident totals | "Quick totals ni; ang detailed summaries naa sa Reports." | Dashboard controller counts users by role and incident rows. |
| 3 | Select **Incident Records** | Search/filter form and records table | "Apil diri ang persisted records, including completed and historical incidents." | GET `/admin/incidents` queries records, reporter relationships, and pagination. |
| 4 | Search for an ID, reporter, barangay, or other field | Matching rows or no-results message | "The search checks saved incident and reporter fields." | `applySearch()` adds query conditions to the persisted query. |
| 5 | Select category/status; apply or clear | Filtered rows or complete list | "Pwede i-combine ang category ug status filters." | Controller filters persisted values and retains them in pagination URLs. |
| 6 | Select **View** for a completed/older row | Read-only details and available history | "Walay status controls dinhi; makita ang history kung naa'y saved history rows." | `show()` loads incident, reporter/volunteer, status history, actor, and status. |
| 7 | Select **Back to records** | Incident list | "Mobalik ko without changing the incident." | GET request returns to the list; no incident mutation occurs. |
| 8 | Select **Reports** | Period/date controls and report summary | "Pili ko sa period ug date nga akong i-review." | GET `/admin/reports` validates parameters and calls the report service. |
| 9 | Choose Daily/Weekly/Monthly and date; select **Generate report** | Totals and distributions | "This summary comes from stored incidents. Apil ang Completed if within this period." | Service scopes `[from, until)`, aggregates rows, and zero-fills frequency dates. |
| 10 | If available, choose a period without incidents | Zero total and no-data message | "Kung walay records, zero ang total; wala mi nag-fabricate ug count." | Empty query returns zero totals and empty distributions. |
| 11 | Select **Logout** | Login page | "Mag-logout ko para ma-end ang Admin session." | Logout invalidates the session and regenerates the CSRF token. |

If a completed example or history row is not present in the demo database,
explain the implemented behavior without claiming a particular row exists.

## Part 7 - Possible Panel Questions and Answers

### 1. Why is Task 4 needed?

It gives the Admin a searchable place to review stored incidents and summaries.
Dili na kinahanglan mangita manually across separate records.

### 2. What is the Admin's role here?

The Admin reviews read-only records and reports. Ang pag-usab sa incident
status kay Dispatcher responsibility.

### 3. Can an Admin edit an incident from this workspace?

No. The Task 4 records/detail pages are read-only. Walay incident edit or
status-update form dinhi.

### 4. What does archived mean in this implementation?

Completed or older incidents that remain stored in the database. We show them
in the same records list and include them in date-scoped reports.

### 5. Is there a separate Archive page?

No. All persisted incidents are in Incident Records and the status filter can
narrow the list. Wala'y separate archive workflow in this scope.

### 6. What fields can the Admin search?

ID, reporter/volunteer identity, category, barangay, landmark, affected
population, coordinates, radius, report date, notes, status, and severity.

### 7. Which filters are available?

Category and workflow status. They can be used together or cleared.

### 8. How many records appear per page?

Fifteen. The current search and filters remain in pagination links.

### 9. How does the page find the reporter?

It loads `reporter` through `reported_by` and falls back to `volunteer` through
`volunteer_id` when needed.

### 10. What does status history show?

Available rows show status, actor/dispatcher, timestamp, and optional note.
Kung walay history row, the page says there is no recorded history.

### 11. Does Task 4 create status history?

No. Task 3 records a transition when the Dispatcher changes status. Task 4 only
reads and displays the history.

### 12. How are report periods defined?

Daily is midnight to next midnight; weekly is Monday to the following Monday;
monthly is the first of the month to the first of the next month. Start is
included, end excluded.

### 13. Which timezone is used?

The service uses `config('app.timezone')`. The current app config is UTC, and
the page displays that timezone and the window.

### 14. What if `reported_at` is null?

The report query uses `created_at` as a legacy fallback so the incident can
still be scoped to a period.

### 15. Are Completed incidents included in reports?

Yes. The report query does not filter statuses. Apil ang Completed if its
report date falls within the selected period.

### 16. What is incident frequency?

The count per calendar date. Dates without incidents are shown as zero.

### 17. Are generated reports saved?

No. `AdminIncidentReportService` calculates them when requested. Wala'y
generated-report table in this Task 4 implementation.

### 18. Can the Admin export PDF or Excel?

Not currently. Reports display on screen. Export is a possible future
improvement, not a completed feature.

### 19. Is there a Task 4 API?

No dedicated API. The Admin pages use web routes and Blade. `POST /api/incidents`
is incident submission; `GET /api/dispatch-points` is a map feed. Dili sila ang
Task 4 reporting interface.

### 20. How is Admin access protected?

Routes use `auth` and `role:admin`. `EnsureUserHasRole` checks active state and
role on the server, not only in the sidebar.

### 21. Can a Dispatcher open an Admin URL directly?

No. The middleware denies non-Admin roles even when they type the URL. Tests
cover Dispatcher and Volunteer denial for all Task 4 routes.

### 22. Which tables support Task 4?

Main tables include `incidents`, `users`, `roles`, `incident_histories`, and
`incident_statuses`, plus category, barangay, and severity reference tables.
Walay archive or generated-report table.

### 23. How does Task 4 depend on Tasks 2 and 3?

Task 2 provides incident fields/reporter data. Task 3 writes status history.
Task 4 reads and summarizes those values without changing the other workflows.

### 24. Does Task 4 calculate severity?

No. Severity is stored by the incident-submission workflow. Task 4 displays
the preliminary severity; dili ni official emergency assessment.

### 25. What appears on the Admin Dashboard?

Counts of volunteers, dispatchers, and incidents, plus a Create User link.
Detailed incident summaries are on Reports.

### 26. What happens for an empty period?

The page displays zero total, empty distributions, zero frequency by date, and
a clear no-data message.

### 27. How do you know reports use database records?

The service queries persisted `incidents` for the selected window and groups
those rows. Feature tests create records and assert report values.

### 28. Is history shown oldest first?

The `Incident::history()` relation orders by `changed_at` newest first. If no
history exists, the page shows a no-history message.

### 29. What are the current limitations?

No export, saved report snapshot, separate archive workflow, or Task 4 API.
Reports use fixed calendar periods and depend on stored incident data.

### 30. What testing evidence is documented?

The last documented focused run reports 10 Task 4 tests and 73 assertions
passing. The last documented full suite had 23 passing, 2 failures, and 1
error in Task 1/Task 3 tests. I would report those results honestly.

### 31. What could be improved later?

If approved, we could consider export, custom date ranges, or saved report
snapshots. Future improvements pa na; dili sila current features.

## Part 8 - Limitations and Future Improvements

### Current limitations

- No archive field or workflow exists. Completed and historical rows are
  treated as archived and remain in the all-records list.
- Reports display on the page; PDF, spreadsheet export, and saved snapshots are
  not implemented.
- Periods are daily, Monday-based weekly, and monthly; there is no custom
  start/end range.
- Timezone follows application config, currently UTC.
- Frequency is grouped by calendar date, not hour.
- History displays only when `incident_histories` rows exist; the Admin page
  does not invent missing history.
- There is no Admin-specific report API.
- Accuracy depends on values already persisted. Legacy null `reported_at`
  values use `created_at`.
- Last documented full-suite run had Task 1/Task 3 failures, while the focused
  Task 4 suite was documented as passing.

### Honest panel responses

For export: "Dili pa siya implemented. Karon, on-screen report pa ang naa."

For archive: "Completed and historical records are the archived records for
this scope. They use existing statuses and rows; wala mi nagdugang ug archive
column or workflow."

For automatic dispatch: "Task 4 does not assign or dispatch responders. It
provides Admin records and summaries; Dispatcher operations handle status."

For missing history: "The page displays history rows that exist. If none exist,
it shows a no-history message rather than inventing an event."

### Possible future improvements

These are suggestions only, not current functionality:

- Add approved PDF/CSV export for a displayed report.
- Add an approved custom date range if reporting requirements change.
- Add more trend comparisons after definitions are agreed.

## Part 9 - Quick Reviewer

### Key features

- Protected Admin-only Incident Records and Reports.
- Search, category/status filters, 15-row pagination, read-only details/history.
- Completed and historical persisted incidents are available in the same list
  and count in reports by their report date.
- Daily, Monday-based weekly, monthly report totals and distributions.
- Empty periods display zero totals and an explicit message.
- Admin Dashboard shows volunteer, dispatcher, and incident counts.

### Important terms

| Term | Simple meaning |
| --- | --- |
| Blade | Laravel template that renders HTML on the server |
| Eloquent | Laravel model/query layer for database data |
| `reported_at` | Time used to scope an incident report; legacy fallback is `created_at` |
| `incident_histories` | Stored status transition, actor, time, and note rows |
| Middleware | Server-side checks before a controller runs |
| `[from, until)` | Include start; stop before end |
| Frequency | Incident count per calendar date |

### Tables, routes, and files

- Tables: `incidents`, `users`, `roles`, `incident_histories`,
  `incident_statuses`, `incident_categories`, `barangays`, `severity_levels`.
- Routes: `GET /admin/dashboard`, `GET /admin/incidents`,
  `GET /admin/incidents/{incident}`, `GET /admin/reports`.
- Other existing endpoints, outside Task 4: `POST /api/incidents`,
  `GET /api/dispatch-points`.
- `backend/routes/web.php` - route groups and names.
- `backend/app/Http/Controllers/Admin/IncidentController.php` - list, detail,
  search.
- `backend/app/Http/Controllers/Admin/ReportController.php` - report input.
- `backend/app/Services/AdminIncidentReportService.php` - date scopes and
  aggregates.
- `backend/app/Models/Incident.php` and `IncidentHistory.php` - relationships.
- `backend/app/Http/Middleware/EnsureUserHasRole.php` - active/role check.
- `backend/resources/views/admin/incidents/index.blade.php` - records list.
- `backend/resources/views/admin/incidents/show.blade.php` - detail/history.
- `backend/resources/views/admin/reports/index.blade.php` - report output.
- `backend/tests/Feature/AdminRecordsAndReportsTest.php` - feature coverage.

### Short answers to memorize

- **Why Task 4?** It helps Admin find records and review summaries.
- **Can Admin change status?** No; the records workspace is read-only.
- **What is archived?** Completed and historical incidents already stored.
- **How are reports generated?** Queries aggregate persisted rows by period.
- **What if there is no data?** Zero totals and a clear empty-state message.
- **How is access protected?** Server-side `auth` and `role:admin` middleware.
- **Is there PDF export?** Not yet; reports display on screen.
- **Does Task 4 change Dispatcher history?** No; it only reads it.
- **Does Task 4 have an API?** No dedicated Task 4 API; it uses web routes.
