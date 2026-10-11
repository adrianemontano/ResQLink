# TASK4 — Admin Incident Records and Reports Feature

**Assigned owner:** Monique

## Scope

Task 4 provides the admin-only incident-record workspace and periodic reports
built entirely from persisted MySQL data. User and volunteer-document
management belongs to Task 1; dispatcher queue and status controls belong to
Task 3. Admin records are read-only and never change incident data.

## Routes

All routes sit behind the `admin.` route group, which applies the `auth` and
`role:admin` middleware. Non-admin users receive `403`.

| Route name | Method and URI | Purpose |
| --- | --- | --- |
| `admin.incidents.index` | `GET /admin/incidents` | Searchable, filterable, paginated record log |
| `admin.incidents.show` | `GET /admin/incidents/{incident}` | Read-only detail with status history |
| `admin.reports.index` | `GET /admin/reports` | Daily, weekly, and monthly report summaries |

## Incident records

`GET /admin/incidents` renders a paginated table (15 records per page) with
persisted filters carried through pagination links via `withQueryString()`.

Accepted query parameters:

| Parameter | Required | Behavior |
| --- | --- | --- |
| `search` | No | Matches incident ID, category, barangay, `landmark`, `nearest_landmark`, or the reporting volunteer's name |
| `category` | No | Exact category match |
| `status` | No | Exact workflow status match |
| `scope` | No | `all` (default), `active`, or `archived` |

Search, filtering, and scoping are centralised in
`app/Queries/AdminIncidentRecordQuery.php` so the controller, tests, and any
future export feature share one definition of the record set. The controller
validates every parameter before querying.

### Record scope and archived access

Incidents with the terminal `Completed` status represent the archived and
historical portion of the record log. The scope switcher on the records page
exposes three read-only views, each showing a live record count:

- **All Records** — every incident.
- **Active** — every status except `Completed`.
- **Archived / Historical** — `Completed` records only.

Archived records keep their original data. Viewing, searching, and filtering
never mutate an incident, and no dispatcher status controls are rendered. This
satisfies `TASK4-004` without adding a separate archive table or migration.

The canonical workflow statuses are declared on `App\Models\Incident` as
`STATUS_REPORTED` and `STATUS_COMPLETED`, so views and queries reference the
same values instead of repeating status strings.

### Detail view

`GET /admin/incidents/{incident}` shows the reporter, category, barangay,
landmark (preferring `nearest_landmark`), affected persons (preferring
`affected_population`), coordinates and impact radius, severity, status,
reported time, notes, a read-only record-state badge, and the ordered status
history with the dispatcher and timestamp for each change.

Affected persons and landmark read `affected_population`/`nearest_landmark`
first and fall back to the legacy `persons_count`/`landmark` columns, matching
the dispatcher views so both workspaces display identical values.

## Reports

`GET /admin/reports` accepts an optional `period` of `daily`, `weekly`, or
`monthly` and an optional `date` in `Y-m-d` format. Invalid values are
rejected by validation. When omitted, the period defaults to `daily` and the
date defaults to today.

The selected period resolves to inclusive `reported_at` boundaries:

| Period | Range |
| --- | --- |
| `daily` | Start of day to end of day |
| `weekly` | Start of week to end of week |
| `monthly` | Start of month to end of month |

All aggregation runs against persisted incident data scoped to that range, so
report results respect the selected reporting period (`TASK4-009`).

### Reported statistics

- **Total incidents** in the period.
- **Status summary cards** — one card per workflow status, labelled
  `Active — Reported`, `Active — Received`, `Active — Dispatched`, and
  `Archived — Completed`.
- **Frequency statistic** — the number of distinct days containing incidents.
- **Distribution by category** — grouped counts with proportional bars.
- **Distribution by barangay** — grouped counts.
- **Distribution by status** — grouped counts with proportional bars.
- **Frequency by date** — grouped counts per day.

Each status owns a fixed palette colour that matches the shared `.badge`
status colours in `public/css/resqlink-theme.css`, so report bars and record
badges read consistently.

### Empty periods

A period with no incidents shows a total of `0`, zeroed status cards, and the
explicit empty state "No incident data in this period." rather than misleading
totals.

## Automated coverage

`backend/tests/Feature/AdminRecordsAndReportsTest.php` covers search across the
submitted landmark field, category and status filtering, the archived and
active record scopes, the scope switcher, read-only detail with status history
and no dispatcher controls, daily period scoping, empty periods, report
summary cards, and admin-only authorization.
