# TASK4 — Admin Incident Records and Reports Feature

**Assigned owner:** Monique

## Scope

Task 4 provides the admin-only incident-record workspace and periodic reports
built entirely from persisted MySQL data. User and volunteer-document
management belongs to Task 1; dispatcher queue and status controls belong to
Task 3. Admin records are read-only and never change incident data. This
feature does not add an archive table or change incident submission,
dispatcher status transitions, or shared incident fields.

## Routes and permissions

All routes sit behind the `admin.` route group, which applies the `auth` and
`role:admin` middleware. Dispatchers, volunteers, inactive accounts, and
unauthenticated users cannot access these pages; non-admin users receive `403`.

| Route name | Method and URI | Purpose |
| --- | --- | --- |
| `admin.incidents.index` | `GET /admin/incidents` | Searchable, filterable, paginated record log |
| `admin.incidents.show` | `GET /admin/incidents/{incident}` | Read-only detail with status history |
| `admin.reports.index` | `GET /admin/reports` | Daily, weekly, and monthly report summaries |

## Incident records

`GET /admin/incidents` renders a paginated table (15 records per page, ordered
by report time then ID) with filters carried through pagination links via
`withQueryString()`.

Accepted query parameters:

| Parameter | Required | Behavior |
| --- | --- | --- |
| `search` | No | See search fields below |
| `category` | No | Exact category match |
| `status` | No | Exact workflow status match |
| `scope` | No | `all` (default), `active`, or `archived` |
| `page` | No | Pagination page |

Search matches:

- the incident ID, as a plain number or in the displayed `INC-####` form;
- the reporter or volunteer name, first/last name, email, or username (every
  word of a multi-word search must match);
- category, barangay, `landmark`, `nearest_landmark`, notes, status, and
  preliminary severity;
- affected persons, coordinates, and impact radius as text;
- the reported date (`reported_at`, falling back to `created_at`).

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

`GET /admin/incidents/{incident}` shows a read-only record-state badge, the
reporter (falling back to the `volunteer` relationship when `reported_by` is
absent), category, barangay, landmark, affected persons, coordinates, impact
radius, severity, status, reported time with timezone, notes, and the status
history with the dispatcher and timestamp for each change. Missing history
displays an explicit empty state.

Affected persons and landmark read `affected_population`/`nearest_landmark`
first and fall back to the legacy `persons_count`/`landmark` columns, matching
the dispatcher views so both workspaces display identical values.

## Reports

`GET /admin/reports` accepts `period=daily|weekly|monthly` and an optional
`date=YYYY-MM-DD`. Invalid values are rejected by validation. The date selects
the day, the week containing that date, or the month containing that date; it
defaults to today in `config('app.timezone')`.

Report data is built by `app/Services/AdminIncidentReportService.php`. Windows
use the configured application timezone (currently `UTC`) and include their
start but exclude their end:

| Period | Window |
| --- | --- |
| `daily` | Midnight to the next midnight |
| `weekly` | Monday midnight to the following Monday midnight |
| `monthly` | First day of the month to the first day of the next month |

The page displays the timezone and window boundaries. Queries use
`reported_at`, falling back to `created_at` for legacy incidents where
`reported_at` is null, so results respect the selected period (`TASK4-009`).

### Reported statistics

- **Total incidents** in the period.
- **Status summary cards** — one card per workflow status, labelled
  `Active — Reported`, `Active — Received`, `Active — Dispatched`, and
  `Archived — Completed`.
- **Days with incidents** — the number of dates in the window with at least
  one incident.
- **Distribution by category** — grouped counts with proportional bars.
- **Distribution by barangay** — grouped counts.
- **Distribution by status** — grouped counts with proportional bars.
- **Frequency by date** — one row per date in the window, including dates with
  zero incidents.

Blank category, barangay, or status values are grouped as `Unknown`. Each
status owns a fixed palette colour that matches the shared `.badge` status
colours in `public/css/resqlink-theme.css`, so report bars and record badges
read consistently.

### Empty periods

A period with no incidents shows a total of `0`, zeroed status cards, zero
frequency for each date, and the explicit empty state "No incident data in
this period. All totals are zero." rather than misleading totals.

## Automated coverage

`backend/tests/Feature/AdminRecordsAndReportsTest.php` covers search across
incident fields and reporter identity, the nearest-landmark field, category
and status filtering, pagination with preserved filters, the archived and
active record scopes and their counts, read-only detail with status history
and no dispatcher controls, daily/weekly/monthly window boundaries, the
`created_at` fallback, report summary cards, empty periods, and denial of
every record and report route to dispatchers and volunteers.
