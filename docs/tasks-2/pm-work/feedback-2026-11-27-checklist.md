# Feedback checklist — 2026-11-27

Feedback items received on 2026-11-27, mapped to the current system and the task
owners in `docs/naming-conventions.md`. Update each item's status and notes as
work lands. Do not mark an item done until its code and documentation are both
updated.

## Summary

| # | Item | Task / owner | Status |
| --- | --- | --- | --- |
| 1 | Consistent fonts and color scheme | All tasks | First pass done |
| 2 | Cited reference for severity levels | TASK2 — Adriane | Not started |
| 3 | Severity formula per incident category, with source and spreadsheet | TASK2 — Adriane | Not started |
| 4 | Dispatch another team to the same incident | TASK3 — Angela | Implemented (see items 11–12), tests not yet run |
| 5 | Admin creates teams and accounts and assigns team leads | TASK1 — Jassy | Implemented, tests not yet run |
| 6 | Incident data in a table view | TASK4 — Monique | Needs clarification |
| 7 | Ask CCDRRMO: cancelled dispatches and additional-dispatch process | Team | Not started |
| 8 | Volunteer: fix the barangay dropdown | TASK2 — Adriane | Fixed |
| 9 | Dispatcher: "mark as unread" in the notification bell | TASK3 — Angela | Done |
| 10 | Dispatcher: newest incidents first | TASK3 — Angela | Done |
| 11 | Dispatcher: status dropdown that allows dispatching teams again | TASK3 — Angela | Implemented, tests not yet run |
| 12 | Dispatcher: field showing which team was dispatched | TASK3 — Angela | Implemented, tests not yet run |
| 13 | Admin has access to all data | TASK1 — Jassy | Dispatch data added; confirm nothing else is missing |
| 14 | Admin needs the different reports | TASK4 — Monique | Per-team report added |

## Items

### 1. Consistent fonts and color scheme — All tasks

Feedback: fonts and colors must be consistent across all pages.

Current state: pages mostly use Poppins, but styles are spread across several
CSS files (`resqlink-dashboard.css`, `resqlink-design-system.css`,
`resqlink-responsive.css`, and others) with some hard-coded colors, such as the
status and severity badge colors.

First pass (2026-11-27): every stylesheet now uses the shared font token; 63
hard-coded colours that matched a palette token now use the token; status and
severity badges on dashboard pages are computed from the shared tokens, so they
match map markers and report bars (Completed is now grey everywhere instead of
blue in tables); static inline styles in Blade views were replaced with shared
classes in `resqlink-utilities.css`; the four error pages share one component;
the forced password-change page was rebuilt with classes, accessible show/hide
buttons, and valid markup. Rules are recorded in
`docs/design-system/resqlink-visual-system.md`.

Remaining: about 120 hard-coded colours that do not match a token (mostly
white, tints, and one-off shades) — review them per page with each owner.

### 2. Cited reference for severity levels — TASK2, Adriane

Feedback: the severity classification needs a reference.

Current state: `IncidentSeverityService` scores affected persons (10 / 50 / 100)
and impact radius (100 / 500 / 1000 m) into Low, Moderate, High, or Critical.
No source is cited for these thresholds.

Next step: find an official basis (for example, NDRRMC or CCDRRMO guidance),
adjust the thresholds to match it, and cite it in the TASK2 documentation.

### 3. Severity formula per category — TASK2, Adriane

Feedback: provide a formula for each incident category, state the formula's
source, then prepare a spreadsheet.

Current state: Flood, Earthquake, Landslide, and Fire all use the same formula.

Next step: define one formula per category with its source, record it in a
spreadsheet for review, then implement it in `IncidentSeverityService`.

### 4. Dispatch another team — TASK3, Angela

Feedback: it should be possible to dispatch another team to an incident.

Current state: an incident moves once through Reported → Received → Dispatched →
Completed, and there is no link between incidents and teams.

Depends on: item 5 (teams now exist) and item 7 (the CCDRRMO process).

### 5. Teams and team leads — TASK1, Jassy

Feedback: the Admin creates teams and accounts and assigns team leads.

