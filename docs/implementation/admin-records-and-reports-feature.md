# TASK4 — Monique: Admin Records and Reports

## Scope and implementation status

This note documents the admin-only incident records and reporting workflow.
It does not add an archive state or change incident submission, dispatcher
status transitions, or shared incident fields.

The records workspace reads every persisted incident, including completed and
historical incidents. The current schema has no separate archive flag or
archive table. Incident details are read-only and show status history when it
exists.

## Routes and permissions

| Route name | Method and path | Permission |
| --- | --- | --- |
| `admin.incidents.index` | `GET /admin/incidents` | Authenticated active admin |
| `admin.incidents.show` | `GET /admin/incidents/{incident}` | Authenticated active admin |
| `admin.reports.index` | `GET /admin/reports` | Authenticated active admin |

The routes are inside the `auth` and `role:admin` middleware group. Dispatchers,
volunteers, inactive accounts, and unauthenticated users cannot access these
pages. The records routes are read-only; no status mutation action is exposed.

## Incident records

`GET /admin/incidents` accepts optional `search`, `category`, `status`, and
`page` query parameters. Search covers the incident ID (including the displayed
`INC-####` form), reporter or volunteer name/email/username, category, barangay,
landmark, affected persons, coordinates, impact radius, reported date, notes,
status, and preliminary severity. Category and status filters are applied to
persisted incident fields. Results are ordered by report time and ID, paginated
at 15 incidents per page, and retain filters when navigating pages.

`GET /admin/incidents/{incident}` reads the incident's `reporter` and
`volunteer` relationships and its `history` entries with related dispatcher and
status. The reporter display falls back to the volunteer relationship when
`reported_by` is absent. Missing history displays an explicit empty state.

## Field meanings and dependencies

Task 4 consumes the existing Task 2 incident fields without changing their
schema. `category` is the incident category; `barangay` is the reported
barangay; `nearest_landmark` falls back to the legacy `landmark` field;
`affected_population` falls back to `persons_count`; `severity` is the
preliminary classification; and `status` is the incident workflow status.
`reported_at` is the report time used for report-window inclusion and display;
legacy rows with no `reported_at` use `created_at`.

Task 4 consumes Task 3 history through `incident_histories`: `status_id` gives
the status label, `changed_by` identifies the dispatcher, `changed_at` is the
transition time, and `notes` is the optional transition note. The admin page
only displays this data and does not change it. Report frequency means the
number of persisted incidents per calendar date in the selected window.

Dependencies are Task 2's incident fields and reporter relationships, plus
Task 3's incident status values and status-history relationships. No Task 2 or
Task 3 endpoint or workflow is changed by Task 4.

For Task 4, archived records mean existing completed and historical incidents
already stored in the database. They remain visible in the all-records list and
are included in reports according to their report date, with their existing
workflow statuses unchanged. No separate archive marker or workflow is used.

## Report definitions

`GET /admin/reports` accepts `period=daily|weekly|monthly` and an optional
`date=YYYY-MM-DD`. The date selects the day, the week containing that date, or
the month containing that date. The selected date defaults to today in
`config('app.timezone')`.

Report windows use the configured application timezone (currently `UTC`). Each
window includes its start and excludes its end: daily is midnight to the next
midnight; weekly is Monday midnight to the following Monday midnight; monthly
is the first day at midnight to the first day of the next month. The page
displays the timezone and window boundaries. Queries use `reported_at`, falling
back to `created_at` for legacy incidents where `reported_at` is null.

Reports calculate total incidents and distributions by category, barangay, and
status from persisted incidents in the selected window. Frequency is the count
per calendar date; each date in the window is returned, including dates with a
zero count. An empty window returns zero total, empty distributions, zero
frequency for each date, and an explicit empty-state message.

## Task 4 implementation-step mapping

1. Reviewed incident reporter/volunteer, status, and history relationships;
   field meanings and Task 2/3 dependencies are recorded above.
2. Reused the existing role-protected `admin.incidents.*` and
   `admin.reports.*` route names.
3. Added persisted incident and reporter-field search, category/status filters,
   15-row pagination, and no-results feedback.
4. Kept detail pages read-only; completed and historical incidents remain
   available, with actor/status history where present.
5. Defined daily, Monday-based weekly, and monthly input behavior in the
   configured application timezone with exclusive end boundaries.
6. Calculated totals, category/barangay/status distributions, and daily
   frequency from persisted data.
7. Added zero-filled date frequency and an explicit empty-period message.
8. Kept Task 2/3 data contracts unchanged and documented routes, reports, and
   permissions here.
