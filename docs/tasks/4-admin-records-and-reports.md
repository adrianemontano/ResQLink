# Task 4 — Admin Incident Records and Reports

## Seeder Credentials — Read First

The current `DatabaseSeeder` creates the roles `admin`, `dispatcher`, and
`volunteer`, but only the default admin user is seeded:

| Role | Username | Email | Password |
| --- | --- | --- | --- |
| Admin | `admin` | `admin@resqlink.local` | `Admin@12345` |

These are development credentials only. The admin values can be overridden with
`DEFAULT_ADMIN_USERNAME`, `DEFAULT_ADMIN_EMAIL`, and `DEFAULT_ADMIN_PASSWORD`.

**Assigned owner:** Monique
**Primary system user:** Admin / Data Management Personnel  
**Platform:** Laravel Blade, Bootstrap 5, MySQL, and Laravel report logic  
**Priority:** Must

## Local map requirement

This task must consume incident location data from the local MySQL database and
must not add remote map, tile, geocoding, or CDN dependencies to admin pages or
report views. Administrative records and reports must continue to work without
internet access.

## Required pages and visibility

The page structure follows the Admin Control Panel in
`html/resqgroup.html`:

| Page or UI location | Required content |
| --- | --- |
| Admin sidebar | `User Management`, `Incident Records`, and `Reports` navigation items; visible only to Admin |
| Admin — Incident Records tab | Search field, category filter, status filter, archived/history access, and incident table |
| Admin — Incident Records table | Incident ID, reporter, category, affected persons, barangay, landmark, reported time, status, severity, and view action |
| Admin — Incident detail view | Full incident data and read-only status/history information; no dispatcher status controls |
| Admin — Reports tab | Daily/weekly/monthly period selector and report summary area |
| Admin — Reports summary cards | Total incidents and status/frequency totals for the selected period |
| Admin — Reports charts/tables | Incident distribution by category, barangay, status, and frequency statistics |
| Shared dashboard footer | Logged-in admin identity and Logout action |

The HTML reference presents `Incident Records` and `Reports` as Admin sidebar
tabs. The Laravel implementation may split them into resource pages, provided
the same navigation labels, filters, tables, and report summaries remain easy
to find.

## System requirements covered

- Incident Data Access.
- Report Generation.
- Admin Dashboard requirements.
- Archived incident access.
- Laravel report generation and MySQL data aggregation.

## Scope

The owner will implement the administrative incident-record workspace and
periodic reporting. User account and volunteer-document management belongs to
Task 1; dispatcher operations belong to Task 3.

## Functional requirements

- `TASK4-001` An admin can view incident records.
- `TASK4-002` An admin can search incident records using relevant incident
  fields.
- `TASK4-003` An admin can filter records by incident category and workflow
  status.
- `TASK4-004` An admin can access archived and historical incidents.
- `TASK4-005` The system can generate daily incident reports.
- `TASK4-006` The system can generate weekly incident reports.
- `TASK4-007` The system can generate monthly incident reports.
- `TASK4-008` Reports include incident distribution by category, distribution by
  barangay, status summaries, and incident frequency statistics.
- `TASK4-009` Report results use persisted incident data and respect the
  selected reporting period.
- `TASK4-010` Unauthorized users cannot access admin records or reports.

## Expected implementation outputs

- Admin incident-record routes using `admin.incidents.*` naming.
- Admin report routes using `admin.reports.*` naming.
- Controllers, query/service classes, requests, policies, and Blade views.
- Views under `resources/views/admin/incidents/` and
  `resources/views/admin/reports/`.
- Reusable filters and report summary partials.

## Dependencies and handoff

Depends on Task 2 for incident fields and Task 3 for status history and archive
behavior. Coordinate report definitions with the database owner and avoid
duplicating dispatcher queue logic.

## Acceptance criteria

The feature is complete when:

- An admin can view, search, and filter incident records by category and status.
- Archived and historical records can be accessed without changing their data.
- Daily, weekly, and monthly report periods produce the correct scoped results.
- Reports include category distribution, barangay distribution, status summary,
  and frequency statistics.
- Empty periods display a clear empty state rather than incorrect totals.
- Admin-only pages and actions reject unauthorized users.
- Report behavior and field definitions are documented.

## Task folder label

This document belongs to **Task 4 — Admin Incident Records and Reports**.

## Implementation note — TASK4

Admin records now support persisted search, category/status filters, pagination,
read-only detail/history access, and completed historical records. Reports
support daily, weekly, and monthly date scopes with category, barangay, status,
and date-frequency aggregations. Empty periods return zero totals and an
explicit empty state. Coverage is defined in
backend/tests/Feature/AdminRecordsAndReportsTest.php.

## Frontend/backend split

The split below defines the concrete interface and server responsibilities for
each requirement; no requirements are added.

- **`TASK4-001`** Admins can access persisted incident records.
  - Frontend: Build the admin incident-records view.
  - Backend: Provide admin-authorized record access.
- **`TASK4-002`** Admins can search records by relevant fields.
  - Frontend: Provide search controls and matching results.
  - Backend: Search persisted records by approved fields.
- **`TASK4-003`** Admins can filter records by category and workflow status.
  - Frontend: Provide category and status filters.
  - Backend: Apply validated filters to persisted records.
- **`TASK4-004`** Admins can retrieve archived and historical incidents.
  - Frontend: Provide archived and historical record views.
  - Backend: Retrieve those records under admin authorization.
- **`TASK4-005`** Admins can generate daily reports.
  - Frontend: Provide a daily period selector and results view.
  - Backend: Generate daily results from persisted data.
- **`TASK4-006`** Admins can generate weekly reports.
  - Frontend: Provide a weekly period selector and results view.
  - Backend: Generate weekly results from persisted data.
- **`TASK4-007`** Admins can generate monthly reports.
  - Frontend: Provide a monthly period selector and results view.
  - Backend: Generate monthly results from persisted data.
- **`TASK4-008`** Reports include category, barangay, status, and frequency statistics.
  - Frontend: Display the required statistics.
  - Backend: Aggregate the required statistics.
- **`TASK4-009`** Report results are scoped to the selected reporting period.
  - Frontend: Show the selected period and result scope.
  - Backend: Enforce the selected period in queries and aggregation.
- **`TASK4-010`** Users without admin access cannot access records or reports.
  - Frontend: Show an unauthorized state.
  - Backend: Enforce authorization for all admin record/report routes.