Done: Admin → Teams (`/admin/teams`) creates, edits, and activates or
deactivates teams; selects Barangay Responder members; and assigns a team lead,
who must be a member. A responder belongs to at most one team. Accounts are
still created in User Management. Details are in
`docs/tasks-2/jassy/task1-remaining-user-access.md`.

Remaining: run the migration on each developer database and run
`AdminTeamManagementTest` once `pdo_sqlite` is available.

### 6. Incident data in a table — TASK4, Monique

Feedback: incident data should be shown and viewable in a table.

Current state: Admin Incident Records already shows a searchable, paginated
table. It is unclear whether the feedback means a new table elsewhere (for
example, for Dispatchers) or an exportable spreadsheet.

Next step: confirm what was meant before building anything.

### 7. CCDRRMO process questions — Team

Questions to ask CCDRRMO:

- How is a dispatch cancelled, and what happens to the incident afterward?
- When an incident needs more help, how is an additional team dispatched?
  Can several teams be on one incident at the same time?
- Which official reference do they use for severity levels (item 2)?

The answers decide how item 4 is built.

## Second feedback list

### 8. Barangay dropdown — TASK2, Adriane

Cause: the barangay suggestions on the volunteer incident create and edit pages
were filled only after the map finished loading. When the map was slow or the
local tile server was not running, the dropdown stayed empty.

Fix: `resources/js/volunteer-report.js` fills the list as soon as the barangay
GeoJSON loads, independent of the map, with names de-duplicated and sorted
alphabetically. The field still accepts typed text; the server does not yet
check the value against the official barangay list.

### 9. Mark as unread — TASK3, Angela

Read notifications in the Dispatcher bell now show a "Mark as unread" button.
Route: `dispatcher.notifications.unread` (`POST
/dispatcher/notifications/{notification}/unread`).

### 10. Newest incidents first — TASK3, Angela

The Dispatcher dashboard queue now lists incidents by report time, newest
first. It previously sorted by severity, then status. Severity is still shown on
each row. TASK3-002 in `docs/tasks/3-dispatcher-incident-coordination.md` was
updated to match. The notification bell and the Incidents page were already
newest first.

### 11–12. Re-dispatch and dispatched team — TASK3, Angela

Also covers item 4. Implemented:

- Moving an incident from Received to Dispatched now requires choosing a team
  in the status confirmation dialog. Only available teams are listed: a team
  already dispatched to an open incident is hidden until that incident is
  completed or the dispatch is cancelled.
- While the incident is Dispatched, the incident page's "Dispatched Teams" card
  can send another team (with an optional note). The same team cannot be on two
  active dispatches for one incident.
- Each dispatch can be cancelled with a required reason. Cancelled dispatches
  stay listed. When the last active dispatch is cancelled, the incident returns
  to Received so a new team can be sent. **Confirm this rule with CCDRRMO
  (item 7).**
- Additional dispatches and cancellations are written to the incident history
  as "Coordination" updates.
- The Dispatcher incident list has a "Teams" column with the active teams.
- Data: new `incident_dispatches` table (migration
  `2026_11_27_000001_create_incident_dispatches_table`).
- Routes: `dispatcher.incidents.dispatches.store` (`POST
  /dispatcher/incidents/{incident}/dispatches`) and
  `dispatcher.incidents.dispatches.cancellation` (`PATCH
  /dispatcher/incidents/{incident}/dispatches/{dispatch}/cancellation`).
- Coverage: `backend/tests/Feature/IncidentDispatchTest.php` (written, not yet
  executed).

### 13. Admin has access to all data — TASK1, Jassy

Clarified: Admin's access is about seeing the data, not performing Dispatcher
actions. Admin incident records now show the read-only "Dispatched Teams" card
(teams, who dispatched, cancellations). Admin cannot dispatch or cancel.

### 14. Different reports for Admin — TASK4, Monique

Added "Dispatches by team" to Admin Reports for the selected daily, weekly, or
monthly period: team lead, members, active and cancelled dispatches, incidents,
completed incidents, and average minutes from report to dispatch. Every team is
listed, including teams with no dispatches. Built by
`TeamDispatchReportService`.

Follow-up: the Admin dashboard also shows "Team dispatch frequency" for the last
30 days: dispatches per team, average per week, last dispatched, and whether
each team is on a call or available (`TeamDispatchFrequencyService`).
