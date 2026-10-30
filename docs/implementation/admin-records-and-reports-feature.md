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

`GET /admin/incidents/{incident}` reads the incident's `reporter` and
`volunteer` relationships and its `history` entries with related dispatcher and
status. The reporter display falls back to the volunteer relationship when
`reported_by` is absent. Missing history displays an explicit empty state.

Affected persons and landmark read `affected_population`/`nearest_landmark`
first and fall back to the legacy `persons_count`/`landmark` columns, matching
the dispatcher views so both workspaces display identical values.

## Reports

`GET /admin/reports` accepts `period=daily|weekly|monthly` and an optional
`date=YYYY-MM-DD`. Invalid values are rejected by validation. The date selects
the day, the week containing that date, or the month containing that date; it
defaults to today in `config('app.timezone')`.

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

1. Reviewed incident reporter/volunteer, status, and history relationships.
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
